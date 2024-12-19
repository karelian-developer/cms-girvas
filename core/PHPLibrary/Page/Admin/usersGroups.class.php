<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\UsersGroups as UsersGroups;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageUsersGroups implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/usersGroups.css', 'rel' => 'stylesheet']);

      $cms_locale_setted_name = $this->system_core->configurator->get_database_entry_value('base_admin_locale');
      $url_locale_setted_name = $this->system_core->urlp->get_param('locale');
      $cookie_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;
      
      $cms_locale_name = (!is_null($url_locale_setted_name)) ? $url_locale_setted_name : $cookie_locale_setted_name;
      $cms_locale_name = (!is_null($cms_locale_name)) ? $cms_locale_name : $cms_locale_setted_name;
      $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_name, 'admin');
      if (!$cms_locale->exists_file_data_json()) {
        $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_setted_name, 'admin');
        $cms_locale_name = $cms_locale_setted_name;
      }

      $this->system_core->locale = $cms_locale;
      $locale_data = $this->system_core->locale->get_data();

      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_USERS_NAVIGATION_INDEX_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => $locale_data['PAGE_USERS_NAVIGATION_USERS_LABEL'],
        'NAVIGATION_ITEM_URL' => '/admin/users',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => $locale_data['PAGE_USERS_NAVIGATION_GROUPS_LABEL'],
        'NAVIGATION_ITEM_URL' => '/admin/usersGroups',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => 'navigation-item__link_is-active'
      ]));

      if (!empty($navigations_items_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $users_groups_table_items_assembled_array = [];
      $users_groups = new UsersGroups($this->system_core);
      $users_groups_array_objects = $users_groups->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $users_groups->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($users_groups);

      $user_group_number = 1;
      foreach ($users_groups_array_objects as $user_group_object) {
        $user_group_object->init_data(['id', 'texts', 'name', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);

        /** @var string Заголовок группы пользователей */
        $users_group_title = (!empty($user_group_object->get_title($cms_locale_name))) ? $user_group_object->get_title($cms_locale_name) : $user_group_object->get_title($cms_locale_setted_name);
        $users_group_title = strip_tags($users_group_title);
        
        $user_group_created_date_timestamp = date('d.m.Y H:i:s', $user_group_object->get_created_unix_timestamp());
        $user_group_updated_date_timestamp = date('d.m.Y H:i:s', $user_group_object->get_updated_unix_timestamp());

        array_push($users_groups_table_items_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/usersGroups/tableItem.tpl', [
          'USER_GROUP_ID' => $user_group_object->get_id(),
          'USER_GROUP_INDEX' => $user_group_number,
          'USER_GROUP_NAME' => $user_group_object->get_name(),
          'USER_GROUP_TITLE' => $users_group_title,
          'USER_GROUP_USERS_COUNT' => $user_group_object->get_users_count(),
          'USER_GROUP_CREATED_DATE_TIMESTAMP' => $user_group_created_date_timestamp,
          'USER_GROUP_UPDATED_DATE_TIMESTAMP' => $user_group_updated_date_timestamp
        ]));

        $user_group_number++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/usersGroups.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
        'PAGE_USERS_GROUPS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'users-groups',
        'ADMIN_PANEL_USERS_GROUPS_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/usersGroups/table.tpl', [
          'ADMIN_PANEL_USERS_GROUPS_TABLE_ITEMS' => implode($users_groups_table_items_assembled_array)
        ])
      ]);
    }

  }

}

?>
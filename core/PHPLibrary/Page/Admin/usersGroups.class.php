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
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageUsersGroups implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USERS_GROUPS_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'index' => [
        'name' => 'index',
        'iconName' => 'index',
        'link' => '/',
        'permanent' => true,
        'isActive' => false
      ],
      'users' => [
        'name' => 'users',
        'iconName' => 'users',
        'link' => '/users',
        'permanent' => false,
        'isActive' => false
      ],
      'groups' => [
        'name' => 'groups',
        'iconName' => 'usersGroups',
        'link' => '/usersGroups',
        'permanent' => false,
        'isActive' => true
      ],
    ];

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/usersGroups.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $users_groups_table_items_assembled_array = [];
      $users_groups = new UsersGroups($this->system_core);
      $users_groups_locale_default = $this->system_core->get_cms_locale('admin');
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
        $users_group_title = $user_group_object->get_title($users_groups_locale_default->get_name());
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
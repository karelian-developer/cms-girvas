<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\UserGroup as UserGroup;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageUserGroup implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/userGroup.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_USERS_GROUP_NAVIGATION_BACK_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin/usersGroups',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
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

      $user_group = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $user_group_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $user_group = (UserGroup::exists_by_id($this->system_core, $user_group_id)) ? new UserGroup($this->system_core, $user_group_id) : null;
        
        if (!is_null($user_group)) {
          $user_group->init_data(['id', 'name', 'permissions', 'texts']);
        }
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/userGroup.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
        'ADMIN_PANEL_PAGE_NAME' => 'user-group',
        'USER_GROUP_ID' => (!is_null($user_group)) ? $user_group->get_id() : 0,
        'USER_GROUP_NAME' => (!is_null($user_group)) ? $user_group->get_name() : '',
        'USER_GROUP_TITLE' => (!is_null($user_group)) ? $user_group->get_title() : '',
        'USER_GROUP_FORM_METHOD' => (!is_null($user_group)) ? 'PATCH' : 'PUT',
        'USER_GROUP_PERMISSION_ADMIN_PANEL_AUTH_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_PANEL_AUTH)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_USERS_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_MODULES_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_TEMPLATES_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_FEEDS_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_SETTINGS_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_ADMIN_VIEWING_LOGS_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_MODER_USERS_BAN_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_MODER_USERS_BAN)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_MODER_USERS_WARNS_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_MODER_USERS_WARNS)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_EDITOR_ENTRIES_EDIT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_EDITOR_PAGES_STATIC_EDIT_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CREATE_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CHANGE_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE)) ? 'checked' : ''),
        'USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_RATE_VALUE' => (is_null($user_group)) ? '' : (($user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE)) ? 'checked' : ''),
      ]);
    }

  }

}

?>
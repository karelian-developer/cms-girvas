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

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
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

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/usersGroups.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $usersGroupsTableItemsAssembled = [];
      $usersGroups = new UsersGroups($this->CMSCore);

      $usersGroupsLocale = $this->CMSCore->get_cms_locale('admin');
      $usersGroupsLocaleName = $usersGroupsLocale->get_name();

      $usersGroupsObjects = $usersGroups->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $usersGroups->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($usersGroups);

      $userGroupNumber = 1;
      foreach ($usersGroupsObjects as $object) {
        $object->init_data(['id', 'texts', 'name', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

        /** @var string Заголовок группы пользователей */
        $usersGroupTitle = $object->get_title($usersGroupsLocaleName);
        $usersGroupTitle = strip_tags($usersGroupTitle);
        
        $createdUnixTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
        $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

        array_push($usersGroupsTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/usersGroups/tableItem.tpl', [
          'USER_GROUP_ID' => $object->get_id(),
          'USER_GROUP_INDEX' => $userGroupNumber,
          'USER_GROUP_NAME' => $object->get_name(),
          'USER_GROUP_TITLE' => $usersGroupTitle,
          'USER_GROUP_USERS_COUNT' => $object->get_users_count(),
          'USER_GROUP_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'USER_GROUP_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]));

        $userGroupNumber++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/usersGroups.tpl', [
        'PAGE_USERS_GROUPS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'users-groups',
        'ADMIN_PANEL_USERS_GROUPS_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/usersGroups/table.tpl', [
          'ADMIN_PANEL_USERS_GROUPS_TABLE_ITEMS' => implode($usersGroupsTableItemsAssembled)
        ])
      ]);
    }

  }

}

?>
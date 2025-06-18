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
  use \core\PHPLibrary\UserGroup as UserGroup;
  use \core\PHPLibrary\Users as Users;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageUsers implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USERS_NAVIGATION_%s_LABEL';

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
        'isActive' => true
      ],
      'groups' => [
        'name' => 'groups',
        'iconName' => 'usersGroups',
        'link' => '/usersGroups',
        'permanent' => false,
        'isActive' => false
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/users.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $subpageName = !is_null($this->CMSCore->urlp->get_path(2)) ? $this->CMSCore->urlp->get_path(2) : 'list';

      $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $usersTableItemsAssembled = [];

      $users = new Users($this->CMSCore);
      $usersLocale = $this->CMSCore->get_cms_locale('admin');
      $usersLocaleName = $usersLocale->get_name();

      $usersObjects = $users->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $users->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($users);

      $userNumber = 1;
      foreach ($usersObjects as $object) {
        $object->init_data(['id', 'login', 'email', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
        
        $objectID = $object->get_group_id();
        $userGroupObject = new UserGroup($this->CMSCore, $objectID);
        $userGroupObject->init_data(['texts']);

        $createdUnixTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
        $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

        $usersGroupTitle = $userGroupObject->get_title($usersLocaleName);
        $usersGroupTitle = strip_tags($usersGroupTitle);

        array_push($usersTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/users/tableItem.tpl', [
          'USER_ID' => $object->get_id(),
          'USER_INDEX' => $userNumber,
          'USER_LOGIN' => strip_tags($object->get_login()),
          'USER_GROUP_TITLE' => $usersGroupTitle,
          'USER_EMAIL' => $object->get_email(),
          'USER_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'USER_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]));

        $userNumber++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/users.tpl', [
        'PAGE_USERS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'users',
        'ADMIN_PANEL_USERS_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/users/table.tpl', [
          'ADMIN_PANEL_USERS_TABLE_ITEMS' => implode($usersTableItemsAssembled)
        ])
      ]);
    }

  }

}

?>
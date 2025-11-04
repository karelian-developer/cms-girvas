<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Users as Users;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageUsers implements InterfacePage
{
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

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/users.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $subpageName = $this->CMSCore->urlp->getPath(2) ?? 'list';

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $usersTableItemsAssembled = [];

    $users = new Users($this->CMSCore);
    $usersLocale = $this->CMSCore->getCMSLocale('admin');
    $usersLocaleName = $usersLocale->getName();

    $usersObjects = $users->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $users->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($users);

    $userNumber = 1;
    foreach ($usersObjects as $object) {
      $object->initData(['id', 'login', 'email', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'emailIsSubmitted']);
      
      $objectID = $object->getGroupID();
      $userGroupObject = new UserGroup($this->CMSCore, $objectID);
      $userGroupObject->initData(['texts']);

      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $usersGroupTitle = $userGroupObject->getTitle($usersLocaleName);
      $usersGroupTitle = strip_tags($usersGroupTitle);

      if (!$object->isBlocked()) {
        $statusLabel = $object->emailIsSubmitted()
          ? '<span style="color: green;">Активен</span>'
          : '<span style="color: orange;">Почта не подтверждена</span>';
      } else {
        $statusLabel = $object->emailIsSubmitted()
          ? '<span style="color: red;">Заблокирован</span>'
          : '<span style="color: red;">Почта не подтверждена (заблокирован)</span>';
      }

      array_push($usersTableItemsAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/users/tableItem.tpl', [
        'USER_ID' => $object->getID(),
        'USER_INDEX' => $userNumber,
        'USER_LOGIN' => strip_tags($object->getLogin()),
        'USER_AVATAR_URL' => $object->getAvatarURL(64),
        'USER_REGISTRATION_IP' => $object->getRegistrationIP(),
        'USER_STATUS_LABEL' => $statusLabel,
        'USER_GROUP_TITLE' => $usersGroupTitle,
        'USER_EMAIL' => $object->getEmail(),
        'USER_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
        'USER_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
      ]));

      $userNumber++;
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/users.tpl', [
      'PAGE_USERS_PAGINATION' => $pagination->assembled,
      'ADMIN_PANEL_PAGE_NAME' => 'users',
      'ADMIN_PANEL_USERS_TABLE' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/users/table.tpl', [
        'ADMIN_PANEL_USERS_TABLE_ITEMS' => implode($usersTableItemsAssembled)
      ])
    ]);
  }

}
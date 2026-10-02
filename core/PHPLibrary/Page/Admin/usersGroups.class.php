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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
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
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\UsersGroups as UsersGroups;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;

class PageUsersGroups implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USERS_GROUPS_NAVIGATION_%s_LABEL';

  /**
   * Допустимые значения параметра sort
   */
  private const ALLOWED_SORT_RULES = [
    'by_createdtimestamp_increase',
    'by_createdtimestamp_decrease',
    'by_updatedtimestamp_increase',
    'by_updatedtimestamp_decrease',
    'by_alphabet_increase',
    'by_alphabet_decrease',
  ];

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false,
      'permission' => null,
    ],
    'users' => [
      'name' => 'users',
      'iconName' => 'users',
      'link' => '/users',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminUsersManagement',
    ],
    'groups' => [
      'name' => 'groups',
      'iconName' => 'usersGroups',
      'link' => '/usersGroups',
      'permanent' => false,
      'isActive' => true,
      'permission' => 'hasPermissionAdminUsersGroupsManagement',
    ],
    'consents' => [
      'name' => 'consents',
      'iconName' => 'usersConsents',
      'link' => '/usersConsents',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminUsersConsentsManagement',
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Получить группу текущего авторизованного пользователя админки
   * 
   * @return UserGroup|null
   */
  private function getCurrentUserGroup() : ?UserGroup
  {
    /** @var User|null $user */
    $user = $this->CMSCore->client->getUser(2);
    if ($user === null) {
      return null;
    }

    $user->initData(['metadata']);

    /** @var UserGroup|null $userGroup */
    $userGroup = $user->getGroup();
    if ($userGroup === null) {
      return null;
    }

    $userGroup->initData(['permissions']);

    return $userGroup;
  }

  /**
   * Проверить, есть ли у текущего пользователя право управления группами
   * 
   * @return bool
   */
  private function currentUserCanManageUserGroups() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionAdminUsersGroupsManagement')
      && $userGroup->hasPermissionAdminUsersGroupsManagement();
  }

  /**
   * Собрать страницу ошибки и подменить итоговую сборку
   * 
   * @param int $httpCode
   * 
   * @return void
   */
  private function assemblyError(int $httpCode) : void
  {
    http_response_code($httpCode);

    $pageError = new PageError($this->CMSCore, $this->page, $httpCode);
    $pageError->assembly();
    $this->assembled = $pageError->assembled;
  }

  /**
   * Отфильтровать подразделы по правам текущего пользователя
   * 
   * @param UserGroup $userGroup
   * 
   * @return void
   */
  private function filterSubsections(UserGroup $userGroup) : void
  {
    foreach ($this->navigationSubsections as $index => $subsection) {
      $permission = $subsection['permission'] ?? null;

      if ($permission === null) {
        continue;
      }

      $allowed = method_exists($userGroup, $permission) && $userGroup->{$permission}();

      if (!$allowed) {
        unset($this->navigationSubsections[$index]);
      }
    }
  }

  /**
   * Инициализация подразделов
   *
   * @return void
   */
  public function initSubnavigation() : void
  {
    /** @var UserGroup|null $userGroup */
    $userGroup = $this->getCurrentUserGroup();

    if ($userGroup === null) {
      return;
    }

    // Если нет доступа ни к одному подразделу — не собираем
    $hasAnyAccess = $this->currentUserCanManageUserGroups()
      || $userGroup->hasPermissionAdminUsersManagement()
      || (method_exists($userGroup, 'hasPermissionAdminUsersConsentsManagement')
        && $userGroup->hasPermissionAdminUsersConsentsManagement());

    if (!$hasAnyAccess) {
      return;
    }

    $this->filterSubsections($userGroup);

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  /**
   * Сборка списка локализаций для записи
   *
   * @param array $localesData
   * @return string
   */
  private function assemblyLocalesItems(array $localesData) : string
  {
    $document = new DOMDocument('1.0', 'UTF-8');

    foreach ($localesData as $localeData) {
      $itemElement = $document->createElement('li', $localeData['title']);
      $itemElement->setAttribute('class', 'grid-table__locale');

      if (!empty($localeData['iconURL'])) {
        $iconElement = $document->createElement('img');
        $iconElement->setAttribute('class', 'grid-table__locale-icon');
        $iconElement->setAttribute('src', $localeData['iconURL']);
        $itemElement->prepend($iconElement);
      }

      $document->appendChild($itemElement);
    }

    return $document->saveHTML();
  }

  /**
   * Сборка
   *
   * @return void
   */
  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageUserGroups()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/usersGroups.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null
      ? (int) $this->CMSCore->urlp->getParam('pageNumber')
      : 0;
    $paginationItemsOnPage = 12;

    // Поиск
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? 'by_createdtimestamp_decrease';
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = 'by_createdtimestamp_decrease';
    }

    $usersGroupsTableItemsAssembled = [];
    $usersGroups = new UsersGroups($this->CMSCore);

    $usersGroupsLocale = $this->CMSCore->getCMSLocale('admin');
    $usersGroupsLocaleName = $usersGroupsLocale->getName();

    $usersGroupsObjects = $usersGroups->getAll([
      'limit'  => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage],
      'search' => $searchValue,
      'sort'   => $sortRule,
    ]);

    $usersGroupsTotal = $usersGroups->getCountTotal($searchValue);

    $pagination = new Pagination(
      $this->CMSCore,
      $usersGroupsTotal,
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    unset($usersGroups);

    $userGroupNumber = $paginationItemCurrent * $paginationItemsOnPage + 1;
    foreach ($usersGroupsObjects as $object) {
      $object->initData(['id', 'texts', 'name', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

      /** @var string Заголовок группы пользователей */
      $usersGroupTitle = $object->getTitle($usersGroupsLocaleName);
      $usersGroupTitle = strip_tags($usersGroupTitle);

      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);

      $usersGroupsTableItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/usersGroups/tableItem.tpl',
        [
          'USERS_GROUP_ID' => $object->getID(),
          'USERS_GROUP_INDEX' => $userGroupNumber,
          'USERS_GROUP_NAME' => $object->getName(),
          'USERS_GROUP_TITLE' => $usersGroupTitle,
          'USERS_GROUP_LOCALES_LIST' => $completedLocales,
          'USERS_GROUP_USERS_COUNT' => $object->getUsersCount(),
          'USERS_GROUP_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'USERS_GROUP_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]
      );

      $userGroupNumber++;
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/users/groups.tpl',
      [
        'PAGE_USERS_GROUPS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'users-groups',
        'ADMIN_PANEL_USERS_GROUPS_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/usersGroups/table.tpl',
          ['ADMIN_PANEL_USERS_GROUPS_TABLE_ITEMS' => implode($usersGroupsTableItemsAssembled)]
        ),
        'USERS_GROUPS_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
        'USERS_GROUPS_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
      ]
    );
  }

  /**
   * Query string для пагинации с сохранением поиска и сортировки
   */
  private function buildQueryString(string $searchValue, string $sortRule) : string
  {
    $basePath = '/admin/usersGroups';

    $parts = [];
    if ($searchValue !== '') {
      $parts[] = 'value=' . urlencode($searchValue);
    }
    $parts[] = 'sort=' . urlencode($sortRule);

    return $basePath . '?' . implode('&', $parts);
  }
}
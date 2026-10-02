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

use \DOMDocument as DOMDocument;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\EntriesCategories as EntriesCategories;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageEntriesCategories implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_CONTENT_NAVIGATION_%s_LABEL';

  /**
   * Допустимые правила сортировки
   */
  private const ALLOWED_SORT_RULES = [
    'by_createdtimestamp_increase',
    'by_createdtimestamp_decrease',
    'by_updatedtimestamp_increase',
    'by_updatedtimestamp_decrease',
    'by_alphabet_increase',
    'by_alphabet_decrease',
  ];

  public CoreInterface $CMSCore;
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
    'entries' => [
      'name' => 'entries',
      'iconName' => 'entries',
      'link' => '/entries',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesEdit',
    ],
    'pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorPagesStaticEdit',
    ],
    'categories' => [
      'name' => 'categories',
      'iconName' => 'entriesCategories',
      'link' => '/entriesCategories',
      'permanent' => false,
      'isActive' => true,
      'permission' => 'hasPermissionEditorEntriesCategoriesEdit',
    ],
    'comments' => [
      'name' => 'comments',
      'iconName' => 'entriesComments',
      'link' => '/entriesComments',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionModerEntriesCommentsManagement',
    ],
    'samples' => [
      'name' => 'samples',
      'iconName' => 'entriesSamples',
      'link' => '/entriesSamples',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesEdit',
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/forms',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminFormsManagement',
    ],
    'blocks' => [
      'name' => 'blocks',
      'iconName' => 'contentBlocks',
      'link' => '/contentBlocks',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorContentBlocksEdit',
    ]
  ];

  public function __construct(CoreInterface $CMSCore, Page $page)
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
   * Проверить, есть ли у текущего пользователя право редактирования категорий записей
   * 
   * @return bool
   */
  private function currentUserCanEditCategories() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionEditorEntriesCategoriesEdit')
      && $userGroup->hasPermissionEditorEntriesCategoriesEdit();
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

    // Показываем навигацию только тем, у кого есть хоть какое-то право контент-раздела
    $hasAnyAccess = (method_exists($userGroup, 'hasAnyEditorPermission')
        && $userGroup->hasAnyEditorPermission())
      || $userGroup->hasPermissionModerEntriesCommentsManagement()
      || $userGroup->hasPermissionAdminFormsManagement();

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
   * 
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

  private function buildQueryString(string $searchValue, string $sortRule) : string
  {
    $basePath = '/admin/entriesCategories';

    $parts = [];
    if ($searchValue !== '') {
      $parts[] = 'value=' . urlencode($searchValue);
    }
    $parts[] = 'sort=' . urlencode($sortRule);

    return $basePath . '?' . implode('&', $parts);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanEditCategories()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesCategories.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    // Поиск
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? EntriesCategories::DEFAULT_SORT_RULE;
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = EntriesCategories::DEFAULT_SORT_RULE;
    }

    $entriesCategoriesTableItemsAssembled = [];
    $entriesCategories = new EntriesCategories($this->CMSCore);

    $entriesCategoriesLocale = $this->CMSCore->getCMSLocale('admin');
    $entriesCategoriesLocaleName = $entriesCategoriesLocale->getName();

    $entriesCategoriesObjects = $entriesCategories->getAll(
      ['limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]],
      $searchValue,
      $sortRule
    );

    $pagination = new Pagination(
      $this->CMSCore,
      $entriesCategories->getCountTotal($searchValue),
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    unset($entriesCategories);

    foreach ($entriesCategoriesObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'parentID']);

      $createdDateTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $updatedDateTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $entriesCategoryTitle = $object->getTitle($entriesCategoriesLocaleName);
      $entriesCategoryTitle = strip_tags($entriesCategoryTitle);

      $entriesCategoryDescription = $object->getDescription($entriesCategoriesLocaleName);
      $entriesCategoryDescription = strip_tags($entriesCategoryDescription);

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);

      $objectParent = $object->getParent();
      if ($objectParent !== null) {
        $objectParent->initData(['texts']);
      }

      $objectParentTitle = $objectParent !== null ? $objectParent->getTitle($entriesCategoriesLocaleName) : 'Нет родителя';

      array_push($entriesCategoriesTableItemsAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesCategories/tableItem.tpl', [
        'ENTRIES_CATEGORY_ID' => $object->getID(),
        'ENTRIES_CATEGORY_INDEX' => $index + 1,
        'ENTRIES_CATEGORY_TITLE' => !empty($entriesCategoryTitle) ? $entriesCategoryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entriesCategoriesLocaleName),
        'ENTRIES_CATEGORY_DESCRIPTION' => !empty($entriesCategoryDescription) ? $entriesCategoryDescription : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entriesCategoriesLocaleName),
        'ENTRIES_CATEGORY_URL' => $object->getURL(),
        'ENTRIES_CATEGORY_LOCALES_LIST' => $completedLocales,
        'ENTRIES_CATEGORY_ENTRIES_COUNT' => $object->getEntriesCount(),
        'ENTRIES_CATEGORY_PARENT_TITLE' => $objectParentTitle,
        'ENTRIES_CATEGORY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
        'ENTRIES_CATEGORY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp
      ]));
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesCategories.tpl', [
      'PAGE_ENTRIES_CATEGORIES_PAGINATION' => $pagination->assembled,
      'ADMIN_PANEL_PAGE_NAME' => 'entries-categories',
      'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesCategories/table.tpl', [
        'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE_ITEMS' => implode($entriesCategoriesTableItemsAssembled)
      ]),
      'ENTRIES_CATEGORIES_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
      'ENTRIES_CATEGORIES_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
    ]);
  }
}
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
use \core\PHPLibrary\Pages as Pages;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;
use \DOMImplementation as DOMImplementation;

class PagePages implements InterfacePage
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
      'isActive' => true,
      'permission' => 'hasPermissionEditorPagesStaticEdit',
    ],
    'categories' => [
      'name' => 'categories',
      'iconName' => 'entriesCategories',
      'link' => '/entriesCategories',
      'permanent' => false,
      'isActive' => false,
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
   * Проверить, есть ли у текущего пользователя право редактирования статических страниц
   * 
   * @return bool
   */
  private function currentUserCanEditPages() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionEditorPagesStaticEdit')
      && $userGroup->hasPermissionEditorPagesStaticEdit();
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

    // Показываем навигацию только тем, у кого есть хоть какое-то право редактора/модератора/форм
    $hasAnyAccess = method_exists($userGroup, 'hasAnyEditorPermission')
      && $userGroup->hasAnyEditorPermission();

    if (!$hasAnyAccess
      && !$userGroup->hasPermissionModerEntriesCommentsManagement()
      && !$userGroup->hasPermissionAdminFormsManagement()) {
      return;
    }

    $this->filterSubsections($userGroup);

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  /**
   * Сборка списка локализаций
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

  /**
   * Query string для пагинации
   */
  private function buildQueryString(string $searchValue, string $sortRule) : string
  {
    $basePath = '/admin/pages';

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
    if (!$this->currentUserCanEditPages()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/pages.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    // Поиск
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? Pages::DEFAULT_SORT_RULE;
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = Pages::DEFAULT_SORT_RULE;
    }

    $pagesStaticTableItemsAssembled = [];
    $pagesStatic = new Pages($this->CMSCore);
    $pagesStaticLocale = $this->CMSCore->getCMSLocale('admin');
    $pagesStaticLocaleName = $this->CMSCore->locale->getName();

    $pagesStaticObjects = $pagesStatic->getAll(
      ['limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]],
      false,
      $searchValue,
      $sortRule
    );

    $pagination = new Pagination(
      $this->CMSCore,
      $pagesStatic->getCountTotal($searchValue),
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    unset($pagesStatic);

    $tableItemsAssembled = [];

    foreach ($pagesStaticObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'authorID']);

      $createdDateTimestamp = $object->getCreatedUnixTimestamp();
      $publishedDateTimestamp = $object->getPublishedUnixTimestamp();
      $updatedDateTimestamp = $object->getUpdatedUnixTimestamp();

      $pageStaticTitle = $object->getTitle($pagesStaticLocaleName);
      $pageStaticDescription = $object->getDescription($pagesStaticLocaleName);

      $pageStaticTitle = strip_tags($pageStaticTitle);
      $pageStaticDescription = strip_tags($pageStaticDescription);

      $authorObject = $object->getAuthor();
      if ($authorObject !== null) {
        $authorObject->initData(['login']);
      }

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);
      $SEOStatus = !empty($object->getCompletedSEOTexts())
        ? '<span style="color: green;">Оптимизировано</span>'
        : '<span style="color: red;">Не оптимизировано</span>';

      $authorLogin = $authorObject !== null ? $authorObject->getLogin() : 'User deleted';

      $templatesAssembled = [];
      $templateContent = ThemeCollector::getTemplateFileContent(
        $this->CMSCore->theme,
        'templates/page/pages/tableItem.tpl'
      );

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_INDEX')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_INDEX', $index);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_ID')) {
        $value = $object !== null ? $object->getID() : 0;
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_ID', $value);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_NAME')) {
        $value = $object !== null ? $object->getName() : '';
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_NAME', $value);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_TITLE')) {
        $value = $object !== null ? $object->getTitle($localeName) : '';
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_TITLE',
          str_replace(
            ThemeCollector::DECODED_ENTITIES,
            ThemeCollector::SAFE_SYMBOLS,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
          )
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_DESCRIPTION')) {
        $value = $object !== null ? $object->getDescription($localeName) : '';
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_DESCRIPTION',
          str_replace(
            ThemeCollector::DECODED_ENTITIES,
            ThemeCollector::SAFE_SYMBOLS,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
          )
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_URL')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_URL', $object->getURL());
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_AUTHOR_LOGIN')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_AUTHOR_LOGIN', $authorLogin);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_LOCALES_LIST')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_LOCALES_LIST', $completedLocales);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_SEO_STATUS')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_SEO_STATUS', $SEOStatus);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_CREATED_DATE_TIMESTAMP')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_CREATED_DATE_TIMESTAMP', date('d.m.Y H:i:s', $createdDateTimestamp));
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP')) {
        $value = $publishedDateTimestamp > 0 ? date('d.m.Y H:i:s', $publishedDateTimestamp) : '-';
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP', $value);
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_UPDATED_DATE_TIMESTAMP')) {
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_UPDATED_DATE_TIMESTAMP', date('d.m.Y H:i:s', $updatedDateTimestamp));
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PUBLISHED_STATUS')) {
        $value = $object->isPublished() ? 'published' : 'not-published';
        ThemeCollector::addTemplateVariable($templatesAssembled, 'PAGE_STATIC_PUBLISHED_STATUS', $value);
      }

      $tableItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/pages/tableItem.tpl',
        $templatesAssembled
      );
    }

    $tableItemsAssembled = $tableItemsAssembled ?? [];

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/pages.tpl',
      [
        'PAGE_PAGES_STATIC_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'page_static',
        'ADMIN_PANEL_PAGES_STATIC_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/pages/table.tpl',
          [
            'ADMIN_PANEL_PAGES_STATIC_TABLE_ITEMS' => implode($tableItemsAssembled)
          ]
        ),
        'PAGES_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
        'PAGES_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
      ]
    );
  }
}
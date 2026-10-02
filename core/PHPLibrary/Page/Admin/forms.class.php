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

use \DOMDocument as DOMDocument;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Forms as Forms;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \ReflectionEnum as ReflectionEnum;

class PageForms implements InterfacePage
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
      'isActive' => false,
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
      'isActive' => true,
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
   * Проверить, есть ли у текущего пользователя право управления формами
   * 
   * @return bool
   */
  private function currentUserCanManageForms() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionAdminFormsManagement')
      && $userGroup->hasPermissionAdminFormsManagement();
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
   * Сборка списка локализаций для форм
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
    $basePath = '/admin/forms';

    $parts = [];
    if ($searchValue !== '') {
      $parts[] = 'value=' . urlencode($searchValue);
    }
    $parts[] = 'sort=' . urlencode($sortRule);

    return $basePath . '?' . implode('&', $parts);
  }

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageForms()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/forms.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    /** @var int Текущий номер страницы */
    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null
      ? (int) $this->CMSCore->urlp->getParam('pageNumber')
      : 0;

    /** @var int Максимальное количество элементов на странице */
    $paginationItemsOnPage = 12;

    // Поиск
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? Forms::DEFAULT_SORT_RULE;
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = Forms::DEFAULT_SORT_RULE;
    }

    $formsTableItemsAssembled = [];

    $forms = new Forms($this->CMSCore);

    /** @var array Массив объектов форм */
    $formsObjects = $forms->getAll(
      ['limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]],
      $searchValue,
      $sortRule
    );

    $pagination = new Pagination(
      $this->CMSCore,
      $forms->getCountTotal($searchValue),
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    unset($forms);

    foreach ($formsObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'elements', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $objectID = $object->getID();
      $objectName = $object->getName();

      /** @var string Дата создания в формате d.m.Y H:i:s */
      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      /** @var string Дата обновления в формате d.m.Y H:i:s */
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      /** @var string Заголовок */
      $objectTitle = $object->getTitle($localeName);
      $objectTitle = strip_tags($objectTitle);

      /** @var string Описание */
      $objectDescription = $object->getDescription($localeName);
      $objectDescription = strip_tags($objectDescription);
      
      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocalesList = $this->assemblyLocalesItems($completedLocalesData);

      $formMethodID = $object->getMethodID();
      $formMethod = match ($formMethodID) {
        1 => 'GET',
        2 => 'POST',
        3 => 'PUT',
        4 => 'DELETE',
        5 => 'PATCH',
      };

      $formsTableItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/forms/item.tpl',
        [
          'FORM_INDEX' => $paginationItemCurrent * $paginationItemsOnPage + $index + 1,
          'FORM_ID' => $objectID,
          'FORM_NAME' => $objectName,
          'FORM_TITLE' => $objectTitle,
          'FORM_DESCRIPTION' => $objectDescription,
          'FORM_METHOD' => $formMethod,
          'FORM_LOCALES_LIST' => $completedLocalesList,
          'FORM_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'FORM_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/forms.tpl',
      [
        'PAGE_PAGINATION' => $pagination->assembled,
        'PAGE_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/forms/wrapper.tpl',
          [
            'PAGE_ITEMS' => implode($formsTableItemsAssembled)
          ]
        ),
        'FORMS_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
        'FORMS_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
      ]
    );
  }
}
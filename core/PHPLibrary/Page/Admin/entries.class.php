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
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;
use \DOMImplementation as DOMImplementation;

/**
 * Страница со списком записей
 */
class PageEntries implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_CONTENT_NAVIGATION_%s_LABEL';

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
    'entries' => [
      'name' => 'entries',
      'iconName' => 'entries',
      'link' => '/entries',
      'permanent' => false,
      'isActive' => true
    ],
    'pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'isActive' => false
    ],
    'categories' => [
      'name' => 'categories',
      'iconName' => 'entriesCategories',
      'link' => '/entriesCategories',
      'permanent' => false,
      'isActive' => false
    ],
    'comments' => [
      'name' => 'comments',
      'iconName' => 'entriesComments',
      'link' => '/entriesComments',
      'permanent' => false,
      'isActive' => false
    ],
    'samples' => [
      'name' => 'samples',
      'iconName' => 'entriesSamples',
      'link' => '/entriesSamples',
      'permanent' => false,
      'isActive' => false
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/forms',
      'permanent' => false,
      'isActive' => false
    ]
  ];

  /**
   * __construct
   * 
   * @param SystemCore $CMSCore
   * @param Page $page
   */
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

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    // Добавление таблицы стилей для страницы
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $entriesTableItemsAssembled = [];

    $entries = new Entries($this->CMSCore);
    $entriesLocale = $this->CMSCore->getCMSLocale('admin');
    $entriesLocaleName = $entriesLocale->getName();
    
    $entriesObjects = $entries->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $entries->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($entries);

    foreach ($entriesObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'categoryID', 'authorID']);

      $entryCategoryID = $object->getCategoryID();
      $entryCategory = new EntryCategory($this->CMSCore, $entryCategoryID);
      $entryCategory->initData(['texts']);

      $entryCreatedDateTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $entryPublishedDateTimestamp = date('d.m.Y H:i:s', $object->getPublishedUnixTimestamp());
      $entryUpdatedDateTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $entryTitle = $object->getTitle($entriesLocaleName);
      $entryDescription = $object->getDescription($entriesLocaleName);
      $entryCategoryTitle = $entryCategory->getTitle($localeName);

      $entryTitle = strip_tags($entryTitle);
      $entryDescription = strip_tags($entryDescription);
      $entryCategoryTitle = strip_tags($entryCategoryTitle);

      $entryAuthor = $object->getAuthor();
      if ($entryAuthor !== null) {
        $entryAuthor->initData(['login']);
      }

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);
      $entrySEOStatus = !empty($object->getCompletedSEOTexts())
        ? '<span style="color: green;">Оптимизировано</span>'
        : '<span style="color: red;">Не оптимизировано</span>';

      $entryAuthorLogin = $entryAuthor !== null ? $entryAuthor->getLogin() : 'User deleted';

      $entriesTableItemsAssembled[] =  ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme, 'templates/page/entries/tableItem.tpl',
        [
          'ENTRY_ID' => $object->getID(),
          'ENTRY_NAME' => $object->getName(),
          'ENTRY_INDEX' => $index + 1,
          'ENTRY_TITLE' => !empty($entryTitle) ? $entryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entriesLocale->getName()),
          'ENTRY_DESCRIPTION' => !empty($entryDescription) ? $entryDescription : sprintf('[ DESCRIPTION NOT FOUND IN LOCALE %s ]', $entriesLocale->getName()),
          'ENTRY_CATEGORY_TITLE' => !empty($entryCategoryTitle) ? $entryCategoryTitle : sprintf('[ CATEGORY TITLE NOT FOUND IN LOCALE %s ]', $localeName),
          'ENTRY_PUBLISHED_STATUS' => $object->isPublished() ? 'published' : 'not-published',
          'ENTRY_URL' => $object->getURL(),
          'ENTRY_AUTHOR_LOGIN' => $entryAuthorLogin,
          'ENTRY_LOCALES_LIST' => $completedLocales,
          'ENTRY_SEO_STATUS' => $entrySEOStatus,
          'ENTRY_CREATED_DATE_TIMESTAMP' => $entryCreatedDateTimestamp,
          'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $object->getPublishedUnixTimestamp() > 0 ? $entryPublishedDateTimestamp : '-',
          'ENTRY_UPDATED_DATE_TIMESTAMP' => $entryUpdatedDateTimestamp,
        ]
      );
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/entries.tpl',
      [
        'PAGE_ENTRIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries',
        'ADMIN_PANEL_ENTRIES_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/entries/table.tpl',
          [
            'ADMIN_PANEL_ENTRIES_TABLE_ITEMS' => implode($entriesTableItemsAssembled)
          ]
        )
      ]
    );
  }

}
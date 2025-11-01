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
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\EntriesCategories as EntriesCategories;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageEntriesCategories implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_NAVIGATION_%s_LABEL';

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
      'isActive' => false
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
      'isActive' => true
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

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesCategories.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $entriesCategoriesTableItemsAssembled = [];
    $entriesCategories = new EntriesCategories($this->CMSCore);

    $entriesCategoriesLocale = $this->CMSCore->getCMSLocale('admin');
    $entriesCategoriesLocaleName = $entriesCategoriesLocale->getName();

    $entriesCategoriesObjects = $entriesCategories->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $entriesCategories->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
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
      ])
    ]);
  }
}
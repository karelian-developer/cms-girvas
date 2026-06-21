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
use \core\PHPLibrary\EntriesSamples as EntriesSamples;
use \core\PHPLibrary\EntriesSample\EnumSortTypeID as EnumSortTypeID;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \ReflectionEnum as ReflectionEnum;

class PageEntriesSamples implements InterfacePage
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
      'isActive' => true
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/forms',
      'permanent' => false,
      'isActive' => false
    ],
    'blocks' => [
      'name' => 'blocks',
      'iconName' => 'contentBlocks',
      'link' => '/contentBlocks',
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

  /**
   * Сборка списка локализаций для записи
   * 
   * @param array $localesData
   * 
   * @return string
   */
  private function assemblyCategoriesItems(string $localeName, array $categories) : string
  {
    $document = new DOMDocument('1.0', 'UTF-8');

    foreach ($categories as $category) {
      $category->initData(['texts']);

      $itemElement = $document->createElement('li', $category->getTitle($localeName));
      $itemElement->setAttribute('class', 'grid-table__category');

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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesSamples.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    /** @var int Текущий номер страницы */
    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    /** @var int Максимальное количество элементов на странице */
    $paginationItemsOnPage = 12;

    $entriesSamplesTableItemsAssembled = [];

    $entriesSamples = new EntriesSamples($this->CMSCore);

    /** @var array Массив объектов выборок */
    $entriesSamplesObjects = $entriesSamples->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $entriesSamples->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($entriesSamples);

    foreach ($entriesSamplesObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $objectID = $object->getID();
      $sortTypeID = $object->getSortTypeID();

      /** @var string Дата создания выборки в формате d.m.Y H:i:s */
      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      /** @var string Дата обновления выборки в формате d.m.Y H:i:s */
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      /** @var string Заголовок выборки */
      $entriesSampleTitle = $object->getTitle($localeName);
      $entriesSampleTitle = strip_tags($entriesSampleTitle);

      /** @var string Описание выборки */
      $entriesSampleDescription = $object->getDescription($localeName);
      $entriesSampleDescription = strip_tags($entriesSampleDescription);
      
      /** @var int Количество записей в выборке */
      $entriesSampleEntriesCount = $object->getEntriesCount();
      
      /** @var int Лимит на количество записей в выборке */
      $entriesSampleEntriesLimitCount = $object->getLimitCount();

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocalesList = $this->assemblyLocalesItems($completedLocalesData);

      $categories = $object->getCategories();
      $categoriesList = $this->assemblyCategoriesItems($localeName, $categories);

      $reflectionEnumSortType = new ReflectionEnum(EnumSortTypeID::class);
      $reflectionEnumSortTypeCases = $reflectionEnumSortType->getCases();
      $reflectionEnumSortTypeName = $reflectionEnumSortTypeCases[$sortTypeID - 1]->getName();
      $reflectionEnumSortTypeLabel = $localeData['PAGE_ENTRIES_SAMPLE_SORT_TYPE_' . $reflectionEnumSortTypeName . '_LABEL'];

      array_push($entriesSamplesTableItemsAssembled,
        ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/entriesSamples/tableItem.tpl',
          [
            'ENTRIES_SAMPLE_INDEX' => $index,
            'ENTRIES_SAMPLE_ID' => $objectID,
            'ENTRIES_SAMPLE_NAME' => $object->getName(),
            'ENTRIES_SAMPLE_TITLE' => $entriesSampleTitle,
            'ENTRIES_SAMPLE_DESCRIPTION' => $entriesSampleDescription,
            'ENTRIES_SAMPLE_ENTRIES_COUNT' => $entriesSampleEntriesCount,
            'ENTRIES_SAMPLE_ENTRIES_LIMIT_COUNT' => $entriesSampleEntriesLimitCount,
            'ENTRIES_SAMPLE_LOCALES_LIST' => $completedLocalesList,
            'ENTRIES_SAMPLE_CATEGORIES_LIST' => $categoriesList,
            'ENTRIES_SAMPLE_METHOD_SORT_LABEL' => $reflectionEnumSortTypeLabel,
            'ENTRIES_SAMPLE_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
            'ENTRIES_SAMPLE_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
          ]
        )
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/entriesSamples.tpl',
      [
        'PAGE_ENTRIES_SAMPLES_PAGINATION' => $pagination->assembled,
        'PAGE_ENTRIES_SAMPLES_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/entriesSamples/table.tpl',
          [
            'PAGE_ENTRIES_SAMPLES_TABLE_ITEMS' => implode($entriesSamplesTableItemsAssembled)
          ]
        )
      ]
    );
  }
}
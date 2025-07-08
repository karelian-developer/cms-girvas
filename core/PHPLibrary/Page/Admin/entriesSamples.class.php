<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin;

use \DOMDocument as DOMDocument;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\EntriesSamples as EntriesSamples;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageEntriesSamples implements InterfacePage
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

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesSamples.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    /** @var int Текущий номер страницы */
    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    /** @var int Максимальное количество элементов на странице */
    $paginationItemsOnPage = 12;

    // $entries_categories_table_items_assembled = [];
    $entriesSamples = new EntriesSamples($this->CMSCore);
    $entriesSamplesLocale = $this->CMSCore->getCMSLocale('admin');
    $entriesSamplesLocaleName = $entriesSamplesLocale->getName();

    /** @var array Массив объектов выборок */
    $entriesSamplesObjects = $entriesSamples->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $entriesSamples->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($entriesSamples);

    /** @var DOMDocument $dom_document Конструктор DOM-документа */
    $document = new DOMDocument();
    
    /** @var DOMElement $tableElement DOM-элемент таблицы */
    $tableElement = $document->createElement('table');
    $tableElement->setAttribute('class', 'table');
    
    /** @var DOMElement $tableRowHeaderElement DOM-элемент строки с заголовками колонок таблицы */
    $tableRowHeaderElement = $document->createElement('tr');

    /** @var array $tableCellsHeadersElements массив для DOM-элементов с заголовками колонок таблицы */
    $tableCellsHeadersElements = [];

    // Генерация ячеек для строки с заголовками колонок таблицы
    for ($i = 0; $i < 6; $i++) {
      /*
        * 0 => Индекс
        * 1 => Заголовок
        * 2 => Количество записей
        * 3 => Дата создания
        * 4 => Дата обновления
        * 5 => Панель
        */

      /** @var DOMElement $tableCellsHeadersElements[] DOM-элемент ячейки с заголовком колонки таблицы */
      $tableCellsHeadersElements[] = $document->createElement('th');
    }

    /* Перебор каждого DOM-элемента с заголовками колонок таблицы
      * для последующего назначения необходимых классов и стилей CSS
      */
    foreach ($tableCellsHeadersElements as $tableCellElement) {
      $tableCellElement->setAttribute('class', 'table__cell table__cell_header');
      $tableCellElement->setAttribute('style', 'font-weight: 700;');
    }

    // Присвоение значений заголовкам колонок таблицы
    $tableCellsHeadersElements[1]->nodeValue = $this->CMSCore->locale->getSingleValueByKey('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_TITLE_LABEL');
    $tableCellsHeadersElements[2]->nodeValue = $this->CMSCore->locale->getSingleValueByKey('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_COUNT_LABEL');
    $tableCellsHeadersElements[3]->nodeValue = $this->CMSCore->locale->getSingleValueByKey('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_CREATED_DATE_TIMESTAMP_LABEL');
    $tableCellsHeadersElements[4]->nodeValue = $this->CMSCore->locale->getSingleValueByKey('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_UPDATED_DATE_TIMESTAMP_LABEL');

    // Добавление DOM-элементов в DOM-элемент $tableRowHeaderElement
    foreach ($tableCellsHeadersElements as $index => $element) {
      $tableRowHeaderElement->appendChild($tableCellsHeadersElements[$index]);
    }
    
    // Добавление DOM-элемента в DOM-элемент $tableElement
    $tableElement->appendChild($tableRowHeaderElement);

    foreach ($entriesSamplesObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $objectID = $object->getID();

      /** @var string Дата создания выборки в формате d.m.Y H:i:s */
      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      /** @var string Дата обновления выборки в формате d.m.Y H:i:s */
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      /** @var string Заголовок выборки */
      $entriesSampleTitle = $object->getTitle($entriesSamplesLocaleName);
      $entriesSampleTitle = strip_tags($entriesSampleTitle);
      
      /** @var int Количество записей в выборке */
      $entriesSampleEntriesCount = $object->getEntriesCount();

      /** @var DOMElement $tableRowItemElement DOM-элемент строки */
      $tableRowItemElement = $document->createElement('tr');

      /** @var array $tableCellsItemElements массив для DOM-элементов со значениями для колонок */
      $tableCellsItemElements = [];

      // Генерация ячеек для строки с заголовками колонок таблицы
      for ($i = 0; $i < 6; $i++) {
        /*
        * 0 => Индекс
        * 1 => Заголовок
        * 2 => Количество записей
        * 3 => Дата создания
        * 4 => Дата обновления
        * 5 => Панель
        */

        /** @var DOMElement $tableCellsItemElements[] DOM-элемент ячейки */
        $tableCellsItemElements[] = $document->createElement('td');
        $tableCellsItemElements[$i]->setAttribute('class', 'table__cell table__cell');

<<<<<<< HEAD
      /* Перебор каждого DOM-элемента с заголовками колонок таблицы
       * для последующего назначения необходимых классов и стилей CSS
       */
      foreach ($table_cells_headers as $table_cell) {
        $table_cell->setAttribute('class', 'table__cell table__cell_header');
        $table_cell->setAttribute('style', 'font-weight: 700;');
      }

      // Присвоение значений заголовкам колонок таблицы
      $table_cells_headers[1]->nodeValue = $this->system_core->locale->get_single_value_by_key('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_TITLE_LABEL');
      $table_cells_headers[2]->nodeValue = $this->system_core->locale->get_single_value_by_key('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_COUNT_LABEL');
      $table_cells_headers[3]->nodeValue = $this->system_core->locale->get_single_value_by_key('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_CREATED_DATE_TIMESTAMP_LABEL');
      $table_cells_headers[4]->nodeValue = $this->system_core->locale->get_single_value_by_key('PAGE_ENTRIES_SAMPLES_TABLE_COLUMN_UPDATED_DATE_TIMESTAMP_LABEL');

      // Добавление DOM-элементов в DOM-элемент $table_row_header
      foreach ($table_cells_headers as $table_cell_index => $table_cell) {
        $table_row_header->appendChild($table_cells_headers[$table_cell_index]);
      }
      
      // Добавление DOM-элемента в DOM-элемент $table
      $table->appendChild($table_row_header);

      foreach ($entries_samples_array_objects as $entries_sample_index => $entries_sample_object) {
        $entries_sample_object->init_data(['id', 'texts', 'name', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);

        /** @var string Дата создания выборки в формате d.m.Y H:i:s */
        $created_date_timestamp = date('d.m.Y H:i:s', $entries_sample_object->get_created_unix_timestamp());
        /** @var string Дата обновления выборки в формате d.m.Y H:i:s */
        $updated_date_timestamp = date('d.m.Y H:i:s', $entries_sample_object->get_updated_unix_timestamp());

        /** @var string Заголовок выборки */
        $entries_sample_title = $entries_sample_object->get_title($locale_default->get_name());
        $entries_sample_title = strip_tags($entries_sample_title);
        
        /** @var int Количество записей в выборке */
        $entries_sample_count = $entries_sample_object->get_entries_count();

        /** @var DOMElement $table_row_item DOM-элемент строки */
        $table_row_item = $dom_document_samples_table->createElement('tr');

        /** @var array $table_cells_item массив для DOM-элементов со значениями для колонок */
        $table_cells_item = [];

        // Генерация ячеек для строки с заголовками колонок таблицы
        for ($i = 0; $i < 6; $i++) {
          /*
          * 0 => Индекс
          * 1 => Заголовок
          * 2 => Количество записей
          * 3 => Дата создания
          * 4 => Дата обновления
          * 5 => Панель
          */

          /** @var DOMElement $table_cells_item[] DOM-элемент ячейки */
          $table_cells_item[] = $dom_document_samples_table->createElement('td');
          $table_cells_item[$i]->setAttribute('class', 'table__cell table__cell');

          if ($i == 0) {
            $table_cells_item[$i]->nodeValue = $entries_sample_index + 1;
          }

          if ($i == 1) {
            $table_cell_link = $dom_document_samples_table->createElement('a');
            $table_cell_link->setAttribute('href', sprintf('/admin/entriesSample/%d', $entries_sample_object->get_id()));
            $table_cell_link->setAttribute('target', '_blank');
            $table_cell_link->nodeValue = $entries_sample_title;

            $table_cells_item[$i]->appendChild($table_cell_link);
          }

          if ($i == 2) {
            $table_cells_item[$i]->nodeValue = $entries_sample_count;
          }

          if ($i == 3) {
            $table_cells_item[$i]->nodeValue = $created_date_timestamp;
          }

          if ($i == 4) {
            $table_cells_item[$i]->nodeValue = $updated_date_timestamp;
          }

          $table_row_item->appendChild($table_cells_item[$i]);
=======
        if ($i == 0) {
          $tableCellsItemElements[$i]->nodeValue = $index + 1;
>>>>>>> develop
        }

        if ($i == 1) {
          $tableCellLinkElement = $document->createElement('a');
          $tableCellLinkElement->setAttribute('href', '/admin/entriesSample/' . $objectID);
          $tableCellLinkElement->setAttribute('target', '_blank');
          $tableCellLinkElement->nodeValue = $entriesSampleTitle;

          $tableCellsItemElements[$i]->appendChild($tableCellLinkElement);
        }

        if ($i == 2) {
          $tableCellsItemElements[$i]->nodeValue = $entriesSampleEntriesCount;
        }

        if ($i == 3) {
          $tableCellsItemElements[$i]->nodeValue = $createdUnixTimestamp;
        }

        if ($i == 4) {
          $tableCellsItemElements[$i]->nodeValue = $updatedUnixTimestamp;
        }

        $tableRowItemElement->appendChild($tableCellsItemElements[$i]);
      }

      $tableElement->appendChild($tableRowItemElement);
    }

    // Добавление DOM-элемента в конструктор DOM-документ
    $document->appendChild($tableElement);

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesSamples.tpl', [
      'PAGE_ENTRIES_SAMPLES_PAGINATION' => $pagination->assembled,
      'PAGE_ENTRIES_SAMPLES_TABLE' => $document->saveHTML()
    ]);
  }
}
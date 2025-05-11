<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \DOMDocument as DOMDocument;
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\EntriesSamples as EntriesSamples;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageEntriesSamples implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
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

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/entriesSamples.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      /** @var int Текущий номер страницы */
      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      /** @var int Максимальное количество элементов на странице */
      $pagination_items_on_page = 12;

      // $entries_categories_table_items_assembled = [];
      $entries_samples = new EntriesSamples($this->system_core);
      $locale_default = $this->system_core->get_cms_locale('admin');

      /** @var array Массив объектов выборок */
      $entries_samples_array_objects = $entries_samples->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $entries_samples->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($entries_samples);

      /** @var DOMDocument $dom_document Конструктор DOM-документа */
      $dom_document_samples_table = new DOMDocument();
      
      /** @var DOMElement $table DOM-элемент таблицы */
      $table = $dom_document_samples_table->createElement('table');
      $table->setAttribute('class', 'table');
      
      /** @var DOMElement $table_row_header DOM-элемент строки с заголовками колонок таблицы */
      $table_row_header = $dom_document_samples_table->createElement('tr');

      /** @var array $table_cells_headers массив для DOM-элементов с заголовками колонок таблицы */
      $table_cells_headers = [];

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

        /** @var DOMElement $table_cells_headers[] DOM-элемент ячейки с заголовком колонки таблицы */
        $table_cells_headers[] = $dom_document_samples_table->createElement('th');
      }

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
        }

        $table->appendChild($table_row_item);
      }

      // Добавление DOM-элемента в конструктор DOM-документ
      $dom_document_samples_table->appendChild($table);

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesSamples.tpl', [
        'PAGE_ENTRIES_SAMPLES_PAGINATION' => $pagination->assembled,
        'PAGE_ENTRIES_SAMPLES_TABLE' => $dom_document_samples_table->saveHTML()
      ]);
    }
  }
}

?>
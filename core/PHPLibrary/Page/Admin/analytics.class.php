<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\Page\Admin\Analytics\PageEntry as PageAnalyticsEntry;
  use \core\PHPLibrary\Page\Admin\Analytics\PagePage as PageAnalyticsPageStatic;
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Metrics as Metrics;
  use \core\PHPLibrary\EntryCategory as EntryCategory;
  use \core\PHPLibrary\PageStatic as PageStatic;
  use \core\PHPLibrary\Pages as PagesStatic;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;
  use \core\PHPLibrary\Users as Users;

/**
 * Страница со списком записей
 */
  class PageAnalytics implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param Page $page
     */
    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly_entries_table(array $entries = []) : string {
      $locale_data = $this->system_core->locale->get_data();

      $dom_document = new \DOMDocument('1.0');

      $template_element = $dom_document->createDocumentFragment();

      $table_element = $dom_document->createElement('table');
      $table_element->setAttribute('class', 'analytics__table table table_entries');

      $table_colgroup_element = $dom_document->createElement('colgroup');
      $table_col_index_element = $dom_document->createElement('col');
      $table_col_title_element = $dom_document->createElement('col');
      $table_col_views_element = $dom_document->createElement('col');

      $table_col_index_element->setAttribute('width', '10%');
      $table_col_title_element->setAttribute('width', '70%');
      $table_col_views_element->setAttribute('width', '20%');

      $table_colgroup_element->appendChild($table_col_index_element);
      $table_colgroup_element->appendChild($table_col_title_element);
      $table_colgroup_element->appendChild($table_col_views_element);
      $table_element->appendChild($table_colgroup_element);

      $table_row_header_element = $dom_document->createElement('tr');
      $table_row_header_element->setAttribute('class', 'table__row');

      $table_cell_index_header_element = $dom_document->createElement('th');
      $table_cell_title_header_element = $dom_document->createElement('th');
      $table_cell_views_header_element = $dom_document->createElement('th');
      //$table_cell_panel_header_element = $dom_document->createElement('th');

      $table_cell_index_header_element->setAttribute('class', 'table__cell table__cell_header');
      $table_cell_title_header_element->setAttribute('class', 'table__cell table__cell_header');
      $table_cell_views_header_element->setAttribute('class', 'table__cell table__cell_header');
      //$table_cell_panel_header_element->setAttribute('class', 'table__cell table__cell_header');

      $table_cell_index_header_text = $dom_document->createTextNode('');
      $table_cell_title_header_text = $dom_document->createTextNode($locale_data['PAGE_ANALYTICS_TABLE_COLUMN_TITLE_TITLE']);
      $table_cell_views_header_text = $dom_document->createTextNode($locale_data['PAGE_ANALYTICS_TABLE_COLUMN_VIEWS_TITLE']);
      //$table_cell_panel_header_text = $dom_document->createTextNode('');

      $table_cell_index_header_element->appendChild($table_cell_index_header_text);
      $table_cell_title_header_element->appendChild($table_cell_title_header_text);
      $table_cell_views_header_element->appendChild($table_cell_views_header_text);
      //$table_cell_panel_header_element->appendChild($table_cell_panel_header_text);

      $table_row_header_element->appendChild($table_cell_index_header_element);
      $table_row_header_element->appendChild($table_cell_title_header_element);
      $table_row_header_element->appendChild($table_cell_views_header_element);
      //$table_row_header_element->appendChild($table_cell_panel_header_element);
      $table_element->appendChild($table_row_header_element);

      if (!empty($entries)) {
        $locale_default = $this->system_core->get_cms_locale('base');

        $entry_index = 1;
        foreach ($entries as $entry) {
          $entry->init_data(['id', 'texts', 'name']);

          $entry_title = $entry->get_title($locale_default->get_name());

          $entry_title = (!empty($entry_title)) ? $entry_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $locale_default->get_name());

          $table_row_element = $dom_document->createElement('tr');
          $table_row_element->setAttribute('class', 'table__row');

          $table_cell_index_element = $dom_document->createElement('td');
          $table_cell_title_element = $dom_document->createElement('td');
          $table_cell_views_element = $dom_document->createElement('td');
          //$table_cell_panel_element = $dom_document->createElement('td');

          $table_cell_index_element->setAttribute('class', 'table__cell table__cell_index');
          $table_cell_title_element->setAttribute('class', 'table__cell table__cell_title');
          $table_cell_views_element->setAttribute('class', 'table__cell table__cell_views');
          //$table_cell_panel_element->setAttribute('class', 'table__cell table__cell_panel');
          //$table_cell_panel_element->setAttribute('role', 'item-panel');

          $table_cell_title_link_element = $dom_document->createElement('a');
          $table_cell_title_link_element->setAttribute('href', $entry->get_url());
          $table_cell_title_link_element->setAttribute('target', '_blank');

          $table_cell_index_text = $dom_document->createTextNode(sprintf('#%d', $entry_index));
          $table_cell_title_text = $dom_document->createTextNode(html_entity_decode($entry_title));
          $table_cell_views_text = $dom_document->createTextNode($entry->get_views_count());
          //$table_cell_panel_text = $dom_document->createTextNode('');
          
          $table_cell_title_link_element->appendChild($table_cell_title_text);

          $table_cell_index_element->appendChild($table_cell_index_text);
          $table_cell_title_element->appendChild($table_cell_title_link_element);
          $table_cell_views_element->appendChild($table_cell_views_text);
          //$table_cell_panel_element->appendChild($table_cell_panel_text);

          $table_row_element->appendChild($table_cell_index_element);
          $table_row_element->appendChild($table_cell_title_element);
          $table_row_element->appendChild($table_cell_views_element);
          //$table_row_element->appendChild($table_cell_panel_element);
          $table_element->appendChild($table_row_element);

          $entry_index++;
        }
      }

      $template_element->appendChild($table_element);
      $dom_document->appendChild($template_element);

      return $dom_document->saveHTML();
    }

    public function assembly_pages_table(array $pages = []) : string {
      $locale_data = $this->system_core->locale->get_data();

      $dom_document = new \DOMDocument('1.0');

      $template_element = $dom_document->createDocumentFragment();

      $table_element = $dom_document->createElement('table');
      $table_element->setAttribute('class', 'analytics__table table table_pages');

      $table_colgroup_element = $dom_document->createElement('colgroup');
      $table_col_index_element = $dom_document->createElement('col');
      $table_col_title_element = $dom_document->createElement('col');
      $table_col_views_element = $dom_document->createElement('col');

      $table_col_index_element->setAttribute('width', '10%');
      $table_col_title_element->setAttribute('width', '70%');
      $table_col_views_element->setAttribute('width', '20%');

      $table_colgroup_element->appendChild($table_col_index_element);
      $table_colgroup_element->appendChild($table_col_title_element);
      $table_colgroup_element->appendChild($table_col_views_element);
      $table_element->appendChild($table_colgroup_element);

      $table_row_header_element = $dom_document->createElement('tr');
      $table_row_header_element->setAttribute('class', 'table__row');

      $table_cell_index_header_element = $dom_document->createElement('th');
      $table_cell_title_header_element = $dom_document->createElement('th');
      $table_cell_views_header_element = $dom_document->createElement('th');
      //$table_cell_panel_header_element = $dom_document->createElement('th');

      $table_cell_index_header_element->setAttribute('class', 'table__cell table__cell_header');
      $table_cell_title_header_element->setAttribute('class', 'table__cell table__cell_header');
      $table_cell_views_header_element->setAttribute('class', 'table__cell table__cell_header');
      //$table_cell_panel_header_element->setAttribute('class', 'table__cell table__cell_header');

      $table_cell_index_header_text = $dom_document->createTextNode('');
      $table_cell_title_header_text = $dom_document->createTextNode($locale_data['PAGE_ANALYTICS_TABLE_COLUMN_TITLE_TITLE']);
      $table_cell_views_header_text = $dom_document->createTextNode($locale_data['PAGE_ANALYTICS_TABLE_COLUMN_VIEWS_TITLE']);
      //$table_cell_panel_header_text = $dom_document->createTextNode('');

      $table_cell_index_header_element->appendChild($table_cell_index_header_text);
      $table_cell_title_header_element->appendChild($table_cell_title_header_text);
      $table_cell_views_header_element->appendChild($table_cell_views_header_text);
      //$table_cell_panel_header_element->appendChild($table_cell_panel_header_text);

      $table_row_header_element->appendChild($table_cell_index_header_element);
      $table_row_header_element->appendChild($table_cell_title_header_element);
      $table_row_header_element->appendChild($table_cell_views_header_element);
      //$table_row_header_element->appendChild($table_cell_panel_header_element);
      $table_element->appendChild($table_row_header_element);

      if (!empty($pages)) {
        $locale_default = $this->system_core->get_cms_locale('base');

        $page_index = 1;
        foreach ($pages as $page) {
          $page->init_data(['id', 'texts', 'name']);

          $page_title = $page->get_title($locale_default->get_name());

          $page_title = (!empty($page_title)) ? $page_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $locale_default->get_name());

          $table_row_element = $dom_document->createElement('tr');
          $table_row_element->setAttribute('class', 'table__row');

          $table_cell_index_element = $dom_document->createElement('td');
          $table_cell_title_element = $dom_document->createElement('td');
          $table_cell_views_element = $dom_document->createElement('td');
          //$table_cell_panel_element = $dom_document->createElement('td');

          $table_cell_index_element->setAttribute('class', 'table__cell table__cell_index');
          $table_cell_title_element->setAttribute('class', 'table__cell table__cell_title');
          $table_cell_views_element->setAttribute('class', 'table__cell table__cell_views');
          //$table_cell_panel_element->setAttribute('class', 'table__cell table__cell_panel');
          //$table_cell_panel_element->setAttribute('role', 'item-panel');

          $table_cell_title_link_element = $dom_document->createElement('a');
          $table_cell_title_link_element->setAttribute('href', $page->get_url());
          $table_cell_title_link_element->setAttribute('target', '_blank');

          $table_cell_index_text = $dom_document->createTextNode(sprintf('#%d', $page_index));
          $table_cell_title_text = $dom_document->createTextNode(html_entity_decode($page_title));
          $table_cell_views_text = $dom_document->createTextNode($page->get_views_count());
          //$table_cell_panel_text = $dom_document->createTextNode('');
          
          $table_cell_title_link_element->appendChild($table_cell_title_text);

          $table_cell_index_element->appendChild($table_cell_index_text);
          $table_cell_title_element->appendChild($table_cell_title_link_element);
          $table_cell_views_element->appendChild($table_cell_views_text);
          //$table_cell_panel_element->appendChild($table_cell_panel_text);

          $table_row_element->appendChild($table_cell_index_element);
          $table_row_element->appendChild($table_cell_title_element);
          $table_row_element->appendChild($table_cell_views_element);
          //$table_row_element->appendChild($table_cell_panel_element);
          $table_element->appendChild($table_row_element);

          $page_index++;
        }
      }

      $template_element->appendChild($table_element);
      $dom_document->appendChild($template_element);

      return $dom_document->saveHTML();
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->system_core->template->add_style(['href' => 'styles/page/analytics.css', 'rel' => 'stylesheet']);
      
      $cms_locale_setted_name = $this->system_core->configurator->get_database_entry_value('base_admin_locale');
      $url_locale_setted_name = $this->system_core->urlp->get_param('locale');
      $cookie_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;
      
      $cms_locale_name = (!is_null($url_locale_setted_name)) ? $url_locale_setted_name : $cookie_locale_setted_name;
      $cms_locale_name = (!is_null($cms_locale_name)) ? $cms_locale_name : $cms_locale_setted_name;
      $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_name, 'admin');
      if (!$cms_locale->exists_file_data_json()) {
        $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_setted_name, 'admin');
        $cms_locale_name = $cms_locale_setted_name;
      }

      $this->system_core->locale = $cms_locale;
      $locale_data = $this->system_core->locale->get_data();

      if ($this->system_core->urlp->get_path(2) == 'entry' && !is_null($this->system_core->urlp->get_path(3))) {
        $entry = null;
        $entry_id = (is_numeric($this->system_core->urlp->get_path(3))) ? (int)$this->system_core->urlp->get_path(3) : 0;
        $entry = (Entry::exists_by_id($this->system_core, $entry_id)) ? new Entry($this->system_core, $entry_id) : null;
        
        if (!is_null($entry)) {
          $entry->init_data(['id', 'texts', 'name']);

          $page = new PageAnalyticsEntry($this->system_core, $this->page, $entry);
          $page->assembly();

          $this->assembled = $page->assembled;
        } else {
          http_response_code(404);

          $page_error = new PageError($this->system_core, $this->page, 404);
          $page_error->assembly();

          $this->assembled = $page_error->assembled;
        }
      } elseif ($this->system_core->urlp->get_path(2) == 'page' && !is_null($this->system_core->urlp->get_path(3))) {
        $page_static = null;
        $page_static_id = (is_numeric($this->system_core->urlp->get_path(3))) ? (int)$this->system_core->urlp->get_path(3) : 0;
        $page_static = (PageStatic::exists_by_id($this->system_core, $page_static_id)) ? new PageStatic($this->system_core, $page_static_id) : null;
        
        if (!is_null($page_static)) {
          $page_static->init_data(['id', 'texts', 'name']);

          $page = new PageAnalyticsPageStatic($this->system_core, $this->page, $page_static);
          $page->assembly();

          $this->assembled = $page->assembled;
        } else {
          http_response_code(404);

          $page_error = new PageError($this->system_core, $this->page, 404);
          $page_error->assembly();
          
          $this->assembled = $page_error->assembled;
        }
      } else {
        /** @var array Преобразованные элементы навигации */
        $navigations_items_transformed = [];
        array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
          'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_ENTRIES_NAVIGATION_INDEX_LABEL']),
          'NAVIGATION_ITEM_URL' => '/admin',
          'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
        ]));

        if (!empty($navigations_items_transformed)) {
          $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
            'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
              'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
            ])
          ]);
        } else {
          $page_navigation_transformed = '';
        }

        $metrics = new Metrics($this->system_core);
        $metrics_entries = $metrics->get_entries_views_by_timestamp(time());
        $metrics_pages = $metrics->get_pages_views_by_timestamp(time());

        if (!empty($metrics_entries)) {
          usort($metrics_entries, function ($a, $b) {
            if ($a->get_views_count() != $b->get_views_count()) {
              return ($a->get_views_count() < $b->get_views_count()) ? 1 : -1;
            }

            return 0;
          });
    
          $entries_table_assembled = $this->assembly_entries_table($metrics_entries);
        } else {
          $entries_table_assembled = '';
        }

        if (!empty($metrics_pages)) {
          usort($metrics_pages, function ($a, $b) {
            if ($a->get_views_count() != $b->get_views_count()) {
              return ($a->get_views_count() < $b->get_views_count()) ? 1 : -1;
            }

            return 0;
          });
    
          $pages_table_assembled = $this->assembly_pages_table($metrics_pages);
        } else {
          $pages_table_assembled = '';
        }

        /** @var string $site_page Содержимое шаблона страницы */
        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/analytics.tpl', [
          'PAGE_NAVIGATION' => $page_navigation_transformed,
          'ADMIN_PANEL_PAGE_NAME' => 'analytics',
          'ENTRIES_LIST_ITEMS' => $entries_table_assembled,
          'PAGES_LIST_ITEMS' => $pages_table_assembled
        ]);
      }
    }

  }

}

?>
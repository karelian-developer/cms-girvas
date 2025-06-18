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
  use \DOMDocument as DOMDocument;

/**
 * Страница со списком записей
 */
  class PageAnalytics implements InterfacePage {
    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $CMSCore
     * @param Page $page
     */
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    public function assembly_entries_table(array $entries = []) : string {
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $document = new DOMDocument('1.0');

      $documentFragment = $document->createDocumentFragment();

      $tableElement = $document->createElement('table');
      $tableElement->setAttribute('class', 'analytics__table table table_entries');

      $tableColgroupElement = $document->createElement('colgroup');
      $tableColIndexElement = $document->createElement('col');
      $tableColTitleElement = $document->createElement('col');
      $tableColViewsElement = $document->createElement('col');

      $tableColIndexElement->setAttribute('width', '10%');
      $tableColTitleElement->setAttribute('width', '70%');
      $tableColViewsElement->setAttribute('width', '20%');

      $tableColgroupElement->appendChild($tableColIndexElement);
      $tableColgroupElement->appendChild($tableColTitleElement);
      $tableColgroupElement->appendChild($tableColViewsElement);
      $tableElement->appendChild($tableColgroupElement);

      $tableRowHeaderElement = $document->createElement('tr');
      $tableRowHeaderElement->setAttribute('class', 'table__row');

      $tableCellIndexHeaderElement = $document->createElement('th');
      $tableCellTitleHeaderElement = $document->createElement('th');
      $tableCellViewsHeaderElement = $document->createElement('th');

      $tableCellIndexHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');
      $tableCellTitleHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');
      $tableCellViewsHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');

      $tableCellIndexHeaderText = $document->createTextNode('');
      $tableCellTitleHeaderText = $document->createTextNode($localeData['PAGE_ANALYTICS_TABLE_COLUMN_TITLE_TITLE']);
      $tableCellViewsHeaderText = $document->createTextNode($localeData['PAGE_ANALYTICS_TABLE_COLUMN_VIEWS_TITLE']);

      $tableCellIndexHeaderElement->appendChild($tableCellIndexHeaderText);
      $tableCellTitleHeaderElement->appendChild($tableCellTitleHeaderText);
      $tableCellViewsHeaderElement->appendChild($tableCellViewsHeaderText);

      $tableRowHeaderElement->appendChild($tableCellIndexHeaderElement);
      $tableRowHeaderElement->appendChild($tableCellTitleHeaderElement);
      $tableRowHeaderElement->appendChild($tableCellViewsHeaderElement);
      $tableElement->appendChild($tableRowHeaderElement);

      if (!empty($entries)) {
        $locale = $this->CMSCore->get_cms_locale('admin');

        $entryIndex = 1;
        foreach ($entries as $entry) {
          $entry->init_data(['id', 'texts', 'name']);

          $entryTitle = $entry->get_title($localeName);

          $entryTitle = !empty($entryTitle) ? $entryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $localeName);

          $tableRowElement = $document->createElement('tr');
          $tableRowElement->setAttribute('class', 'table__row');

          $tableCellIndexElement = $document->createElement('td');
          $tableCellTitleElement = $document->createElement('td');
          $tableCellViewsElement = $document->createElement('td');

          $tableCellIndexElement->setAttribute('class', 'table__cell cell table__cell_index');
          $tableCellTitleElement->setAttribute('class', 'table__cell cell table__cell_title');
          $tableCellViewsElement->setAttribute('class', 'table__cell cell table__cell_views');

          $tableCellTitleLinkElement = $document->createElement('a');
          $tableCellTitleLinkElement->setAttribute('href', $entry->get_url());
          $tableCellTitleLinkElement->setAttribute('target', '_blank');

          $tableCellIndexText = $document->createTextNode(sprintf('#%d', $entryIndex));
          $tableCellTitleText = $document->createTextNode(html_entity_decode($entryTitle));
          $tableCellViewsText = $document->createTextNode($entry->get_views_count());
          
          $tableCellTitleLinkElement->appendChild($tableCellTitleText);

          $tableCellIndexElement->appendChild($tableCellIndexText);
          $tableCellTitleElement->appendChild($tableCellTitleLinkElement);
          $tableCellViewsElement->appendChild($tableCellViewsText);

          $tableRowElement->appendChild($tableCellIndexElement);
          $tableRowElement->appendChild($tableCellTitleElement);
          $tableRowElement->appendChild($tableCellViewsElement);
          $tableElement->appendChild($tableRowElement);

          $entryIndex++;
        }
      }

      $documentFragment->appendChild($tableElement);
      $document->appendChild($documentFragment);

      return $document->saveHTML();
    }

    public function assembly_pages_table(array $pages = []) : string {
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $document = new DOMDocument('1.0');

      $documentFragment = $document->createDocumentFragment();

      $tableElement = $document->createElement('table');
      $tableElement->setAttribute('class', 'analytics__table table table_pages');

      $tableColgroupElement = $document->createElement('colgroup');
      $tableColIndexElement = $document->createElement('col');
      $tableColTitleElement = $document->createElement('col');
      $tableColViewsElement = $document->createElement('col');

      $tableColIndexElement->setAttribute('width', '10%');
      $tableColTitleElement->setAttribute('width', '70%');
      $tableColViewsElement->setAttribute('width', '20%');

      $tableColgroupElement->appendChild($tableColIndexElement);
      $tableColgroupElement->appendChild($tableColTitleElement);
      $tableColgroupElement->appendChild($tableColViewsElement);
      $tableElement->appendChild($tableColgroupElement);

      $tableRowHeaderElement = $document->createElement('tr');
      $tableRowHeaderElement->setAttribute('class', 'table__row');

      $tableCellIndexHeaderElement = $document->createElement('th');
      $tableCellTitleHeaderElement = $document->createElement('th');
      $tableCellViewsHeaderElement = $document->createElement('th');

      $tableCellIndexHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');
      $tableCellTitleHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');
      $tableCellViewsHeaderElement->setAttribute('class', 'table__cell cell table__cell_header');

      $tableCellIndexHeaderText = $document->createTextNode('');
      $tableCellTitleHeaderText = $document->createTextNode($localeData['PAGE_ANALYTICS_TABLE_COLUMN_TITLE_TITLE']);
      $tableCellViewsHeaderText = $document->createTextNode($localeData['PAGE_ANALYTICS_TABLE_COLUMN_VIEWS_TITLE']);

      $tableCellIndexHeaderElement->appendChild($tableCellIndexHeaderText);
      $tableCellTitleHeaderElement->appendChild($tableCellTitleHeaderText);
      $tableCellViewsHeaderElement->appendChild($tableCellViewsHeaderText);

      $tableRowHeaderElement->appendChild($tableCellIndexHeaderElement);
      $tableRowHeaderElement->appendChild($tableCellTitleHeaderElement);
      $tableRowHeaderElement->appendChild($tableCellViewsHeaderElement);
      $tableElement->appendChild($tableRowHeaderElement);

      if (!empty($pages)) {
        $locale = $this->CMSCore->get_cms_locale('admin');

        $pageIndex = 1;
        foreach ($pages as $page) {
          $page->init_data(['id', 'texts', 'name']);

          $pageTitle = $page->get_title($localeName);

          $pageTitle = !empty($pageTitle) ? $pageTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $localeName);

          $tableRowElement = $document->createElement('tr');
          $tableRowElement->setAttribute('class', 'table__row');

          $tableCellIndexElement = $document->createElement('td');
          $tableCellTitleElement = $document->createElement('td');
          $tableCellViewsElement = $document->createElement('td');

          $tableCellIndexElement->setAttribute('class', 'table__cell cell table__cell_index');
          $tableCellTitleElement->setAttribute('class', 'table__cell cell table__cell_title');
          $tableCellViewsElement->setAttribute('class', 'table__cell cell table__cell_views');

          $tableCellTitleLinkElement = $document->createElement('a');
          $tableCellTitleLinkElement->setAttribute('href', $page->get_url());
          $tableCellTitleLinkElement->setAttribute('target', '_blank');

          $tableCellIndexText = $document->createTextNode(sprintf('#%d', $pageIndex));
          $tableCellTitleText = $document->createTextNode(html_entity_decode($pageTitle));
          $tableCellViewsText = $document->createTextNode($page->get_views_count());
          
          $tableCellTitleLinkElement->appendChild($tableCellTitleText);

          $tableCellIndexElement->appendChild($tableCellIndexText);
          $tableCellTitleElement->appendChild($tableCellTitleLinkElement);
          $tableCellViewsElement->appendChild($tableCellViewsText);

          $tableRowElement->appendChild($tableCellIndexElement);
          $tableRowElement->appendChild($tableCellTitleElement);
          $tableRowElement->appendChild($tableCellViewsElement);
          $tableElement->appendChild($tableRowElement);

          $pageIndex++;
        }
      }

      $documentFragment->appendChild($tableElement);
      $document->appendChild($documentFragment);

      return $document->saveHTML();
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->CMSCore->theme->add_style(['href' => 'styles/page/analytics.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();

      if ($this->CMSCore->urlp->get_path(2) == 'entry' && !is_null($this->CMSCore->urlp->get_path(3))) {
        $entry = null;
        $entryID = (is_numeric($this->CMSCore->urlp->get_path(3))) ? (int)$this->CMSCore->urlp->get_path(3) : 0;
        $entry = (Entry::exists_by_id($this->CMSCore, $entryID)) ? new Entry($this->CMSCore, $entryID) : null;
        
        if (!is_null($entry)) {
          $entry->init_data(['id', 'texts', 'name']);

          $page = new PageAnalyticsEntry($this->CMSCore, $this->page, $entry);
          $page->assembly();

          $this->assembled = $page->assembled;
        } else {
          http_response_code(404);

          $pageError = new PageError($this->CMSCore, $this->page, 404);
          $pageError->assembly();

          $this->assembled = $pageError->assembled;
        }
      } elseif ($this->CMSCore->urlp->get_path(2) == 'page' && !is_null($this->CMSCore->urlp->get_path(3))) {
        $pageStatic = null;
        $pageStaticID = (is_numeric($this->CMSCore->urlp->get_path(3))) ? (int)$this->CMSCore->urlp->get_path(3) : 0;
        $pageStatic = (PageStatic::exists_by_id($this->CMSCore, $pageStaticID)) ? new PageStatic($this->CMSCore, $pageStaticID) : null;
        
        if (!is_null($pageStatic)) {
          $pageStatic->init_data(['id', 'texts', 'name']);

          $page = new PageAnalyticsPageStatic($this->CMSCore, $this->page, $pageStatic);
          $page->assembly();

          $this->assembled = $page->assembled;
        } else {
          http_response_code(404);

          $pageError = new PageError($this->CMSCore, $this->page, 404);
          $pageError->assembly();
          
          $this->assembled = $pageError->assembled;
        }
      } else {
        /** @var array Преобразованные элементы навигации */
        $navigationsItemsTransformed = [];
        array_push($navigationsItemsTransformed, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/navigationHorizontal/item.tpl', [
          'NAVIGATION_ITEM_TITLE' => sprintf('< %s', '{LANG:PAGE_ENTRIES_NAVIGATION_INDEX_LABEL}'),
          'NAVIGATION_ITEM_URL' => '/admin',
          'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
        ]));

        if (!empty($navigationsItemsTransformed)) {
          $pageNavigationTransformed = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/navigationHorizontal.tpl', [
            'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/navigationHorizontal/list.tpl', [
              'NAVIGATION_ITEMS' => implode($navigationsItemsTransformed)
            ])
          ]);
        } else {
          $pageNavigationTransformed = '';
        }

        $metrics = new Metrics($this->CMSCore);
        $metricsEntries = $metrics->get_entries_views_by_timestamp(time());
        $metricsPages = $metrics->get_pages_views_by_timestamp(time());

        if (!empty($metricsEntries)) {
          usort($metricsEntries, function ($a, $b) {
            if ($a->get_views_count() != $b->get_views_count()) {
              return ($a->get_views_count() < $b->get_views_count()) ? 1 : -1;
            }

            return 0;
          });
    
          $entriesTableAssembled = $this->assembly_entries_table($metricsEntries);
        } else {
          $entriesTableAssembled = '';
        }

        if (!empty($metricsPages)) {
          usort($metricsPages, function ($a, $b) {
            if ($a->get_views_count() != $b->get_views_count()) {
              return ($a->get_views_count() < $b->get_views_count()) ? 1 : -1;
            }

            return 0;
          });
    
          $pagesTableAssembled = $this->assembly_pages_table($metricsPages);
        } else {
          $pagesTableAssembled = '';
        }

        /** @var string $site_page Содержимое шаблона страницы */
        $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/analytics.tpl', [
          'PAGE_NAVIGATION' => $pageNavigationTransformed,
          'ADMIN_PANEL_PAGE_NAME' => 'analytics',
          'ENTRIES_LIST_ITEMS' => $entriesTableAssembled,
          'PAGES_LIST_ITEMS' => $pagesTableAssembled
        ]);
      }
    }

  }

}

?>
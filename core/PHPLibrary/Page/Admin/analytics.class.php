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

use \core\PHPLibrary\Page\Admin\Analytics\PageEntry as PageAnalyticsEntry;
use \core\PHPLibrary\Page\Admin\Analytics\PagePage as PageAnalyticsPageStatic;
use \core\PHPLibrary\Page\Admin\Analytics\PageForms as PageAnalyticsForms;
use \core\PHPLibrary\Page\Admin\Analytics\PageForm as PageAnalyticsForm;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Metrics as Metrics;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\Pages as PagesStatic;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Form as Form;
use \core\PHPLibrary\Forms as Forms;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\Users as Users;
use \core\PHPLibrary\URLParser as CMSURLP;
use \DOMDocument as DOMDocument;

/**
 * Страница со списком записей
 */
class PageAnalytics implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ANALYTICS_NAVIGATION_%s_LABEL';

  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/',
      'permanent' => true,
      'isActive' => false
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/analytics/forms',
      'permanent' => false,
      'isActive' => false
    ]
  ];

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   * @param InterfacePage $page
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public InterfacePage $page
  ) {}

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
   * Сборка таблицы с записями
   * 
   * @param array $entries
   * 
   * @return string
   */
  public function assemblyEntriesTable(array $entries = []) : string
  {
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

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
      $locale = $this->CMSCore->getCMSLocale('admin');

      $entryIndex = 1;
      foreach ($entries as $entry) {
        $entry->initData(['id', 'texts', 'name']);

        $entryTitle = $entry->getTitle($localeName);

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
        $tableCellTitleLinkElement->setAttribute('href', $entry->getURL());
        $tableCellTitleLinkElement->setAttribute('target', '_blank');

        $tableCellIndexText = $document->createTextNode(sprintf('#%d', $entryIndex));
        $tableCellTitleText = $document->createTextNode(html_entity_decode($entryTitle));
        $tableCellViewsText = $document->createTextNode($entry->getViewsCount());
        
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

  public function assemblyPagesTable(array $pages = []) : string
  {
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

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
      $locale = $this->CMSCore->getCMSLocale('admin');

      $pageIndex = 1;
      foreach ($pages as $page) {
        $page->initData(['id', 'texts', 'name']);

        $pageTitle = $page->getTitle($localeName);

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
        $tableCellTitleLinkElement->setAttribute('href', $page->getURL());
        $tableCellTitleLinkElement->setAttribute('target', '_blank');

        $tableCellIndexText = $document->createTextNode(sprintf('#%d', $pageIndex));
        $tableCellTitleText = $document->createTextNode(html_entity_decode($pageTitle));
        $tableCellViewsText = $document->createTextNode($page->getViewsCount());
        
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
  public function assembly() : void
  {
    $CMSCore = $this->CMSCore;
    $CMSURLP = $CMSCore->urlp;
    $CMSTheme = $CMSCore->theme;
    $CMSLocale = $CMSCore->locale;

    // Добавление таблицы стилей для страницы
    $CMSTheme->addStyle(['href' => 'styles/page/analytics.css', 'rel' => 'stylesheet']);
    
    $localeData = $CMSLocale->getData();
    $localeName = $CMSLocale->getName();

    if ($CMSURLP->getPath(2) === 'entry' && $CMSURLP->getPath(3) !== null) {
      $entry = null;
      $entryID = $this->getContentEntityIDFromURL($this->CMSCore, $CMSURLP);
      $entry = $this->getEntryObjectByID($this->CMSCore, $entryID);
      
      if ($entry !== null) {
        $entry->initData(['id', 'texts', 'name']);

        $page = new PageAnalyticsEntry($this->CMSCore, $this->page, $entry);
        
        if (property_exists($page, 'navigationSubsections')) {
          $this->navigationSubsections = $page->navigationSubsections;
        }

        $page->assembly();

        $this->assembled = $page->assembled;
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();

        $this->assembled = $pageError->assembled;
      }
    } elseif ($CMSURLP->getPath(2) === 'page' && $CMSURLP->getPath(3) !== null) {
      $pageStatic = null;
      $pageStaticID = $this->getContentEntityIDFromURL($this->CMSCore, $CMSURLP);
      $pageStatic = $this->getPageStaticObjectByID($this->CMSCore, $pageStaticID);
      
      if ($pageStatic !== null) {
        $pageStatic->initData(['id', 'texts', 'name']);

        $page = new PageAnalyticsPageStatic($this->CMSCore, $this->page, $pageStatic);
        
        if (property_exists($page, 'navigationSubsections')) {
          $this->navigationSubsections = $page->navigationSubsections;
        }

        $page->assembly();

        $this->assembled = $page->assembled;
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();
        
        $this->assembled = $pageError->assembled;
      }
    } elseif ($CMSURLP->getPath(2) === 'forms') {
      $this->CMSCore->theme->addStyle(['href' => 'styles/page/analytics/forms.css', 'rel' => 'stylesheet']);

      $page = new PageAnalyticsForms($this->CMSCore, $this->page);
        
      if (property_exists($page, 'navigationSubsections')) {
        $this->navigationSubsections = $page->navigationSubsections;
      }

      $page->assembly();

      $this->assembled = $page->assembled;
    } elseif ($CMSURLP->getPath(2) === 'form' && $CMSURLP->getPath(3) !== null) {
      $form = null;
      $formID = $this->getContentEntityIDFromURL($this->CMSCore, $CMSURLP);
      $form = $this->getFormObjectByID($this->CMSCore, $formID);
      
      if ($form !== null) {
        $this->CMSCore->theme->addStyle(['href' => 'styles/page/analytics/form.css', 'rel' => 'stylesheet']);

        $page = new PageAnalyticsForm($this->CMSCore, $this->page, $form);
          
        if (property_exists($page, 'navigationSubsections')) {
          $this->navigationSubsections = $page->navigationSubsections;
        }

        $page->assembly();

        $this->assembled = $page->assembled;
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();
        
        $this->assembled = $pageError->assembled;
      }
    } else {
      $metrics = new Metrics($this->CMSCore);
      $metricsEntries = $metrics->getEntriesViewsByTimestamp(time());
      $metricsPages = $metrics->getPagesViewsByTimestamp(time());

      if (!empty($metricsEntries)) {
        usort($metricsEntries, function ($a, $b)
        {
          if ($a->getViewsCount() !== $b->getViewsCount()) {
            return $a->getViewsCount() < $b->getViewsCount() ? 1 : -1;
          }

          return 0;
        });
  
        $entriesTableAssembled = $this->assemblyEntriesTable($metricsEntries);
      } else {
        $entriesTableAssembled = '';
      }

      if (!empty($metricsPages)) {
        usort($metricsPages, function ($a, $b)
        {
          if ($a->getViewsCount() !== $b->getViewsCount()) {
            return $a->getViewsCount() < $b->getViewsCount() ? 1 : -1;
          }

          return 0;
        });
  
        $pagesTableAssembled = $this->assemblyPagesTable($metricsPages);
      } else {
        $pagesTableAssembled = '';
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = ThemeCollector::assemblyFileContent(
        $CMSTheme,
        'templates/page/analytics.tpl',
        [
          'ADMIN_PANEL_PAGE_NAME' => 'analytics',
          'ENTRIES_LIST_ITEMS' => $entriesTableAssembled,
          'PAGES_LIST_ITEMS' => $pagesTableAssembled
        ]
      );
    }
  }

  /**
   * Получение объекта статической страницы по ID
   * 
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return ?EntityTypeContent
   */
  private function getPageStaticObjectByID(CoreInterface $CMSCore, int $id) : ?EntityTypeContent
  {
    return Form::existsByID($CMSCore, $id)
      ? new Form($CMSCore, $id)
      : null;
  }

  /**
   * Получение объекта формы по ID
   * 
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return ?EntityTypeContent
   */
  private function getFormObjectByID(CoreInterface $CMSCore, int $id) : ?EntityTypeContent
  {
    return Form::existsByID($CMSCore, $id)
      ? new Form($CMSCore, $id)
      : null;
  }

  /**
   * Получение объекта записи по ID
   * 
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return ?EntityTypeContent
   */
  private function getEntryObjectByID(CoreInterface $CMSCore, int $id) : ?EntityTypeContent
  {
    return Entry::existsByID($CMSCore, $id)
      ? new Entry($CMSCore, $id)
      : null;
  }

  /**
   * Получение ID сущности контента через URL
   * 
   * @param CoreInterface $CMSCore
   * @param CMSURLP $CMSURLP
   * 
   * @return int
   */
  private function getContentEntityIDFromURL(CoreInterface $CMSCore, CMSURLP $CMSURLP) : int
  {
    return is_numeric($CMSURLP->getPath(3))
      ? (int) $CMSURLP->getPath(3)
      : 0;
  }
}
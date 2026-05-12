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

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \DOMDocument as DOMDocument;

class PageArchive implements InterfacePage
{
  public CMSCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param CMSCore $CMSCore
   * @param Page $page
   * @return void
   */
  public function __construct(CMSCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Добавление обязательных CSS-файлов
   *
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/archive.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle([
        'href' => 'styles/' . $stylePath,
        'rel' => 'stylesheet'
      ]);
    }
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->addRequiredStyles();

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $categoryName = $this->CMSCore->urlp->getPath(1) !== null 
      ? urldecode($this->CMSCore->urlp->getPath(1)) 
      : 'all';
    
    if (EntryCategory::existsByName($this->CMSCore, $categoryName) || $categoryName === 'all') {
      http_response_code(200);

      $pageIndex = $this->CMSCore->urlp->getParam('pageNumber');
      $yearParam = $this->CMSCore->urlp->getParam('year');
      $monthParam = $this->CMSCore->urlp->getParam('month');
      
      $entriesCountOnPage = 12;
      $paginationItemCurrent = $pageIndex ?? 0;
      $paginationItemCurrent = is_numeric($paginationItemCurrent) ? (int)$paginationItemCurrent : 0;

      $clientIsLogged = $this->CMSCore->client->isLogged(1);
      $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;
      
      if ($clientUser !== null) {
        $clientUser->initData(['metadata']);
        $isPublished = $clientUser->getID() !== 1 || $clientUser->getGroupID() !== 1;
      } else {
        $isPublished = true;
      }

      /** @var Entries $entries */
      $entries = new Entries($this->CMSCore);
      
      // Определяем период
      if ($yearParam && is_numeric($yearParam)) {
        $year = (int)$yearParam;
        
        if ($monthParam && is_numeric($monthParam) && $monthParam >= 1 && $monthParam <= 12) {
          $month = (int)$monthParam;
          $startTimestamp = mktime(0, 0, 0, $month, 1, $year);
          $endTimestamp = mktime(23, 59, 59, $month, date('t', $startTimestamp), $year);
          
          $archiveTitle = sprintf('%s %d', $this::getMonthName($month, $this->CMSCore->locale), $year);
          $archiveDescription = sprintf($localeData['PAGE_ARCHIVE_MONTH_DESCRIPTION'], $archiveTitle);
        } else {
          $startTimestamp = mktime(0, 0, 0, 1, 1, $year);
          $endTimestamp = mktime(23, 59, 59, 12, 31, $year);
          
          $archiveTitle = sprintf('%d', $year);
          $archiveDescription = sprintf($localeData['PAGE_ARCHIVE_YEAR_DESCRIPTION'], $year);
        }
        
        $params = ['limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]];
        
        if ($categoryName !== 'all') {
          $category = EntryCategory::getByName($this->CMSCore, $categoryName);
          if ($category) {
            $params['categoryID'] = $category->getID();
          }
        }
        
        $entriesObjects = $entries->getByDateRange($startTimestamp, $endTimestamp, $localeName, $params, $isPublished);
        $entriesCount = $entries->getCountByDateRange($startTimestamp, $endTimestamp, $params, $isPublished);
        
        $showEntries = true;
      } else {
        // Показываем список доступных годов
        $availableYears = $entries->getAvailableYears($isPublished);
        $entriesObjects = [];
        $entriesCount = 0;
        $showEntries = false;
        $archiveTitle = $localeData['PAGE_ARCHIVE_TITLE'];
        $archiveDescription = $localeData['PAGE_ARCHIVE_DESCRIPTION'];
      }

      // Хлебные крошки
      $this->page->breadcrumbs->add($localeData['PAGE_ARCHIVE_BREADCRUMBS_LABEL'], '/archive');
      
      if ($categoryName !== 'all') {
        $category = EntryCategory::getByName($this->CMSCore, $categoryName);
        if ($category) {
          $this->page->breadcrumbs->add($category->getTitle($localeName), '/archive/' . $categoryName);
        }
      }
      
      if (isset($year)) {
        $this->page->breadcrumbs->add((string)$year, '/archive' . ($categoryName !== 'all' ? '/' . $categoryName : '') . '?year=' . $year);
      }
      
      if (isset($month)) {
        $this->page->breadcrumbs->add($this::getMonthName($month, $this->CMSCore->locale), '');
      }
      
      $this->page->breadcrumbs->assembly();

      // Мета-теги
      $siteMetaTitle = $archiveTitle . ' | ' . $this->CMSCore->configurator->getSiteTitle();
      $this->CMSCore->configurator->setMetaTitle($siteMetaTitle);
      $this->CMSCore->configurator->setMetaDescription($archiveDescription);

      // Сборка списка записей
      $entriesArrayTemplates = [];
      
      if ($showEntries) {
        $entriesTemplateContent = ThemeCollector::getTemplateFileContent(
          $this->CMSCore->theme,
          'templates/page/archive/entriesList/item.tpl'
        );

        $templatesAssembled = [];
        
        foreach ($entriesObjects as $entryObject) {
          $entryObject->initData(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'metadata']);
          
          if ($entryObject->getPublishedUnixTimestamp() > time()) {
            continue;
          }

          $entryCategory = $entryObject->getCategory();
          
          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_TITLE')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_TITLE',
              htmlspecialchars($entryObject->getTitle($localeName), ENT_QUOTES, 'UTF-8')
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_DESCRIPTION')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_DESCRIPTION',
              htmlspecialchars($entryObject->getDescription($localeName), ENT_QUOTES, 'UTF-8')
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_URL')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_URL',
              $entryObject->getURL()
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_CREATED_DATE',
              date('d.m.Y', $entryObject->getCreatedUnixTimestamp())
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PREVIEW_URL')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_PREVIEW_URL',
              $entryObject->getPreviewURL() !== '' 
                ? $entryObject->getPreviewURL() 
                : Entry::getPreviewDefaultURL($this->CMSCore, 512)
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CATEGORY_TITLE')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_CATEGORY_TITLE',
              $entryCategory->getTitle($localeName)
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CATEGORY_URL')) {
            ThemeCollector::addTemplateVariable(
              $templatesAssembled,
              'ENTRY_CATEGORY_URL',
              $entryCategory->getURL()
            );
          }

          if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CATEGORY_TITLE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CATEGORY_TITLE',
            $entryCategory->getTitle($localeName)
          );
        }

        $entryCreatedUnixTimestamp = $entryObject->getCreatedUnixTimestamp();
        $entryPublishedUnixTimestamp = $entryObject->getPublishedUnixTimestamp();
        $entryUpdatedUnixTimestamp = $entryObject->getUpdatedUnixTimestamp();

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP',
            date('d.m.Y H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP',
            $entryPublishedUnixTimestamp > 0
              ? date('d.m.Y H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP',
            date('d.m.Y H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME',
            date('d.m.Y', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME',
            $entryPublishedUnixTimestamp > 0
              ? date('d.m.Y', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME',
            date('d.m.Y', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE',
            date('H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE',
            $entryPublishedUnixTimestamp > 0
              ? date('H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE',
            date('H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601',
            date('Y-m-dH:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601',
            $entryPublishedUnixTimestamp > 0
              ? date('Y-m-dH:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601',
            date('Y-m-dH:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            date('Y-m-d', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            $entryPublishedUnixTimestamp > 0
              ? date('Y-m-d', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            date('Y-m-d', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            date('H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            $entryPublishedUnixTimestamp > 0
              ? date('H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            date('H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

          $entriesArrayTemplates[] = ThemeCollector::assemblyFileContent(
            $this->CMSCore->theme,
            'templates/page/archive/entriesList/item.tpl',
            $templatesAssembled
          );

          $templatesAssembled = [];
        }

        unset($entriesObjects);
      }

      // Сборка списка доступных годов/месяцев
      $archiveNavHtml = $this->buildArchiveNavigation($entries, $categoryName, $isPublished, $localeName);
      unset($entries);

      // Пагинация
      $paginationHtml = '';
      if ($showEntries && $entriesCount > 0) {
        $baseUrl = '?year=' . $year;
        if (isset($month)) {
          $baseUrl .= '&month=' . $month;
        }
        
        $pagination = new Pagination($this->CMSCore, $entriesCount, $entriesCountOnPage, $paginationItemCurrent, $baseUrl, false);
        $pagination->assembly();
        $paginationHtml = $pagination->assembled;
      }

      $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
        'PAGE_NAME' => 'archive',
        'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/archive.tpl', [
          'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
          'ARCHIVE_TITLE' => $archiveTitle,
          'ARCHIVE_DESCRIPTION' => $archiveDescription,
          'ARCHIVE_NAVIGATION' => $archiveNavHtml,
          'ENTRIES_LIST' => $showEntries 
            ? (!empty($entriesArrayTemplates) 
              ? ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/archive/entriesList/list.tpl', [
                  'ENTRIES_LIST_ITEMS' => implode($entriesArrayTemplates)
                ]) 
              : sprintf('<div class="page__simple-note">%s</div>', $localeData['PAGE_ENTRIES_NOT_FOUND_LABEL']))
            : '',
          'ENTRIES_PAGINATION' => $paginationHtml
        ])
      ]);

      unset($entriesArrayTemplates);
    } else {
      http_response_code(404);

      $pageError = new PageError($this->CMSCore, $this->page, 404);
      $pageError->assembly();
      $this->assembled = $pageError->assembled;
    }
  }

  /**
   * Построение навигации по архиву
   *
   * @param Entries $entries
   * @param string $categoryName
   * @param bool $isPublished
   * @param string $localeName
   * @return string
   */
  private function buildArchiveNavigation(Entries $entries, string $categoryName, bool $isPublished, string $localeName) : string
  {
    $availableYears = $entries->getAvailableYears($isPublished);
    
    if (empty($availableYears)) {
      return '';
    }

    $document = new DOMDocument();
    
    $ulYears = $document->createElement('ul');
    $ulYears->setAttribute('class', 'archive-navigation');
    
    $yearParam = $this->CMSCore->urlp->getParam('year');
    
    foreach ($availableYears as $yearData) {
      $year = (int)$yearData['year'];
      $yearCount = (int)$yearData['count'];
      
      $yearUrl = '/archive' . ($categoryName !== 'all' ? '/' . $categoryName : '') . '?year=' . $year;
      
      $liYear = $document->createElement('li');
      $liYear->setAttribute('class', 'archive-navigation__year');
      
      $aYear = $document->createElement('a', (string)$year);
      $aYear->setAttribute('href', $yearUrl);
      $aYear->setAttribute('class', 'archive-navigation__year-link');
      
      $spanYearCount = $document->createElement('span', '(' . $yearCount . ')');
      $spanYearCount->setAttribute('class', 'archive-navigation__count');
      
      $liYear->appendChild($aYear);
      $liYear->appendChild($spanYearCount);
      
      // Показываем месяцы только если выбран год или параметр года отсутствует
      if (($yearParam !== null && (int)$yearParam === $year) || $yearParam === null) {
        $availableMonths = $entries->getAvailableMonths($year, $isPublished);
        
        if (!empty($availableMonths)) {
          $ulMonths = $document->createElement('ul');
          $ulMonths->setAttribute('class', 'archive-navigation__months');
          
          foreach ($availableMonths as $monthData) {
            $month = (int)$monthData['month'];
            $monthCount = (int)$monthData['count'];
            $monthUrl = $yearUrl . '&month=' . $month;
            $monthName = $this::getMonthName($month, $this->CMSCore->locale);
            
            $liMonth = $document->createElement('li');
            $liMonth->setAttribute('class', 'archive-navigation__month');
            
            $aMonth = $document->createElement('a', $monthName);
            $aMonth->setAttribute('href', $monthUrl);
            $aMonth->setAttribute('class', 'archive-navigation__month-link');
            
            $spanMonthCount = $document->createElement('span', '(' . $monthCount . ')');
            $spanMonthCount->setAttribute('class', 'archive-navigation__count');
            
            $liMonth->appendChild($aMonth);
            $liMonth->appendChild($spanMonthCount);
            
            $ulMonths->appendChild($liMonth);
          }
          
          $liYear->appendChild($ulMonths);
        }
      }
      
      $ulYears->appendChild($liYear);
    }
    
    $document->appendChild($ulYears);
    
    return $document->saveHTML();
  }

  /**
   * Получить название месяца по номеру
   *
   * @param int $month Номер месяца (1-12)
   * @param CMSLocale $locale Объект локали
   * @return string
   */
  public static function getMonthName(int $month, CMSLocale $locale) : string
  {
    $localeData = $locale->getData();
    
    $monthKeys = [
      1 => 'january',
      2 => 'february',
      3 => 'march',
      4 => 'april',
      5 => 'may',
      6 => 'june',
      7 => 'july',
      8 => 'august',
      9 => 'september',
      10 => 'october',
      11 => 'november',
      12 => 'december'
    ];
    
    $key = 'DEFAULT_TEXT_MONTH_' . strtoupper($monthKeys[$month]);
    
    return $localeData[$key] ?? (string)$month;
  }
}
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\User as User;

class PageEntries implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
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
    foreach (['page.css', 'page/entries.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }

  /**
   * Проверка возможности отображения записи для пользователя
   * 
   * Объект User должен передаваться с инициализированными данными:
   * - metadata
   * 
   * @param bool $isPublished
   * @param ?User $user
   * 
   * @return bool
   */
  public function entryIsVisible(bool $isPublished, ?User $user) : bool
  {
    if ($isPublished && $user !== null) {
      $userGroup = $user->getGroup();

      if ($userGroup !== null) {
        return $user->isSuperAdmin()
          || $userGroup->isSuperGroup()
          || $userGroup->hasPermissionEditorEntriesEdit();
      }
    }

    return false;
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

    $categoryName = $this->CMSCore->urlp->getPath(1) !== null ? urldecode($this->CMSCore->urlp->getPath(1)) : 'all';
    
    if (EntryCategory::existsByName($this->CMSCore, $categoryName) || $categoryName === 'all') {
      http_response_code(200);

      $entriesCountOnPage = 6;
      $pageIndex = $this->CMSCore->urlp->getParam('pageNumber');
      $paginationItemCurrent = $pageIndex ?? 0;
      $paginationItemCurrent = is_numeric($paginationItemCurrent) ? (int) $paginationItemCurrent : 0;

      $this->page->breadcrumbs->add($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');

      $clientIsLogged = $this->CMSCore->client->isLogged(1);
      
      $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;
      if ($clientUser !== null) {
        $clientUser->initData(['metadata']);
      }

      if ($clientUser !== null) {
        $isPublished = $clientUser->getID() !== 1 || $clientUser->getGroupID() !== 1;
      } else {
        $isPublished = true;
      }

      if ($categoryName !== 'all') {
        $category = EntryCategory::getByName($this->CMSCore, $categoryName);
        $category->initData(['name', 'texts']);
        $categoryID = $category->getID();

        $categoryTitle = strip_tags($category->getTitle($localeName));
        $categorySEOTitle = strip_tags($category->getSEOTitle($localeName));
        $categorySEOTitle = $categorySEOTitle !== ''
          ? $categorySEOTitle
          : $categoryTitle;
        
        $categoryDescription = strip_tags($category->getDescription($localeName));
        $categorySEODescription = strip_tags($category->getSEODescription($localeName));
        $categorySEODescription = $categorySEODescription !== ''
          ? $categorySEODescription
          : $categoryDescription;
        $categorySEODescription = str_replace('"', '&quot;', $categorySEODescription);
        $categoryKeywords = $category->getKeywords($localeName);
        $categoryKeywords = str_replace('"', '&quot;', $categoryKeywords);

        $this->CMSCore->configurator->setMetaTitle($categorySEOTitle . ' | ' . $localeData['DEFAULT_PAGE'] . ' ' . $pageIndex + 1);
        $this->CMSCore->configurator->setMetaDescription($categorySEODescription);
        $this->CMSCore->configurator->setMetaKeywords($categoryKeywords);

        $this->page->breadcrumbs->add($category->getTitle($this->CMSCore->configurator->getDatabaseEntryValue('base_locale')), '/entries/' . $category->getName());
        $this->page->breadcrumbs->assembly();

        /** @var Entries $entries Объект класса Entries */
        $entries = new Entries($this->CMSCore);
        $entriesObjects = $entries->getByCategoryID($categoryID, [
          'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
        ], $isPublished);
        
        $entriesCount = $entries->getCountByCategoryID($categoryID, $isPublished);
      } else {
        $this->page->breadcrumbs->assembly();

        $this->CMSCore->configurator->setMetaTitle($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] . ' | ' . $this->CMSCore->configurator->getSiteTitle());

        /** @var Entries $entries Объект класса Entries */
        $entries = new Entries($this->CMSCore);
        $entriesObjects = $entries->getAll([
          'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
        ], $isPublished);

        $entriesCount = $entries->getCountTotal($isPublished);
      }

      unset($entries);

      $entriesArrayTemplates = [];
      $entriesTemplateContent = ThemeCollector::getTemplateFileContent(
        $this->CMSCore->theme,
        'templates/page/entries/entriesList/item.tpl'
      );

      $templatesAssembled = [];
      foreach ($entriesObjects as $entryObject) {
        $entryObject->initData(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
        
        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_ID')) {
          $entryID = $entryObject->getID();
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_ID', $entryID);
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_TITLE')) {
          $entryTitle = $entryObject->getTitle($localeName);
          $entryTitle = strip_tags($entryTitle);
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_TITLE', $entryTitle);
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_DESCRIPTION')) {
          $entryDescription = $entryObject->getDescription($localeName);
          $entryDescription = strip_tags($entryDescription);
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_DESCRIPTION', $entryTitle);
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_DESCRIPTION')) {
          $entryContent = $entryObject->getContent($localeName);
          $entryContent = strip_tags($entryContent);
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_DESCRIPTION', $entryTitle);
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_URL')) {
          $entryURL = $entryObject->getURL();
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_URL', $entryURL);
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PREVIEW_URL')) {
          $entryPreviewURL = $entryObject->getPreviewURL();
          $entryPreviewURL = $entryPreviewURL !== ''
            ? $entryPreviewURL
            : Entry::getPreviewDefaultURL($this->CMSCore, 512);
          
          ThemeCollector::addTemplateVariable($templatesAssembled, 'ENTRY_PREVIEW_URL', $entryPreviewURL);
        }

        $createdDateTimestamp = date('d.m.Y H:i:s', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestamp = date('d.m.Y H:i:s', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestamp = date('d.m.Y H:i:s', $entryObject->getUpdatedUnixTimestamp());

        $createdDateTimestampWithoutTime = date('d.m.Y', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestampWithoutTime = date('d.m.Y', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestampWithoutTime = date('d.m.Y', $entryObject->getUpdatedUnixTimestamp());

        $createdDateTimestampWithoutDate = date('H:i:s', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestampWithoutDate = date('H:i:s', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestampWithoutDate = date('H:i:s', $entryObject->getUpdatedUnixTimestamp());

        $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->getUpdatedUnixTimestamp());

        $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->getUpdatedUnixTimestamp());

        $createdDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->getCreatedUnixTimestamp());
        $publishedDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->getPublishedUnixTimestamp());
        $updatedDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->getUpdatedUnixTimestamp());

        $category = $entryObject->getCategory();
        $categoryTitle = $category->getTitle($localeName);
        $categoryTitle = strip_tags($categoryTitle);
        $categoryDescription = $category->getDescription($localeName);
        $categoryDescription = strip_tags($categoryDescription);

        if (!empty($entryTitle) && !empty($entryDescription) && !empty($entryContent)) {
          array_push($entriesArrayTemplates, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entries/entriesList/item.tpl', [
            //'ENTRY_ID' => $entryObject->getID(),
            //'ENTRY_TITLE' => $entryTitle,
            //'ENTRY_DESCRIPTION' => $entryDescription,
            //'ENTRY_URL' => $entryObject->getURL(),
            //'ENTRY_PREVIEW_URL' => $entryObject->getPreviewURL() !== '' ? $entryObject->getPreviewURL() : Entry::getPreviewDefaultURL($this->CMSCore, 512),
            'ENTRY_CATEGORY_TITLE' => $categoryTitle,
            'ENTRY_CATEGORY_DESCRIPTION' => $categoryDescription,
            'ENTRY_CATEGORY_URL' => $category->getURL(),
            'ENTRY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $entryObject->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestamp : date('d.m.Y H:i:s', 0),
            'ENTRY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryObject->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutTime : date('d.m.Y', 0),
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutDate,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryObject->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutDate : date('H:i:s', 0),
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $updatedDateTimestampWithoutDate,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601' => $createdDateTimestampISO8601,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $publishedDateTimestampISO8601,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601' => $updatedDateTimestampISO8601,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $createdDateTimestampISO8601WithoutTime,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $publishedDateTimestampISO8601WithoutTime,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $updatedDateTimestampISO8601WithoutTime,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $createdDateTimestampISO8601WithoutDate,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $publishedDateTimestampISO8601WithoutDate,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $updatedDateTimestampISO8601WithoutDate
          ]));
        }

        unset($entry_data);
      }

      unset($entriesObjects);

      $pagination = new Pagination($this->CMSCore, $entriesCount, $entriesCountOnPage, $paginationItemCurrent);
      $pagination->assembly();

      $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
        'PAGE_NAME' => 'entries',
        'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entries.tpl', [
          'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
          'ENTRIES_CATEGORY_TITLE' => ($categoryName == 'all') ? $localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] : $category->getTitle($localeName),
          'ENTRIES_CATEGORY_DESCRIPTION' => ($categoryName == 'all') ? $localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_DESCRIPTION'] : $category->getDescription($localeName),
          'ENTRIES_LIST' => (!empty($entriesArrayTemplates)) ? ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entries/entriesList/list.tpl', [
            'ENTRIES_LIST_ITEMS' => implode($entriesArrayTemplates)
          ]) : sprintf('<div class="page__simple-note">%s</div>', $localeData['PAGE_ENTRIES_NOT_FOUND_LABEL']),
          'ENTRIES_PAGINATION' => (!empty($entriesArrayTemplates)) ? $pagination->assembled : ''
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
}
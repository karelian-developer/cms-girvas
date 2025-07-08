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
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;

class PageIndex implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $CMSCore = $this->CMSCore;
    $CMSTheme = $CMSCore->theme;
    $CMSLocale = $CMSCore->locale;

    $CMSTheme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
    $CMSTheme->addStyle(['href' => 'styles/page/index.css', 'rel' => 'stylesheet']);

    $localeData = $CMSLocale->getData();
    $localeName = $CMSLocale->getName();

    /** @var Entries $entries Объект класса Entries */
    $entries = new Entries($CMSCore);
    $entriesObjects = $entries->getAll(['limit' => [6, 0]]);
    unset($entries);

    $entriesArrayTemplates = [];
    foreach ($entriesObjects as $entryObject) {
      $entryObject->initData(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
      $categoryObject = $entryObject->getCategory(['texts', 'name', 'metadata']);
      
      /** @var string Заголовок записи */
      $entryTitle = $entryObject->getTitle($localeName);
      $entryTitle = strip_tags($entryTitle); 
      /** @var string Описание записи */
      $entryDescription = $entryObject->getDescription($localeName);
      $entryDescription = strip_tags($entryDescription); 

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

      $categoryTitle = $categoryObject->getTitle($localeName);
      $categoryTitle = strip_tags($categoryTitle); 

      $entryPreviewURL = $entryObject->getPreviewURL() !== '' ? $entryObject->getPreviewURL() : Entry::getPreviewDefaultURL($CMSCore, 512);
      $entryURL = $entryObject->getURL();
      $entryCategoryURL = $categoryObject->getURL();

      if ($entryObject->isPublished() && $categoryObject->isShowedOnIndexPage()) {
        array_push($entriesArrayTemplates, ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page/index/entriesList/item.tpl', [
          'ENTRY_ID' => $entryObject->getID(),
          'ENTRY_TITLE' => $entryTitle,
          'ENTRY_DESCRIPTION' => $entryDescription,
          'ENTRY_URL' => $entryURL,
          'ENTRY_PREVIEW_URL' => $entryPreviewURL,
          'ENTRY_CATEGORY_TITLE' => $categoryTitle,
          'ENTRY_CATEGORY_URL' => $entryCategoryURL,
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
    }

    unset($entriesObjects);

    $this->assembled = ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page.tpl', [
      'PAGE_NAME' => 'index',
      'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page/index.tpl', [
        'ENTRIES_LIST' => ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page/index/entriesList/list.tpl', [
          'ENTRIES_LIST_ITEMS' => implode($entriesArrayTemplates)
        ])
      ])
    ]);

    unset($entriesArrayTemplates);
  }
}
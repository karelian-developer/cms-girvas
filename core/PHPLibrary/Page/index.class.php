<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageIndex implements InterfacePage {
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
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/page/index.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      /** @var Entries $entries Объект класса Entries */
      $entries = new Entries($this->CMSCore);
      $entriesObjects = $entries->get_all(['limit' => [6, 0]]);
      unset($entries);

      $entriesArrayTemplates = [];
      foreach ($entriesObjects as $entryObject) {
        $entryObject->init_data(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
        $categoryObject = $entryObject->get_category(['texts', 'name', 'metadata']);
        
        /** @var string Заголовок записи */
        $entryTitle = $entryObject->get_title($localeName);
        $entryTitle = strip_tags($entryTitle); 
        /** @var string Описание записи */
        $entryDescription = $entryObject->get_description($localeName);
        $entryDescription = strip_tags($entryDescription); 

        $createdDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_updated_unix_timestamp());

        $createdDateTimestampWithoutTime = date('d.m.Y', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestampWithoutTime = date('d.m.Y', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestampWithoutTime = date('d.m.Y', $entryObject->get_updated_unix_timestamp());

        $createdDateTimestampWithoutDate = date('H:i:s', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestampWithoutDate = date('H:i:s', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestampWithoutDate = date('H:i:s', $entryObject->get_updated_unix_timestamp());

        $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryObject->get_updated_unix_timestamp());

        $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryObject->get_updated_unix_timestamp());

        $createdDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->get_created_unix_timestamp());
        $publishedDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->get_published_unix_timestamp());
        $updatedDateTimestampISO8601WithoutDate = date('H:i:s', $entryObject->get_updated_unix_timestamp());

        $categoryTitle = $categoryObject->get_title($localeName);
        $categoryTitle = strip_tags($categoryTitle); 

        if ($entryObject->is_published() && $categoryObject->is_showed_on_index_page()) {
          array_push($entriesArrayTemplates, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/index/entriesList/item.tpl', [
            'ENTRY_ID' => $entryObject->get_id(),
            'ENTRY_TITLE' => $entryTitle,
            'ENTRY_DESCRIPTION' => $entryDescription,
            'ENTRY_URL' => $entryObject->get_url(),
            'ENTRY_PREVIEW_URL' => $entryObject->get_preview_url() !== '' ? $entryObject->get_preview_url() : Entry::get_preview_default_url($this->CMSCore, 512),
            'ENTRY_CATEGORY_TITLE' => $categoryTitle,
            'ENTRY_CATEGORY_URL' => $categoryObject->get_url(),
            'ENTRY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $entryObject->get_published_unix_timestamp() > 0 ? $publishedDateTimestamp : '-',
            'ENTRY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryObject->get_published_unix_timestamp() > 0 ? $publishedDateTimestampWithoutTime : '-',
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutDate,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryObject->get_published_unix_timestamp() > 0 ? $publishedDateTimestampWithoutDate : '-',
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

      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
        'PAGE_NAME' => 'index',
        'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/index.tpl', [
          'ENTRIES_LIST' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/index/entriesList/list.tpl', [
            'ENTRIES_LIST_ITEMS' => implode($entriesArrayTemplates)
          ])
        ])
      ]);

      unset($entriesArrayTemplates);
    }

  }

}

?>
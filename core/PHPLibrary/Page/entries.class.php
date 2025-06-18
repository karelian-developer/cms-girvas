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
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\EntryCategory as EntryCategory;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageEntries implements InterfacePage {
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $categoryName = !is_null($this->CMSCore->urlp->get_path(1)) ? urldecode($this->CMSCore->urlp->get_path(1)) : 'all';
      
      if (EntryCategory::exists_by_name($this->CMSCore, $categoryName) || $categoryName === 'all') {
        http_response_code(200);

        $entriesCountOnPage = 6;
        $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;

        $this->page->breadcrumbs->add($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');

        $clientIsLogged = $this->CMSCore->client->is_logged(1);
        $clientUser = ($clientIsLogged) ? $this->CMSCore->client->get_user(1) : null;

        if ($clientUser != null) {
          $clientUser->init_data(['metadata']);

          $entrieIsNotPublishedIsVisible = ($clientUser->get_id() === 1 || $clientUser->get_group_id() === 1) ? true : false;
          $isPublished = ($entrieIsNotPublishedIsVisible) ? false : true;
        } else {
          $isPublished = true;
        }

        if ($categoryName !== 'all') {
          $category = EntryCategory::get_by_name($this->CMSCore, $categoryName);
          $category->init_data(['name', 'texts']);
          $categoryID = $category->get_id();

          $this->CMSCore->configurator->set_meta_title($category->get_title($localeName) . ' | ' . $this->CMSCore->configurator->get_site_title());

          $this->page->breadcrumbs->add($category->get_title($this->CMSCore->configurator->get_database_entry_value('base_locale')), '/entries/' . $category->get_name());
          $this->page->breadcrumbs->assembly();

          /** @var Entries $entries Объект класса Entries */
          $entries = new Entries($this->CMSCore);
          $entriesObjects = $entries->get_by_category_id($categoryID, [
            'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
          ], $isPublished);
          
          $entriesCount = $entries->get_count_by_category_id($categoryID, $isPublished);
        } else {
          $this->page->breadcrumbs->assembly();

          $this->CMSCore->configurator->set_meta_title($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] . ' | ' . $this->CMSCore->configurator->get_site_title());

          /** @var Entries $entries Объект класса Entries */
          $entries = new Entries($this->CMSCore);
          $entriesObjects = $entries->get_all([
            'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
          ], $isPublished);

          $entriesCount = $entries->get_count_total($isPublished);
        }

        unset($entries);

        $entriesArrayRemplates = [];
        foreach ($entriesObjects as $entryObject) {
          $entryObject->init_data(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

          /** @var string Заголовок записи */
          $entryTitle = $entryObject->get_title($localeName);
          $entryTitle = strip_tags($entryTitle); 
          /** @var string Описание записи */
          $entryDescription = $entryObject->get_description($localeName);
          $entryDescription = strip_tags($entryDescription);
          /** @var string Содержание записи */
          $entryContent = $entryObject->get_content($localeName);
          $entryContent = strip_tags($entryContent);

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

          $category = $entryObject->get_category();
          $categoryTitle = $category->get_title($localeName);
          $categoryTitle = strip_tags($categoryTitle);

          if (!empty($entryTitle) && !empty($entryDescription) && !empty($entryContent)) {
            array_push($entriesArrayRemplates, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries/entriesList/item.tpl', [
              'ENTRY_ID' => $entryObject->get_id(),
              'ENTRY_TITLE' => $entryTitle,
              'ENTRY_DESCRIPTION' => $entryDescription,
              'ENTRY_URL' => $entryObject->get_url(),
              'ENTRY_PREVIEW_URL' => $entryObject->get_preview_url() !== '' ? $entryObject->get_preview_url() : Entry::get_preview_default_url($this->CMSCore, 512),
              'ENTRY_CATEGORY_TITLE' => $categoryTitle,
              'ENTRY_CATEGORY_URL' => $category->get_url(),
              'ENTRY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entryObject->get_published_unix_timestamp() > 0) ? $publishedDateTimestamp : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entryObject->get_published_unix_timestamp() > 0) ? $publishedDateTimestampWithoutTime : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutDate,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entryObject->get_published_unix_timestamp() > 0) ? $publishedDateTimestampWithoutDate : '-',
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

        $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
          'PAGE_NAME' => 'entries',
          'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries.tpl', [
            'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
            'ENTRIES_CATEGORY_TITLE' => ($categoryName == 'all') ? $localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] : $category->get_title($localeName),
            'ENTRIES_LIST' => (!empty($entriesArrayRemplates)) ? TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries/entriesList/list.tpl', [
              'ENTRIES_LIST_ITEMS' => implode($entriesArrayRemplates)
            ]) : sprintf('<div class="page__simple-note">%s</div>', $localeData['PAGE_ENTRIES_NOT_FOUND_LABEL']),
            'ENTRIES_PAGINATION' => (!empty($entriesArrayRemplates)) ? $pagination->assembled : ''
          ])
        ]);
  
        unset($entriesArrayRemplates);
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();
        $this->assembled = $pageError->assembled;
      }
    }

  }

}

?>
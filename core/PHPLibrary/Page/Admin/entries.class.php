<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\EntryCategory as EntryCategory;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

/**
 * Страница со списком записей
 */
  class PageEntries implements InterfacePage {
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
        'isActive' => true
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
        'isActive' => false
      ]
    ];

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

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = (!is_null($this->CMSCore->urlp->get_param('pageNumber'))) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $entriesTableItemsAssembled = [];

      $entries = new Entries($this->CMSCore);
      $entriesLocale = $this->CMSCore->get_cms_locale('admin');
      $entriesLocaleName = $entriesLocale->get_name();
      
      $entriesObjects = $entries->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $entries->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($entries);

      $entryNumber = 1;
      foreach ($entriesObjects as $entryObject) {
        $entryObject->init_data(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'categoryID']);

        $entryCategoryID = $entryObject->get_category_id();
        $entryCategory = new EntryCategory($this->CMSCore, $entryCategoryID);
        $entryCategory->init_data(['texts']);

        $entryCreatedDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_created_unix_timestamp());
        $entryPublishedDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_published_unix_timestamp());
        $entryUpdatedDateTimestamp = date('d.m.Y H:i:s', $entryObject->get_updated_unix_timestamp());

        $entryTitle = $entryObject->get_title($entriesLocaleName);
        $entryDescription = $entryObject->get_description($entriesLocaleName);
        $entryCategoryTitle = $entryCategory->get_title($localeName);

        $entryTitle = strip_tags($entryTitle);
        $entryDescription = strip_tags($entryDescription);
        $entryCategoryTitle = strip_tags($entryCategoryTitle);

        array_push($entriesTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries/tableItem.tpl', [
          'ENTRY_ID' => $entryObject->get_id(),
          'ENTRY_NAME' => $entryObject->get_name(),
          'ENTRY_INDEX' => $entryNumber,
          'ENTRY_TITLE' => !empty($entryTitle) ? $entryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entriesLocale->get_name()),
          'ENTRY_DESCRIPTION' => !empty($entryDescription) ? $entryDescription : sprintf('[ DESCRIPTION NOT FOUND IN LOCALE %s ]', $entriesLocale->get_name()),
          'ENTRY_CATEGORY_TITLE' => !empty($entryCategoryTitle) ? $entryCategoryTitle : sprintf('[ CATEGORY TITLE NOT FOUND IN LOCALE %s ]', $localeName),
          'ENTRY_PUBLISHED_STATUS' => $entryObject->is_published() ? 'published' : 'not-published',
          'ENTRY_URL' => $entryObject->get_url(),
          'ENTRY_CREATED_DATE_TIMESTAMP' => $entryCreatedDateTimestamp,
          'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $entryObject->get_published_unix_timestamp() > 0 ? $entryPublishedDateTimestamp : '-',
          'ENTRY_UPDATED_DATE_TIMESTAMP' => $entryUpdatedDateTimestamp,
        ]));

        $entryNumber++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries.tpl', [
        'PAGE_ENTRIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries',
        'ADMIN_PANEL_ENTRIES_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entries/table.tpl', [
          'ADMIN_PANEL_ENTRIES_TABLE_ITEMS' => implode($entriesTableItemsAssembled)
        ])
      ]);
    }

  }

}

?>
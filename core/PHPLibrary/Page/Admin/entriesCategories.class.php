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
  use \core\PHPLibrary\EntriesCategories as EntriesCategories;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageEntriesCategories implements InterfacePage {
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
        'isActive' => false
      ],
      'categories' => [
        'name' => 'categories',
        'iconName' => 'entriesCategories',
        'link' => '/entriesCategories',
        'permanent' => false,
        'isActive' => true
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

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entriesCategories.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = (!is_null($this->CMSCore->urlp->get_param('pageNumber'))) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $entriesCategoriesTableItemsAssembled = [];
      $entriesCategories = new EntriesCategories($this->CMSCore);

      $entriesCategoriesLocale = $this->CMSCore->get_cms_locale('admin');
      $entriesCategoriesLocaleName = $entriesLocale->get_name();

      $entriesCategoriesObjects = $entriesCategories->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $entriesCategories->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($entriesCategories);

      foreach ($entriesCategoriesObjects as $index => $object) {
        $object->init_data(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'parentID']);

        $createdDateTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
        $updatedDateTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

        $entriesCategoryTitle = $object->get_title($entriesCategoriesLocaleName);
        $entriesCategoryTitle = strip_tags($entriesCategoryTitle);

        array_push($entriesCategoriesTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesCategories/tableItem.tpl', [
          'ENTRIES_CATEGORY_ID' => $object->get_id(),
          'ENTRIES_CATEGORY_INDEX' => $index + 1,
          'ENTRIES_CATEGORY_TITLE' => (!empty($entriesCategoryTitle)) ? $entriesCategoryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entriesCategoriesLocaleName),
          'ENTRIES_CATEGORY_URL' => $object->get_url(),
          'ENTRIES_CATEGORY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
          'ENTRIES_CATEGORY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesCategories.tpl', [
        'PAGE_ENTRIES_CATEGORIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries-categories',
        'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesCategories/table.tpl', [
          'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE_ITEMS' => implode($entriesCategoriesTableItemsAssembled)
        ])
      ]);
    }

  }

}

?>
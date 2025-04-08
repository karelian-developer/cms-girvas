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

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
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

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/entriesCategories.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $entries_categories_table_items_assembled = [];
      $entries_categories = new EntriesCategories($this->system_core);
      $entries_categories_locale_default = $this->system_core->get_cms_locale('admin');
      $entries_categories_array_objects = $entries_categories->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $entries_categories->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($entries_categories);

      foreach ($entries_categories_array_objects as $entries_category_index => $entries_category_object) {
        $entries_category_object->init_data(['id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'parent_id']);

        $created_date_timestamp = date('d.m.Y H:i:s', $entries_category_object->get_created_unix_timestamp());
        $updated_date_timestamp = date('d.m.Y H:i:s', $entries_category_object->get_updated_unix_timestamp());

        $entries_category_title = $entries_category_object->get_title($entries_categories_locale_default->get_name());
        $entries_category_title = strip_tags($entries_category_title);

        array_push($entries_categories_table_items_assembled, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesCategories/tableItem.tpl', [
          'ENTRIES_CATEGORY_ID' => $entries_category_object->get_id(),
          'ENTRIES_CATEGORY_INDEX' => $entries_category_index + 1,
          'ENTRIES_CATEGORY_TITLE' => (!empty($entries_category_title)) ? $entries_category_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entries_categories_locale_default->get_name()),
          'ENTRIES_CATEGORY_URL' => $entries_category_object->get_url(),
          'ENTRIES_CATEGORY_CREATED_DATE_TIMESTAMP' => $created_date_timestamp,
          'ENTRIES_CATEGORY_UPDATED_DATE_TIMESTAMP' => $updated_date_timestamp
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesCategories.tpl', [
        'PAGE_ENTRIES_CATEGORIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries-categories',
        'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesCategories/table.tpl', [
          'ADMIN_PANEL_ENTRIES_CATEGORIES_TABLE_ITEMS' => implode($entries_categories_table_items_assembled)
        ])
      ]);
    }

  }

}

?>
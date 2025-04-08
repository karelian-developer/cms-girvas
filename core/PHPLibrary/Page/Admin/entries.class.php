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
     * @param SystemCore $system_core
     * @param Page $page
     */
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

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->system_core->template->add_style(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();
      $cms_locale_name = $this->system_core->locale->get_name();

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $entries_table_items_assembled_array = [];

      $entries = new Entries($this->system_core);
      $entries_locale_default = $this->system_core->get_cms_locale('admin');
      
      $entries_array_objects = $entries->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $entries->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($entries);

      $entry_number = 1;
      foreach ($entries_array_objects as $entry_object) {
        $entry_object->init_data(['id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata', 'category_id']);

        $entry_category_id = $entry_object->get_category_id();
        $entry_category_object = new EntryCategory($this->system_core, $entry_category_id);
        $entry_category_object->init_data(['texts']);

        $entry_created_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_updated_unix_timestamp());

        $entry_title = $entry_object->get_title($entries_locale_default->get_name());
        $entry_description = $entry_object->get_description($entries_locale_default->get_name());
        $entry_category_title = $entry_category_object->get_title($cms_locale_name);

        $entry_title = strip_tags($entry_title);
        $entry_description = strip_tags($entry_description);
        $entry_category_title = strip_tags($entry_category_title);

        array_push($entries_table_items_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries/tableItem.tpl', [
          'ENTRY_ID' => $entry_object->get_id(),
          'ENTRY_NAME' => $entry_object->get_name(),
          'ENTRY_INDEX' => $entry_number,
          'ENTRY_TITLE' => (!empty($entry_title)) ? $entry_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $entries_locale_default->get_name()),
          'ENTRY_DESCRIPTION' => (!empty($entry_description)) ? $entry_description : sprintf('[ DESCRIPTION NOT FOUND IN LOCALE %s ]', $entries_locale_default->get_name()),
          'ENTRY_CATEGORY_TITLE' => (!empty($entry_category_title)) ? $entry_category_title : sprintf('[ CATEGORY TITLE NOT FOUND IN LOCALE %s ]', $cms_locale_name),
          'ENTRY_PUBLISHED_STATUS' => ($entry_object->is_published()) ? 'published' : 'not-published',
          'ENTRY_URL' => $entry_object->get_url(),
          'ENTRY_CREATED_DATE_TIMESTAMP' => $entry_created_date_timestamp,
          'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entry_object->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp : '-',
          'ENTRY_UPDATED_DATE_TIMESTAMP' => $entry_updated_date_timestamp,
        ]));

        $entry_number++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries.tpl', [
        'PAGE_ENTRIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries',
        'ADMIN_PANEL_ENTRIES_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries/table.tpl', [
          'ADMIN_PANEL_ENTRIES_TABLE_ITEMS' => implode($entries_table_items_assembled_array)
        ])
      ]);
    }

  }

}

?>
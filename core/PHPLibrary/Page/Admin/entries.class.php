<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
  use \core\PHPLibrary\Pagination as Pagination;

/**
 * Страница со списком записей
 */
  class PageEntries implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

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
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->system_core->template->add_style(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);
      
      $cms_locale_setted_name = $this->system_core->configurator->get_database_entry_value('base_admin_locale');
      $url_locale_setted_name = $this->system_core->urlp->get_param('locale');
      $cookie_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;
      
      $cms_locale_name = (!is_null($url_locale_setted_name)) ? $url_locale_setted_name : $cookie_locale_setted_name;
      $cms_locale_name = (!is_null($cms_locale_name)) ? $cms_locale_name : $cms_locale_setted_name;
      $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_name, 'admin');
      if (!$cms_locale->exists_file_data_json()) {
        $cms_locale = new SystemCoreLocale($this->system_core, $cms_locale_setted_name, 'admin');
        $cms_locale_name = $cms_locale_setted_name;
      }

      $this->system_core->locale = $cms_locale;
      $locale_data = $this->system_core->locale->get_data();

      /** @var array Преобразованные элементы навигации */
      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_ENTRIES_NAVIGATION_INDEX_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => $locale_data['PAGE_ENTRIES_NAVIGATION_ENTRIES_LABEL'],
        'NAVIGATION_ITEM_URL' => '/admin/entries',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => 'navigation-item__link_is-active'
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => $locale_data['PAGE_ENTRIES_NAVIGATION_CATEGORIES_LABEL'],
        'NAVIGATION_ITEM_URL' => '/admin/entriesCategories',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => $locale_data['PAGE_ENTRIES_NAVIGATION_COMMENTS_LABEL'],
        'NAVIGATION_ITEM_URL' => '/admin/entriesComments',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));

      if (!empty($navigations_items_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $entries_table_items_assembled_array = [];

      $entries = new Entries($this->system_core);
      $entries_locale_default = $this->system_core->get_cms_locale('base');
      
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

        $entry_title = htmlspecialchars($entry_title);
        $entry_description = htmlspecialchars($entry_description);
        $entry_category_title = htmlspecialchars($entry_category_title);

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
        'PAGE_NAVIGATION' => $page_navigation_transformed,
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
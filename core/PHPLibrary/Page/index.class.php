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
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  Page $page
     * @return void
     */
    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_style(['href' => 'styles/page/index.css', 'rel' => 'stylesheet']);

      $cms_base_locale_setted_name = $this->system_core->configurator->get_database_entry_value('base_locale');
      $url_base_locale_setted_name = $this->system_core->urlp->get_param('locale');
      $cookie_base_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;
      
      $cms_base_locale_name = (!is_null($url_base_locale_setted_name)) ? $url_base_locale_setted_name : $cookie_base_locale_setted_name;
      $cms_base_locale_name = (!is_null($cms_base_locale_name)) ? $cms_base_locale_name : $cms_base_locale_setted_name;
      $cms_base_locale = new SystemCoreLocale($this->system_core, $cms_base_locale_name);
      if (!$cms_base_locale->exists_file_data_json()) {
        $cms_base_locale = new SystemCoreLocale($this->system_core, $cms_base_locale_setted_name);
        $cms_base_locale_name = $cms_base_locale_setted_name;
      }

      $this->system_core->locale = $cms_base_locale;
      $locale_data = $this->system_core->locale->get_data();

      /** @var Entries $entries Объект класса Entries */
      $entries = new Entries($this->system_core);
      $entries_array_objects = $entries->get_all(['limit' => [6, 0]]);
      unset($entries);

      $entries_array_templates = [];
      foreach ($entries_array_objects as $entry_object) {
        $entry_object->init_data(['id', 'category_id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);
        $entry_category_object = $entry_object->get_category(['texts', 'name', 'metadata']);
        
        /** @var string Заголовок записи */
        $entry_title = (!empty($entry_object->get_title($cms_base_locale_name))) ? $entry_object->get_title($cms_base_locale_name) : $entry_object->get_title($cms_base_locale_setted_name);
        $entry_title = strip_tags($entry_title); 
        /** @var string Описание записи */
        $entry_description = (!empty($entry_object->get_description($cms_base_locale_name))) ? $entry_object->get_description($cms_base_locale_name) : $entry_object->get_description($cms_base_locale_setted_name);
        $entry_description = strip_tags($entry_description); 

        $entry_created_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp = date('d.m.Y H:i:s', $entry_object->get_updated_unix_timestamp());

        $entry_created_date_timestamp_without_time = date('d.m.Y', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp_without_time = date('d.m.Y', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp_without_time = date('d.m.Y', $entry_object->get_updated_unix_timestamp());

        $entry_created_date_timestamp_without_date = date('H:i:s', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp_without_date = date('H:i:s', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp_without_date = date('H:i:s', $entry_object->get_updated_unix_timestamp());

        $entry_created_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry_object->get_updated_unix_timestamp());

        $entry_created_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry_object->get_updated_unix_timestamp());

        $entry_created_date_timestamp_iso_8601_without_date = date('H:i:s', $entry_object->get_created_unix_timestamp());
        $entry_published_date_timestamp_iso_8601_without_date = date('H:i:s', $entry_object->get_published_unix_timestamp());
        $entry_updated_date_timestamp_iso_8601_without_date = date('H:i:s', $entry_object->get_updated_unix_timestamp());

        $entry_category_title = $entry_category_object->get_title($cms_base_locale_name);
        $entry_category_title = strip_tags($entry_category_title); 

        if ($entry_object->is_published() && $entry_category_object->is_showed_on_index_page()) {
          array_push($entries_array_templates, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/entriesList/item.tpl', [
            'ENTRY_ID' => $entry_object->get_id(),
            'ENTRY_TITLE' => $entry_title,
            'ENTRY_DESCRIPTION' => $entry_description,
            'ENTRY_URL' => $entry_object->get_url(),
            'ENTRY_PREVIEW_URL' => ($entry_object->get_preview_url() != '') ? $entry_object->get_preview_url() : Entry::get_preview_default_url($this->system_core, 512),
            'ENTRY_CATEGORY_TITLE' => $entry_category_title,
            'ENTRY_CATEGORY_URL' => $entry_category_object->get_url(),
            'ENTRY_CREATED_DATE_TIMESTAMP' => $entry_created_date_timestamp,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entry_object->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp : '-',
            'ENTRY_UPDATED_DATE_TIMESTAMP' => $entry_updated_date_timestamp,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_created_date_timestamp_without_time,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entry_object->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_time : '-',
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_updated_date_timestamp_without_time,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry_created_date_timestamp_without_date,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entry_object->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_date : '-',
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry_updated_date_timestamp_without_date,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601' => $entry_created_date_timestamp_iso_8601,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $entry_published_date_timestamp_iso_8601,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601' => $entry_updated_date_timestamp_iso_8601,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_created_date_timestamp_iso_8601_without_time,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_published_date_timestamp_iso_8601_without_time,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_updated_date_timestamp_iso_8601_without_time,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_created_date_timestamp_iso_8601_without_date,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_published_date_timestamp_iso_8601_without_date,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_updated_date_timestamp_iso_8601_without_date
          ]));
        }

        unset($entry_data);
      }

      unset($entries_array_objects);

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
        'PAGE_NAME' => 'index',
        'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index.tpl', [
          'ENTRIES_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/entriesList/list.tpl', [
            'ENTRIES_LIST_ITEMS' => implode($entries_array_templates)
          ])
        ])
      ]);

      unset($entries_array_templates);
    }

  }

}

?>
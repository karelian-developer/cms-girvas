<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
      $this->system_core->template->add_style(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);

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

      $entries_category_name = (!is_null($this->system_core->urlp->get_path(1))) ? urldecode($this->system_core->urlp->get_path(1)) : 'all';
      
      if (EntryCategory::exists_by_name($this->system_core, $entries_category_name) || $entries_category_name == 'all') {
        http_response_code(200);

        $entries_count_on_page = 6;
        $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;

        $this->page->breadcrumbs->add($locale_data['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');

        $client_is_logged = $this->system_core->client->is_logged(1);
        $client_user = ($client_is_logged) ? $this->system_core->client->get_user(1) : null;

        if ($client_user != null) {
          $client_user->init_data(['metadata']);

          $entries_not_published_is_visible = ($client_user->get_id() == 1 || $client_user->get_group_id() == 1) ? true : false;
          $only_published = ($entries_not_published_is_visible) ? false : true;
        } else {
          $only_published = true;
        }

        if ($entries_category_name != 'all') {
          $entries_category = EntryCategory::get_by_name($this->system_core, $entries_category_name);
          $entries_category->init_data(['name', 'texts']);
          $entries_category_id = $entries_category->get_id();

          $this->system_core->configurator->set_meta_title(sprintf('%s | %s', $entries_category->get_title($cms_base_locale_name), $this->system_core->configurator->get_site_title()));

          $this->page->breadcrumbs->add($entries_category->get_title($this->system_core->configurator->get_database_entry_value('base_locale')), sprintf('/entries/%s', $entries_category->get_name()));
          $this->page->breadcrumbs->assembly();

          /** @var Entries $entries Объект класса Entries */
          $entries = new Entries($this->system_core);
          $entries_array_objects = $entries->get_by_category_id($entries_category_id, [
            'limit' => [$entries_count_on_page, $pagination_item_current * $entries_count_on_page]
          ], $only_published);
          
          $entries_count = $entries->get_count_by_category_id($entries_category_id, $only_published);
        } else {
          $this->page->breadcrumbs->assembly();

          $this->system_core->configurator->set_meta_title(sprintf('%s | %s', $locale_data['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'], $this->system_core->configurator->get_site_title()));

          /** @var Entries $entries Объект класса Entries */
          $entries = new Entries($this->system_core);
          $entries_array_objects = $entries->get_all([
            'limit' => [$entries_count_on_page, $pagination_item_current * $entries_count_on_page]
          ], $only_published);

          $entries_count = $entries->get_count_total($only_published);
        }

        unset($entries);

        $entries_array_templates = [];
        foreach ($entries_array_objects as $entry_object) {
          $entry_object->init_data(['id', 'category_id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);

          /** @var string Заголовок записи */
          $entry_title = (!empty($entry_object->get_title($cms_base_locale_name))) ? $entry_object->get_title($cms_base_locale_name) : $entry_object->get_title($cms_base_locale_setted_name);
          $entry_title = strip_tags($entry_title); 
          /** @var string Описание записи */
          $entry_description = (!empty($entry_object->get_description($cms_base_locale_name))) ? $entry_object->get_description($cms_base_locale_name) : $entry_object->get_description($cms_base_locale_setted_name);
          $entry_description = strip_tags($entry_description);
          /** @var string Содержание записи */
          $entry_content = (!empty($entry_object->get_content($cms_base_locale_name))) ? $entry_object->get_content($cms_base_locale_name) : $entry_object->get_content($cms_base_locale_setted_name);
          $entry_content = strip_tags($entry_content);

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

          $entry_category = $entry_object->get_category();
          $entry_category_title = $entry_category->get_title($cms_base_locale_name);
          $entry_category_title = strip_tags($entry_category_title);

          if (!empty($entry_title) && !empty($entry_description) && !empty($entry_content)) {
            array_push($entries_array_templates, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries/entriesList/item.tpl', [
              'ENTRY_ID' => $entry_object->get_id(),
              'ENTRY_TITLE' => $entry_title,
              'ENTRY_DESCRIPTION' => $entry_description,
              'ENTRY_URL' => $entry_object->get_url(),
              'ENTRY_PREVIEW_URL' => ($entry_object->get_preview_url() != '') ? $entry_object->get_preview_url() : Entry::get_preview_default_url($this->system_core, 512),
              'ENTRY_CATEGORY_TITLE' => $entry_category_title,
              'ENTRY_CATEGORY_URL' => $entry_category->get_url(),
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

        $pagination = new Pagination($this->system_core, $entries_count, $entries_count_on_page, $pagination_item_current);
        $pagination->assembly();

        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
          'PAGE_NAME' => 'entries',
          'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries.tpl', [
            'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
            'ENTRIES_CATEGORY_TITLE' => ($entries_category_name == 'all') ? $locale_data['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] : $entries_category->get_title($cms_base_locale_name),
            'ENTRIES_LIST' => (!empty($entries_array_templates)) ? TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entries/entriesList/list.tpl', [
              'ENTRIES_LIST_ITEMS' => implode($entries_array_templates)
            ]) : sprintf('<div class="page__simple-note">%s</div>', $locale_data['PAGE_ENTRIES_NOT_FOUND_LABEL']),
            'ENTRIES_PAGINATION' => (!empty($entries_array_templates)) ? $pagination->assembled : ''
          ])
        ]);
  
        unset($entries_array_templates);
      } else {
        http_response_code(404);

        $page_error = new PageError($this->system_core, $this->page, 404);
        $page_error->assembly();
        $this->assembled = $page_error->assembled;
      }
    }

  }

}

?>
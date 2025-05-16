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
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\PageStatic as PageStatic;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PagePage implements InterfacePage {
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
      $this->system_core->template->add_style(['href' => 'styles/page/static.css', 'rel' => 'stylesheet']);

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

      if (!is_null($this->system_core->urlp->get_path(1))) {
        $page_static_name = urldecode($this->system_core->urlp->get_path(1));

        if (PageStatic::exists_by_name($this->system_core, $page_static_name)) {
          $page_static = PageStatic::get_by_name($this->system_core, $page_static_name);
          $page_static->init_data(['id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);

          if (!is_null($url_base_locale_setted_name)) {
            if ($url_base_locale_setted_name == $cms_base_locale_setted_name) {
              $this->system_core->template->add_link_canonical(sprintf('/page/%s', $page_static->get_name()));
            }
          }

          $page_is_visible = false;

          $client_is_logged = $this->system_core->client->is_logged(1);
          $client_user = ($client_is_logged) ? $this->system_core->client->get_user(1) : null;

          $page_is_visible = ($page_static->is_published()) ? true : false;
          if (!$page_is_visible) {
            if ($client_user != null) {
              $page_is_visible = ($client_user->get_id() == 1 || $client_user->get_group_id() == 1) ? true : false;
            }
          }

          if ($page_is_visible) {
            http_response_code(200);

            $this->page->breadcrumbs->add($locale_data['PAGE_STATIC_PAGE_BREADCRUMPS_INDEX_LABEL'], '/');
            $this->page->breadcrumbs->add($page_static->get_title($this->system_core->configurator->get_database_entry_value('base_locale')), $page_static->get_name());
            $this->page->breadcrumbs->assembly();

            $this->system_core->configurator->set_meta_title($page_static->get_title($cms_base_locale_name));
            $this->system_core->configurator->set_meta_description(str_replace('"', '&quot;', $page_static->get_description($cms_base_locale_name)));
            $this->system_core->configurator->set_meta_keywrords(str_replace('"', '&quot;', $page_static->get_keywords($cms_base_locale_name)));

            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();
            //$parsedown->setSafeMode(true);
            //$parsedown->setMarkupEscaped(true);

            /**
             * @var string Заголовок статической страницы
             */
            $page_static_title = (!empty($page_static->get_title($cms_base_locale_name))) ? $page_static->get_title($cms_base_locale_name) : $page_static->get_title($cms_base_locale_setted_name);
            $page_static_title = strip_tags($page_static_title);
            /**
             * @var string Содержание статической страницы
             */
            $page_static_content = (!empty($page_static->get_content($cms_base_locale_name))) ? $page_static->get_content($cms_base_locale_name) : $page_static->get_content($cms_base_locale_setted_name);

            $page_static_created_date_timestamp = date('d.m.Y H:i:s', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp = date('d.m.Y H:i:s', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp = date('d.m.Y H:i:s', $page_static->get_updated_unix_timestamp());

            $page_static_created_date_timestamp_without_time = date('d.m.Y', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp_without_time = date('d.m.Y', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp_without_time = date('d.m.Y', $page_static->get_updated_unix_timestamp());
    
            $page_static_created_date_timestamp_without_date = date('H:i:s', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp_without_date = date('H:i:s', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp_without_date = date('H:i:s', $page_static->get_updated_unix_timestamp());

            $page_static_created_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $page_static->get_updated_unix_timestamp());

            $page_static_created_date_timestamp_iso_8601_without_time = date('Y-m-d', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp_iso_8601_without_time = date('Y-m-d', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp_iso_8601_without_time = date('Y-m-d', $page_static->get_updated_unix_timestamp());
    
            $page_static_created_date_timestamp_iso_8601_without_date = date('H:i:s', $page_static->get_created_unix_timestamp());
            $page_static_published_date_timestamp_iso_8601_without_date = date('H:i:s', $page_static->get_published_unix_timestamp());
            $page_static_updated_date_timestamp_iso_8601_without_date = date('H:i:s', $page_static->get_updated_unix_timestamp());

            $page_content_tags = [
              'PAGE_ID' => $page_static->get_id(),
              'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
              'PAGE_TITLE' => $page_static_title,
              'PAGE_CONTENT' => $parsedown->text($page_static_content),
              'PAGE_PREVIEW_URL' => ($page_static->get_preview_url() != '') ? $page_static->get_preview_url() : PageStatic::get_preview_default_url($this->system_core, 1024),
              'PAGE_CREATED_DATE_TIMESTAMP' => $page_static_created_date_timestamp,
              'PAGE_PUBLISHED_DATE_TIMESTAMP' => ($page_static->get_published_unix_timestamp() > 0) ? $page_static_published_date_timestamp : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP' => $page_static_updated_date_timestamp,
              'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $page_static_created_date_timestamp_without_time,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($page_static->get_published_unix_timestamp() > 0) ? $page_static_published_date_timestamp_without_time : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $page_static_updated_date_timestamp_without_time,
              'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $page_static_created_date_timestamp_without_date,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($page_static->get_published_unix_timestamp() > 0) ? $page_static_published_date_timestamp_without_date : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $page_static_updated_date_timestamp_without_date,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601' => $page_static_created_date_timestamp_iso_8601,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $page_static_published_date_timestamp_iso_8601,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601' => $page_static_updated_date_timestamp_iso_8601,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $page_static_created_date_timestamp_iso_8601_without_time,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $page_static_published_date_timestamp_iso_8601_without_time,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $page_static_updated_date_timestamp_iso_8601_without_time,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $page_static_created_date_timestamp_iso_8601_without_date,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $page_static_published_date_timestamp_iso_8601_without_date,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $page_static_updated_date_timestamp_iso_8601_without_date
            ];

            $additional_fields_data = $page_static->get_additional_fields_data();
            if (count($additional_fields_data) > 0) {
              foreach ($additional_fields_data as $field_data_name => $field_data) {
                $tag_name = sprintf('PAGE_ADDITIONAL_DATA_%s', strtoupper($field_data_name));
                $page_content_tags[$tag_name] = $field_data;
              }
            }

            /** @var string Путь до персонального шаблона */
            $personal_template_path = ($page_static->exists_personal_template_file()) ? sprintf('templates/%', $page_static->get_personal_template_path()) : 'templates/page/static.tpl';

            $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
              'PAGE_NAME' => 'static',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, $personal_template_path, $page_content_tags)
            ]);
          } else {
            http_response_code(404);
  
            $page_error = new PageError($this->system_core, $this->page, 404);
            $page_error->assembly();
            $this->assembled = $page_error->assembled;
          }
        } else {
          http_response_code(404);

          $page_error = new PageError($this->system_core, $this->page, 404);
          $page_error->assembly();
          $this->assembled = $page_error->assembled;
        }
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
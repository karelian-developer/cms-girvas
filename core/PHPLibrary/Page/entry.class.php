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
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageEntry implements InterfacePage {
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
      $this->system_core->template->add_style(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);

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
        $entry_name = urldecode($this->system_core->urlp->get_path(1));

        if (Entry::exists_by_name($this->system_core, $entry_name)) {
          $entry = Entry::get_by_name($this->system_core, $entry_name);
          $entry->init_data(['id', 'category_id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);

          if (!is_null($url_base_locale_setted_name)) {
            if ($url_base_locale_setted_name == $cms_base_locale_setted_name) {
              $this->system_core->template->add_link_canonical(sprintf('/entry/%s', $entry->get_name()));
            }
          }

          $entry_is_visible = false;

          $client_is_logged = $this->system_core->client->is_logged(1);
          $client_user = ($client_is_logged) ? $this->system_core->client->get_user(1) : null;

          $entry_is_visible = ($entry->is_published()) ? true : false;
          if (!$entry_is_visible) {
            if ($client_user != null) {
              $entry_is_visible = ($client_user->get_id() == 1 || $client_user->get_group_id() == 1) ? true : false;
            }
          }

          if ($entry_is_visible) {
            http_response_code(200);

            $entry_category = $entry->get_category();
            $entry_category_title = $entry_category->get_title($cms_base_locale_name);

            $this->system_core->configurator->set_meta_title($entry->get_title($cms_base_locale_name));
            $this->system_core->configurator->set_meta_description($entry->get_description($cms_base_locale_name));
            $this->system_core->configurator->set_meta_keywrords($entry->get_keywords($cms_base_locale_name));

            $this->page->breadcrumbs->add($locale_data['PAGE_ENTRY_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');
            $this->page->breadcrumbs->add($entry_category_title, sprintf('/entries/%s', $entry_category->get_name()));
            $this->page->breadcrumbs->add($entry->get_title($cms_base_locale_name), sprintf('/entry/%s', $entry->get_name()));
            $this->page->breadcrumbs->assembly();

            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            $parsedown->setMarkupEscaped(true);

            //sortColumn=created_unix_timestamp&sortType=desc
            $entry_comments_array = $entry->get_comments([
              'limit' => [2, 0],
              'order_by' => [
                'column' => 'created_unix_timestamp',
                'sort' => 'desc'
              ],
              'parent_id' => 0
            ]);
            foreach ($entry_comments_array as $entry_comment) {
              $entry_comment->init_data(['created_unix_timestamp']);
            }

            usort($entry_comments_array, function ($a, $b) {
              $a_cut = $a->get_created_unix_timestamp();
              $b_cut = $b->get_created_unix_timestamp();

              if ($a_cut != $b_cut) {
                return ($a_cut > $b_cut) ? -1 : 1;
              }

              return 0;
            });

            $entry_comments_transformed_array = [];
            $entry_comment_index = 1;
            foreach ($entry_comments_array as $entry_comment) {
              $entry_comment->init_data(['entry_id', 'author_id', 'content', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);
              $entry_comment_author = $entry_comment->get_author();
              $entry_comment_author->init_data(['login', 'metadata']);

              $entry_comment_author_group = $entry_comment_author->get_group();
              $entry_comment_author_group->init_data(['texts']);

              $entry_comment_content = strip_tags($entry_comment->get_content());

              array_push($entry_comments_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/comment.tpl', [
                'COMMENT_ID' => $entry_comment->get_id(),
                'COMMENT_INDEX' => $entry_comment_index,
                'COMMENT_CREATED_DATE_TIMESTAMP' => date('d.m.Y H:i:s', $entry_comment->get_created_unix_timestamp()),
                'COMMENT_AUTHOR_LOGIN' => $entry_comment_author->get_login(),
                'COMMENT_AUTHOR_AVATAR_URL' => $entry_comment_author->get_avatar_url(64),
                'COMMENT_AUTHOR_GROUP_TITLE' => $entry_comment_author_group->get_title($cms_base_locale_name),
                'COMMENT_CONTENT' => ($entry_comment->is_hidden()) ? sprintf('%s: %s', $locale_data['PAGE_ENTRY_COMMENT_HIDE_LABEL'], strip_tags($entry_comment->get_hidden_reason())) : $entry_comment_content
              ]));

              $entry_comment_index++;
            }

            if (count($entry_comments_array) > 0) {
              $entry_comments_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/commentsList.tpl', [
                'COMMENTS_ITEMS' => implode($entry_comments_transformed_array)
              ]);
            }

            /**
             * @var string Заголовок записи
             */
            $entry_title = (!empty($entry->get_title($cms_base_locale_name))) ? $entry->get_title($cms_base_locale_name) : $entry->get_title($cms_base_locale_setted_name);
            $entry_title = strip_tags($entry_title);
            
            /**
             * @var string Содержание записи
             */
            $entry_content = (!empty($entry->get_content($cms_base_locale_name))) ? $entry->get_content($cms_base_locale_name) : $entry->get_content($cms_base_locale_setted_name);
            
            $entry_created_date_timestamp = date('d.m.Y H:i:s', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp = date('d.m.Y H:i:s', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp = date('d.m.Y H:i:s', $entry->get_updated_unix_timestamp());

            $entry_created_date_timestamp_without_time = date('d.m.Y', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp_without_time = date('d.m.Y', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp_without_time = date('d.m.Y', $entry->get_updated_unix_timestamp());
    
            $entry_created_date_timestamp_without_date = date('H:i:s', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp_without_date = date('H:i:s', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp_without_date = date('H:i:s', $entry->get_updated_unix_timestamp());

            $entry_created_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_updated_unix_timestamp());

            $entry_created_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_updated_unix_timestamp());
    
            $entry_created_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_created_unix_timestamp());
            $entry_published_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_published_unix_timestamp());
            $entry_updated_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_updated_unix_timestamp());

            /**
             * @property string Собранный шаблон в виде строки
             */
            $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
              'PAGE_NAME' => 'entry',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry.tpl', [
                'ENTRY_ID' => $entry->get_id(),
                'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
                'ENTRY_TITLE' => $entry_title,
                'ENTRY_CONTENT' => $parsedown->text($entry_content),
                'ENTRY_PREVIEW_URL' => ($entry->get_preview_url() != '') ? $entry->get_preview_url() : Entry::get_preview_default_url($this->system_core, 1024),
                'ENTRY_CATEGORY_TITLE' => $entry_category_title,
                'ENTRY_CATEGORY_URL' => $entry_category->get_url(),
                'ENTRY_COMMENTS_LIST' => (count($entry_comments_array) > 0) ? $entry_comments_transformed : $locale_data['PAGE_ENTRY_COMMENTS_NOT_FOUND_LABEL'],
                'ENTRY_CREATED_DATE_TIMESTAMP' => $entry_created_date_timestamp,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp : '-',
                'ENTRY_UPDATED_DATE_TIMESTAMP' => $entry_updated_date_timestamp,
                'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_created_date_timestamp_without_time,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_time : '-',
                'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_updated_date_timestamp_without_time,
                'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry_created_date_timestamp_without_date,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_date : '-',
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
              ])
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
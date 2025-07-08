<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageEntry implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

<<<<<<< HEAD
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
            $this->system_core->configurator->set_meta_description(str_replace('"', '&quot;', $entry->get_description($cms_base_locale_name)));
            $this->system_core->configurator->set_meta_keywrords(str_replace('"', '&quot;', $entry->get_keywords($cms_base_locale_name)));

            $this->page->breadcrumbs->add($locale_data['PAGE_ENTRY_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');
            $this->page->breadcrumbs->add($entry_category_title, sprintf('/entries/%s', $entry_category->get_name()));
            $this->page->breadcrumbs->add($entry->get_title($cms_base_locale_name), sprintf('/entry/%s', $entry->get_name()));
            $this->page->breadcrumbs->assembly();

            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();
            //$parsedown->setSafeMode(true);
            //$parsedown->setMarkupEscaped(true);

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

              if ($entry_comment_author != null) {
                $entry_comment_author->init_data(['login', 'metadata']);

                $entry_comment_author_group = $entry_comment_author->get_group();
                $entry_comment_author_group->init_data(['texts']);
              }

              $entry_comment_content = strip_tags($entry_comment->get_content());

              array_push($entry_comments_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/comment.tpl', [
                'COMMENT_ID' => $entry_comment->get_id(),
                'COMMENT_INDEX' => $entry_comment_index,
                'COMMENT_CREATED_DATE_TIMESTAMP' => date('d.m.Y H:i:s', $entry_comment->get_created_unix_timestamp()),
                'COMMENT_AUTHOR_LOGIN' => ($entry_comment_author != null) ? $entry_comment_author->get_login() : '{LANG:DEFAULT_TEXT_USER_DELETED}',
                'COMMENT_AUTHOR_AVATAR_URL' => ($entry_comment_author != null) ? $entry_comment_author->get_avatar_url(64) : User::get_avatar_default_url($this->system_core, 64),
                'COMMENT_AUTHOR_GROUP_TITLE' => ($entry_comment_author != null) ? $entry_comment_author_group->get_title($cms_base_locale_name) : '',
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

            $page_content_tags = [
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
            ];

            $additional_fields_data = $entry->get_additional_fields_data();
            if (count($additional_fields_data) > 0) {
              foreach ($additional_fields_data as $field_data_name => $field_data) {
                $tag_name = sprintf('ENTRY_ADDITIONAL_DATA_%s', strtoupper($field_data_name));
                $page_content_tags[$tag_name] = $field_data;
              }
            }

            /**
             * @property string Собранный шаблон в виде строки
             */
            $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
              'PAGE_NAME' => 'entry',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry.tpl', $page_content_tags)
            ]);
          } else {
            http_response_code(404);
=======
  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }
>>>>>>> develop
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    if ($this->CMSCore->urlp->getPath(1) !== null) {
      $entryName = urldecode($this->CMSCore->urlp->getPath(1));

      if (Entry::existsByName($this->CMSCore, $entryName)) {
        $entry = Entry::getByName($this->CMSCore, $entryName);
        $entry->initData(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

        if ($this->CMSCore->urlp->getParam('locale') === $localeName) {
          $this->CMSCore->theme->addLinkCanonical('/entry/' . $entry->getName());
        }

        $isVisible = false;

        $clientIsLogged = $this->CMSCore->client->isLogged(1);
        $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;

        $isVisible = $entry->isPublished();
        if (!$isVisible && $clientUser !== null) {
          $isVisible = $clientUser->getID() === 1 || $clientUser->getGroupID() === 1;
        }

        if ($isVisible) {
          http_response_code(200);

          $category = $entry->getCategory();
          $categoryTitle = $category->getTitle($localeName);

          $this->CMSCore->configurator->setMetaTitle($entry->getTitle($localeName));
          $this->CMSCore->configurator->setMetaDescription(str_replace('"', '&quot;', $entry->getDescription($localeName)));
          $this->CMSCore->configurator->setMetaKeywords(str_replace('"', '&quot;', $entry->getKeywords($localeName)));

          $this->page->breadcrumbs->add($localeData['PAGE_ENTRY_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');
          $this->page->breadcrumbs->add($categoryTitle, '/entries/' . $category->getName());
          $this->page->breadcrumbs->add($entry->getTitle($localeName), '/entry/' . $entry->getName());
          $this->page->breadcrumbs->assembly();

          /**
           * @var Parsedown Парсер markdown-разметки
           */
          $parsedown = new Parsedown();

          $commentsArray = $entry->getComments([
            'limit' => [2, 0],
            'order_by' => [
              'column' => 'createdUnixTimestamp',
              'sort' => 'desc'
            ],
            'parent_id' => 0
          ]);

          foreach ($commentsArray as $entryComment) {
            $entryComment->initData(['createdUnixTimestamp']);
          }

          usort($commentsArray, function ($a, $b)
          {
            $aCut = $a->getCreatedUnixTimestamp();
            $bCut = $b->getCreatedUnixTimestamp();

            if ($aCut !== $bCut) {
              return $aCut > $bCut ? -1 : 1;
            }

            return 0;
          });

          $entryCommentsTransformedArray = [];
          $entryCommentIndex = 1;
          foreach ($commentsArray as $entryComment) {
            $entryComment->initData(['entryID', 'authorID', 'content', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
            
            $entryCommentAuthor = $entryComment->getAuthor();

            if ($entryCommentAuthor !== null) {
              $entryCommentAuthor->initData(['login', 'metadata']);

              $entryCommentAuthorGroup = $entryCommentAuthor->getGroup();
              $entryCommentAuthorGroup->initData(['texts']);
            }

            $entryCommentContent = strip_tags($entryComment->getContent());

            array_push($entryCommentsTransformedArray, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/comment.tpl', [
              'COMMENT_ID' => $entryComment->getID(),
              'COMMENT_INDEX' => $entryCommentIndex,
              'COMMENT_CREATED_DATE_TIMESTAMP' => date('d.m.Y H:i:s', $entryComment->getCreatedUnixTimestamp()),
              'COMMENT_AUTHOR_LOGIN' => $entryCommentAuthor !== null ? $entryCommentAuthor->getLogin() : '{LANG:DEFAULT_TEXT_USER_DELETED}',
              'COMMENT_AUTHOR_AVATAR_URL' => $entryCommentAuthor !== null ? $entryCommentAuthor->getAvatarURL(64) : User::getAvatarDefaultURL($this->CMSCore, 64),
              'COMMENT_AUTHOR_GROUP_TITLE' => $entryCommentAuthor !== null ? $entryCommentAuthorGroup->getTitle($localeName) : '',
              'COMMENT_CONTENT' => $entryComment->isHidden() ? $localeData['PAGE_ENTRY_COMMENT_HIDE_LABEL'] . ' ' . strip_tags($entryComment->getHiddenReason()) : $entryCommentContent
            ]));

            $entryCommentIndex++;
          }

          if (count($commentsArray) > 0) {
            $entryCommentsTransformed = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/commentsList.tpl', [
              'COMMENTS_ITEMS' => implode($entryCommentsTransformedArray)
            ]);
          }

          /**
           * @var string Заголовок записи
           */
          $entryTitle = $entry->getTitle($localeName);
          $entryTitle = strip_tags($entryTitle);
          
          /**
           * @var string Содержание записи
           */
          $entry_content = $entry->getContent($localeName);
          
          $createdDateTimestamp = date('d.m.Y H:i:s', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestamp = date('d.m.Y H:i:s', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestamp = date('d.m.Y H:i:s', $entry->getUpdatedUnixTimestamp());

          $createdDateTimestampWithoutTime = date('d.m.Y', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestampWithoutTime = date('d.m.Y', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestampWithoutTime = date('d.m.Y', $entry->getUpdatedUnixTimestamp());
  
          $createdDateTimestampWithoutDate = date('H:i:s', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestampWithoutDate = date('H:i:s', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestampWithoutDate = date('H:i:s', $entry->getUpdatedUnixTimestamp());

          $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->getUpdatedUnixTimestamp());

          $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->getUpdatedUnixTimestamp());
  
          $createdDateTimestampISO8601WithoutDate = date('H:i:s', $entry->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601WithoutDate = date('H:i:s', $entry->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601WithoutDate = date('H:i:s', $entry->getUpdatedUnixTimestamp());

          $pageTemplateVariables = [
            'ENTRY_ID' => $entry->getID(),
            'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
            'ENTRY_TITLE' => $entryTitle,
            'ENTRY_CONTENT' => $parsedown->text($entry_content),
            'ENTRY_PREVIEW_URL' => $entry->getPreviewURL() !== '' ? $entry->getPreviewURL() : Entry::getPreviewDefaultURL($this->CMSCore, 1024),
            'ENTRY_CATEGORY_TITLE' => $categoryTitle,
            'ENTRY_CATEGORY_URL' => $category->getURL(),
            'ENTRY_COMMENTS_LIST' => count($commentsArray) > 0 ? $entryCommentsTransformed : $localeData['PAGE_ENTRY_COMMENTS_NOT_FOUND_LABEL'],
            'ENTRY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $entry->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestamp : date('d.m.Y H:i:s', 0),
            'ENTRY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutTime : date('d.m.Y', 0),
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutDate,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutDate : date('H:i:s', 0),
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
          ];

          $additionalFieldsData = $entry->getAdditionalFieldsData();
          if (count($additionalFieldsData) > 0) {
            foreach ($additionalFieldsData as $name => $data) {
              $variableName = 'ENTRY_ADDITIONAL_DATA_' . strtoupper($name);
              $pageTemplateVariables[$variableName] = $data;
            }
          }

          /**
           * @property string Собранный шаблон в виде строки
           */
          $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
            'PAGE_NAME' => 'entry',
            'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry.tpl', $pageTemplateVariables)
          ]);
        } else {
          http_response_code(404);

          $pageError = new PageError($this->CMSCore, $this->page, 404);
          $pageError->assembly();
          $this->assembled = $pageError->assembled;
        }
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();
        $this->assembled = $pageError->assembled;
      }
    } else {
      http_response_code(404);

      $pageError = new PageError($this->CMSCore, $this->page, 404);
      $pageError->assembly();
      $this->assembled = $pageError->assembled;
    }
  }
}
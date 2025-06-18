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
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageEntry implements InterfacePage {
    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @param  Page $page
     * @return void
     */
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      if (!is_null($this->CMSCore->urlp->get_path(1))) {
        $entryName = urldecode($this->CMSCore->urlp->get_path(1));

        if (Entry::exists_by_name($this->CMSCore, $entryName)) {
          $entry = Entry::get_by_name($this->CMSCore, $entryName);
          $entry->init_data(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

          if ($this->CMSCore->urlp->get_param('locale') === $localeName) {
            $this->CMSCore->theme->add_link_canonical('/entry/' . $entry->get_name());
          }

          $isVisible = false;

          $clientIsLogged = $this->CMSCore->client->is_logged(1);
          $clientUser = $clientIsLogged ? $this->CMSCore->client->get_user(1) : null;

          $isVisible = $entry->is_published() ? true : false;
          if (!$isVisible) {
            if ($clientUser != null) {
              $isVisible = ($clientUser->get_id() === 1 || $clientUser->get_group_id() === 1) ? true : false;
            }
          }

          if ($isVisible) {
            http_response_code(200);

            $category = $entry->get_category();
            $categoryTitle = $category->get_title($localeName);

            $this->CMSCore->configurator->set_meta_title($entry->get_title($localeName));
            $this->CMSCore->configurator->set_meta_description(str_replace('"', '&quot;', $entry->get_description($localeName)));
            $this->CMSCore->configurator->set_meta_keywrords(str_replace('"', '&quot;', $entry->get_keywords($localeName)));

            $this->page->breadcrumbs->add($localeData['PAGE_ENTRY_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');
            $this->page->breadcrumbs->add($categoryTitle, '/entries/' . $category->get_name());
            $this->page->breadcrumbs->add($entry->get_title($localeName), '/entry/' . $entry->get_name());
            $this->page->breadcrumbs->assembly();

            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();

            $commentsArray = $entry->get_comments([
              'limit' => [2, 0],
              'order_by' => [
                'column' => 'createdUnixTimestamp',
                'sort' => 'desc'
              ],
              'parent_id' => 0
            ]);
            foreach ($commentsArray as $entryComment) {
              $entryComment->init_data(['createdUnixTimestamp']);
            }

            usort($commentsArray, function ($a, $b) {
              $aCut = $a->get_created_unix_timestamp();
              $bCut = $b->get_created_unix_timestamp();

              if ($aCut != $bCut) {
                return ($aCut > $bCut) ? -1 : 1;
              }

              return 0;
            });

            $entryCommentsTransformedArray = [];
            $entryCommentIndex = 1;
            foreach ($commentsArray as $entryComment) {
              $entryComment->init_data(['entry_id', 'authorID', 'content', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
              
              $entryCommentAuthor = $entryComment->get_author();

              if ($entryCommentAuthor !== null) {
                $entryCommentAuthor->init_data(['login', 'metadata']);

                $entryCommentAuthorGroup = $entryCommentAuthor->get_group();
                $entryCommentAuthorGroup->init_data(['texts']);
              }

              $entryCommentContent = strip_tags($entryComment->get_content());

              array_push($entryCommentsTransformedArray, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/comment.tpl', [
                'COMMENT_ID' => $entryComment->get_id(),
                'COMMENT_INDEX' => $entryCommentIndex,
                'COMMENT_CREATED_DATE_TIMESTAMP' => date('d.m.Y H:i:s', $entryComment->get_created_unix_timestamp()),
                'COMMENT_AUTHOR_LOGIN' => ($entryCommentAuthor != null) ? $entryCommentAuthor->get_login() : '{LANG:DEFAULT_TEXT_USER_DELETED}',
                'COMMENT_AUTHOR_AVATAR_URL' => ($entryCommentAuthor != null) ? $entryCommentAuthor->get_avatar_url(64) : User::get_avatar_default_url($this->CMSCore, 64),
                'COMMENT_AUTHOR_GROUP_TITLE' => ($entryCommentAuthor != null) ? $entryCommentAuthorGroup->get_title($localeName) : '',
                'COMMENT_CONTENT' => ($entryComment->is_hidden()) ? sprintf('%s: %s', $localeData['PAGE_ENTRY_COMMENT_HIDE_LABEL'], strip_tags($entryComment->get_hidden_reason())) : $entryCommentContent
              ]));

              $entryCommentIndex++;
            }

            if (count($commentsArray) > 0) {
              $entryCommentsTransformed = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/commentsList.tpl', [
                'COMMENTS_ITEMS' => implode($entryCommentsTransformedArray)
              ]);
            }

            /**
             * @var string Заголовок записи
             */
            $entryTitle = $entry->get_title($localeName);
            $entryTitle = strip_tags($entryTitle);
            
            /**
             * @var string Содержание записи
             */
            $entry_content = $entry->get_content($localeName);
            
            $createdDateTimestamp = date('d.m.Y H:i:s', $entry->get_created_unix_timestamp());
            $publishedDateTimestamp = date('d.m.Y H:i:s', $entry->get_published_unix_timestamp());
            $updatedDateTimestamp = date('d.m.Y H:i:s', $entry->get_updated_unix_timestamp());

            $createdDateTimestampWithoutTime = date('d.m.Y', $entry->get_created_unix_timestamp());
            $publishedDateTimestampWithoutTime = date('d.m.Y', $entry->get_published_unix_timestamp());
            $updatedDateTimestampWithoutTime = date('d.m.Y', $entry->get_updated_unix_timestamp());
    
            $createdDateTimestampWithoutDate = date('H:i:s', $entry->get_created_unix_timestamp());
            $publishedDateTimestampWithoutDate = date('H:i:s', $entry->get_published_unix_timestamp());
            $updatedDateTimestampWithoutDate = date('H:i:s', $entry->get_updated_unix_timestamp());

            $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->get_created_unix_timestamp());
            $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->get_published_unix_timestamp());
            $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entry->get_updated_unix_timestamp());

            $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->get_created_unix_timestamp());
            $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->get_published_unix_timestamp());
            $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entry->get_updated_unix_timestamp());
    
            $createdDateTimestampISO8601WithoutDate = date('H:i:s', $entry->get_created_unix_timestamp());
            $publishedDateTimestampISO8601WithoutDate = date('H:i:s', $entry->get_published_unix_timestamp());
            $updatedDateTimestampISO8601WithoutDate = date('H:i:s', $entry->get_updated_unix_timestamp());

            $pageTemplateVariables = [
              'ENTRY_ID' => $entry->get_id(),
              'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
              'ENTRY_TITLE' => $entryTitle,
              'ENTRY_CONTENT' => $parsedown->text($entry_content),
              'ENTRY_PREVIEW_URL' => ($entry->get_preview_url() != '') ? $entry->get_preview_url() : Entry::get_preview_default_url($this->CMSCore, 1024),
              'ENTRY_CATEGORY_TITLE' => $categoryTitle,
              'ENTRY_CATEGORY_URL' => $category->get_url(),
              'ENTRY_COMMENTS_LIST' => (count($commentsArray) > 0) ? $entryCommentsTransformed : $localeData['PAGE_ENTRY_COMMENTS_NOT_FOUND_LABEL'],
              'ENTRY_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entry->get_published_unix_timestamp() > 0) ? $publishedDateTimestamp : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entry->get_published_unix_timestamp() > 0) ? $publishedDateTimestampWithoutTime : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutDate,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entry->get_published_unix_timestamp() > 0) ? $publishedDateTimestampWithoutDate : '-',
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

            $additionalFieldsData = $entry->get_additional_fields_data();
            if (count($additionalFieldsData) > 0) {
              foreach ($additionalFieldsData as $name => $data) {
                $variableName = sprintf('ENTRY_ADDITIONAL_DATA_%s', strtoupper($name));
                $pageTemplateVariables[$variableName] = $data;
              }
            }

            /**
             * @property string Собранный шаблон в виде строки
             */
            $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'entry',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry.tpl', $pageTemplateVariables)
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

}

?>
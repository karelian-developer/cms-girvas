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
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageEntry implements InterfacePage
{
  public CMSCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param  CMSCore $CMSCore
   * @param  Page $page
   * @return void
   */
  public function __construct(CMSCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/entry.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }

  /**
   * Проверка возможности отображения для пользователя
   * 
   * Объект User должен передаваться с инициализированными данными:
   * - metadata
   * 
   * @param bool $isPublished
   * @param ?User $user
   * 
   * @return bool
   */
  public function isVisible(bool $isPublished, ?User $user) : bool
  {
    if ($isPublished && $user !== null) {
      $userGroup = $user->getGroup();

      if ($userGroup !== null) {
        return $user->isSuperAdmin()
          || $userGroup->isSuperGroup()
          || $userGroup->hasPermissionEditorEntriesEdit()
      }
    }

    return false;
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->addRequiredStyles();

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

        $clientIsLogged = $this->CMSCore->client->isLogged(1);
        $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;

        $isPublished = $entry->isPublished();
        
        if ($this->isVisible($isPublished, $clientUser)) {
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
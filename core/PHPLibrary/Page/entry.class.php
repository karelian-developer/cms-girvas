<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\NadvoParse as NadvoParse;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;

class PageEntry implements InterfacePage
{
  public string $assembled = '';
  private ?EntityTypeContent $targetObject = null;
  public array $metaOpenGraphAllowed = [
    'title',
    'description',
    'type',
    'url',
    'image',
    'site_name'
  ];

  /**
   * __construct
   *
   * @param  CoreInterface $CMSCore
   * @param  InterfacePage $page
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public InterfacePage $page
  ) {
    $this->initTargetObject();

    if ($this->targetObject !== null) {
      $this->targetObject->initData(
        [
          'id',
          'categoryID',
          'authorID',
          'texts',
          'name',
          'createdUnixTimestamp',
          'updatedUnixTimestamp',
          'metadata'
        ]
      );
    }

    $this->initMetaOpenGraph();
  }

  /**
   * Инициализация данных OpenGraph
   * 
   * @return void
   */
  private function initMetaOpenGraph() : void
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSLocale = $this->CMSCore->locale;
    $CMSLocaleName = $CMSLocale->getName();

    $imageURL = $this->targetObject->getPreviewURL() !== ''
      ? $this->targetObject->getPreviewURL()
      : $this->targetObject::getPreviewDefaultURL($this->CMSCore, 1024);

    $this->metaOpenGraphAllowed['title'] = $this->targetObject->getSEOTitle($CMSLocaleName);
    $this->metaOpenGraphAllowed['description'] = $this->targetObject->getSEODescription($CMSLocaleName);
    $this->metaOpenGraphAllowed['type'] = 'website';
    $this->metaOpenGraphAllowed['url'] = $this->CMSCore->getCMSLink() . $imageURL;
    $this->metaOpenGraphAllowed['image'] = $this->CMSCore->getCMSLink() . $this->targetObject->getPreviewURL();
    $this->metaOpenGraphAllowed['site_name'] = $CMSConfigurator->getSiteTitle();
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
    $publishedUnixTimestamp = $this->targetObject->getPublishedUnixTimestamp();

    if ($isPublished && $publishedUnixTimestamp < time()) {
      return true;
    }

    if ($user !== null) {
      $userGroup = $user->getGroup();

      if ($userGroup !== null) {
        return $user->isSuperAdmin()
          || $userGroup->isSuperGroup()
          || $userGroup->hasPermissionEditorEntriesEdit();
      }
    }
    
    return false;
  }

  /**
   * Инициализировать целевой объект страницы
   * 
   * @return void
   */
  private function initTargetObject() : void
  {
    if ($this->CMSCore->urlp->getPath(1) !== null) {
      $entryName = urldecode($this->CMSCore->urlp->getPath(1));

      if (Entry::existsByName($this->CMSCore, $entryName)) {
        $this->targetObject = Entry::getByName($this->CMSCore, $entryName);
      }
    }
  }

  /**
   * Получить целевой объект страницы
   * 
   * @return ?EntityTypeContent
   */
  public function getTargetObject() : ?EntityTypeContent
  {
    return $this->targetObject;
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

    $entry = $this->targetObject;

    if ($entry !== null) {
      $entryName = $this->CMSCore->urlp->getPath(1);
      $entryName = urldecode($entryName);

      if ($this->CMSCore->urlp->getParam('locale') === $localeName) {
        $this->CMSCore->theme->addLinkCanonical('/entry/' . $entry->getName());
      }

      $clientIsLogged = $this->CMSCore->client->isLogged(1);
      $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;

      if ($clientUser !== null) {
        $clientUser->initData(['metadata']);
      }

      $isPublished = $entry->isPublished();
      
      if ($this->isVisible($isPublished, $clientUser)) {
        http_response_code(200);

        $category = $entry->getCategory();
        $categoryTitle = $category->getTitle($localeName);

        $entryTitle = strip_tags($entry->getTitle($localeName));
        $entrySEOTitle = strip_tags($entry->getSEOTitle($localeName));
        $entrySEOTitle = $entrySEOTitle !== ''
          ? $entrySEOTitle
          : $entryTitle;

        $entryDescription = strip_tags($entry->getDescription($localeName));
        $entrySEODescription = strip_tags($entry->getSEODescription($localeName));
        $entrySEODescription = $entrySEODescription !== ''
          ? $entrySEODescription
          : $entryDescription;
        $entrySEODescription = str_replace('"', '&quot;', $entrySEODescription);

        $entryKeywords = $entry->getKeywords($localeName);
        $entryKeywords = str_replace('"', '&quot;', $entryKeywords);

        $this->CMSCore->configurator->setMetaTitle($entrySEOTitle);
        $this->CMSCore->configurator->setMetaDescription($entrySEODescription);
        $this->CMSCore->configurator->setMetaKeywords($entryKeywords);

        $this->page->breadcrumbs->add($localeData['PAGE_ENTRY_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');
        $this->page->breadcrumbs->add($categoryTitle, '/entries/' . $category->getName());
        $this->page->breadcrumbs->add($entryTitle, '/entry/' . $entry->getName());
        $this->page->breadcrumbs->assembly();

        $nadvoParse = new NadvoParse();

        $commentsArray = $entry->getComments([
          'limit' => [2, 0],
          'orderBy' => [
            'column' => 'createdUnixTimestamp',
            'sort' => 'desc'
          ],
          'parentID' => 0
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
         * @var string Содержание записи
         */
        $entryContent = $entry->getContent($localeName);

        $siteTimezone = $this->CMSCore->configurator->getSiteTimezone();
        
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

        $createdDateTimestampISO8601TZ = new DateTime();
        $createdDateTimestampISO8601TZ->setTimestamp($entry->getCreatedUnixTimestamp());
        $createdDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

        $publishedDateTimestampISO8601TZ = new DateTime();
        $publishedDateTimestampISO8601TZ->setTimestamp($entry->getPublishedUnixTimestamp());
        $publishedDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

        $updatedDateTimestampISO8601TZ = new DateTime();
        $updatedDateTimestampISO8601TZ->setTimestamp($entry->getUpdatedUnixTimestamp());
        $updatedDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

        $author = $entry->getAuthor();
        $authorLogin = $author->getLogin();
        $authorName = $author->getName();
        $authorSurname = $author->getSurname();
        $authorPatronymic = $author->getPatronymic();

        $entryPrevious = $entry->getPreviousEntry();
        $entryNext = $entry->getNextEntry();

        if ($entryPrevious !== null) {
          $entryPrevious->initData(['name', 'texts', 'metadata']);
        }

        if ($entryNext !== null) {
          $entryNext->initData(['name', 'texts', 'metadata']);
        }

        $pageTemplateVariables = [
          'ENTRY_ID' => $entry->getID(),
          'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
          'ENTRY_TITLE' => $entryTitle,
          'ENTRY_DESCRIPTION' => $entryDescription,
          'ENTRY_CONTENT' => $nadvoParse->parse($entryContent),
          'ENTRY_PREVIEW_URL' => $entry->getPreviewURL() !== '' ? $entry->getPreviewURL() : Entry::getPreviewDefaultURL($this->CMSCore, 1024),
          'ENTRY_CATEGORY_TITLE' => $categoryTitle,
          'ENTRY_CATEGORY_URL' => $category->getURL(),
          'ENTRY_COMMENTS_LIST' => count($commentsArray) > 0 ? $entryCommentsTransformed : $localeData['PAGE_ENTRY_COMMENTS_NOT_FOUND_LABEL'],
          'ENTRY_AUTHOR_LOGIN' => $authorLogin,
          'ENTRY_AUTHOR_NAME' => $authorName,
          'ENTRY_AUTHOR_SURNAME' => $authorSurname,
          'ENTRY_AUTHOR_PATRONYMIC' => $authorPatronymic,
          'ENTRY_PREVIOUS_TITLE' => ($entryPrevious !== null)
            ? strip_tags($entryPrevious->getTitle($localeName))
            : '',
          'ENTRY_PREVIOUS_DESCRIPTION' => ($entryPrevious !== null)
            ? strip_tags($entryPrevious->getDescription($localeName))
            : '',
          'ENTRY_PREVIOUS_URL' => ($entryPrevious !== null)
            ? $entryPrevious->getURL()
            : '#',
          'ENTRY_NEXT_TITLE' => ($entryNext !== null)
            ? strip_tags($entryNext->getTitle($localeName))
            : '',
          'ENTRY_NEXT_DESCRIPTION' => ($entryNext !== null)
            ? strip_tags($entryNext->getDescription($localeName))
            : '',
          'ENTRY_NEXT_URL' => ($entryNext !== null)
            ? $entryNext->getURL()
            : '#',
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
          'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $createdDateTimestampISO8601TZ->format('c'),
          'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $publishedDateTimestampISO8601TZ->format('c'),
          'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $updatedDateTimestampISO8601TZ->format('c'),
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
  }
}
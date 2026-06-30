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
  public array $metaOpenGraphAllowed = [];

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

      $this->initMetaOpenGraph();
    }
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

    $title = strip_tags($this->targetObject->getTitle($CMSLocaleName));
    $SEOTitle = strip_tags($this->targetObject->getSEOTitle($CMSLocaleName));
    $SEOTitle = $SEOTitle !== ''
      ? $SEOTitle
      : $title;

    $description = strip_tags($this->targetObject->getDescription($CMSLocaleName));
    $SEODescription = strip_tags($this->targetObject->getSEODescription($CMSLocaleName));
    $SEODescription = $SEODescription !== ''
      ? $SEODescription
      : $description;
    $SEODescription = str_replace('"', '&quot;', $SEODescription);

    $this->metaOpenGraphAllowed['title'] = $SEOTitle;
    $this->metaOpenGraphAllowed['description'] = $SEODescription;
    $this->metaOpenGraphAllowed['type'] = 'website';
    $this->metaOpenGraphAllowed['url'] = rtrim($this->CMSCore->getCMSLink(), '/') . $this->targetObject->getURL();
    $this->metaOpenGraphAllowed['image'] = rtrim($this->CMSCore->getCMSLink(), '/') . $imageURL;
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
   * Сборка списка локализаций для записи
   * 
   * @param array $localesData
   * 
   * @return string
   */
  private function assemblyLocalesItems(array $localesData) : string
  {
    $document = new DOMDocument('1.0', 'UTF-8');

    foreach ($localesData as $localeData) {
      $itemElement = $document->createElement('li', $localeData['title']);
      $itemElement->setAttribute('class', 'entry-locales');

      if (!empty($localeData['iconURL'])) {
        $iconElement = $document->createElement('img');
        $iconElement->setAttribute('class', 'entry-locales__locale-icon');
        $iconElement->setAttribute('src', $localeData['iconURL']);
        $itemElement->prepend($iconElement);
      }

      $document->appendChild($itemElement);
    }

    return $document->saveHTML();
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
        $category->initData(['name', 'texts', 'parentID']);

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
        $parentChain = $category->getParentChain();
        foreach ($parentChain as $chainCategory) {
          $chainCategory->initData(['name', 'texts']);
          $this->page->breadcrumbs->add(
            $chainCategory->getTitle($localeName),
            '/entries/' . $chainCategory->getName()
          );
        }
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
            'COMMENT_AUTHOR_AVATAR_URL' => $entryCommentAuthor !== null ? $entryCommentAuthor->getAvatarURL(128) : User::getAvatarDefaultURL($this->CMSCore, 64),
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

        $siteTimezone = $this->CMSCore->configurator->getSiteTimezone();
        
        $createdDateTimestamp = $entry->getCreatedUnixTimestamp();
        $publishedDateTimestamp = $entry->getPublishedUnixTimestamp();
        $updatedDateTimestamp = $entry->getUpdatedUnixTimestamp();

        $author = $entry->getAuthor();
        if ($author !== null) {
          $author->initData(['login']);
        }

        $completedLocalesData = $entry->getCompletedLocalesData($this->CMSCore);
        $completedLocales = $this->assemblyLocalesItems($completedLocalesData);

        $entryPrevious = $entry->getPreviousEntry();
        $entryNext = $entry->getNextEntry();

        if ($entryPrevious !== null) {
          $entryPrevious->initData(['name', 'texts', 'metadata']);
        }

        if ($entryNext !== null) {
          $entryNext->initData(['name', 'texts', 'metadata']);
        }

        $templateContent = ThemeCollector::getTemplateFileContent(
          $this->CMSCore->theme,
          'templates/page/entry.tpl'
        );

        $templatesAssembled = [];

        $createdUnixTimestamp = $entry->getCreatedUnixTimestamp();
        $publishedUnixTimestamp = $entry->getPublishedUnixTimestamp();
        $updatedUnixTimestamp = $entry->getUpdatedUnixTimestamp();

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_ID')) {
          $value = $entry !== null ? $entry->getID() : 0;

          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_ID',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_BREADCRUMPS')) {
          $value = $entry !== null ? $this->page->breadcrumbs->assembled : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'PAGE_BREADCRUMPS',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_TITLE')) {
          $value = $entry !== null ? $entry->getTitle($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_TITLE',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_DESCRIPTION')) {
          $value = $entry !== null ? $entry->getDescription($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_DESCRIPTION',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CONTENT')) {
          $value = $entry !== null ? $entry->getContent($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CONTENT',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              $nadvoParse->parse(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'))
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PREVIEW_URL')) {
          $value = $entry->getPreviewURL() !== ''
            ? $entry->getPreviewURL() :
            Entry::getPreviewDefaultURL($this->CMSCore, 1024);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PREVIEW_URL',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CATEGORY_TITLE')) {
          $value = $category !== null ? $category->getTitle($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CATEGORY_TITLE',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CATEGORY_URL')) {
          $value = $category !== null ? $category->getURL() : '/entries';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CATEGORY_URL',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_URL')) {
          $value = $entry !== null ? $entry->getURL() : '#';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_URL',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_COMMENTS_LIST')) {
          $value = count($commentsArray) > 0 ? $entryCommentsTransformed : $localeData['PAGE_ENTRY_COMMENTS_NOT_FOUND_LABEL'];
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_COMMENTS_LIST',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_AUTHOR_LOGIN')) {
          $value = $author !== null ? $author->getLogin() : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_AUTHOR_LOGIN',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_AUTHOR_NAME')) {
          $value = $author !== null ? $author->getName() : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_AUTHOR_NAME',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_AUTHOR_SURNAME')) {
          $value = $author !== null ? $author->getSurname() : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_AUTHOR_SURNAME',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_AUTHOR_PATRONYMIC')) {
          $value = $author !== null ? $author->getPatronymic() : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_AUTHOR_PATRONYMIC',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_LOCALES_LIST')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_LOCALES_LIST',
            $completedLocales
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PREVIOUS_TITLE')) {
          $value = $entryPrevious !== null ? $entryPrevious->getTitle($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PREVIOUS_TITLE',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PREVIOUS_DESCRIPTION')) {
          $value = $entryPrevious !== null ? $entryPrevious->getDescription($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PREVIOUS_DESCRIPTION',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PREVIOUS_URL')) {
          $value = $entryPrevious !== null ? $entryPrevious->getURL() : '#';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PREVIOUS_URL',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_NEXT_TITLE')) {
          $value = $entryNext !== null ? $entryNext->getTitle($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_NEXT_TITLE',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_NEXT_DESCRIPTION')) {
          $value = $entryNext !== null ? $entryNext->getDescription($localeName) : '';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_NEXT_DESCRIPTION',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_NEXT_URL')) {
          $value = $entryNext !== null ? $entryNext->getURL() : '#';
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_NEXT_URL',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          $value = date('d.m.Y H:i:s', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP')) {
          $value = $publishedUnixTimestamp > 0 ?  date('d.m.Y H:i:s', $publishedUnixTimestamp) : date('d.m.Y H:i:s', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP')) {
          $value = date('d.m.Y H:i:s', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          $value = date('d.m.Y', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          $value = $publishedUnixTimestamp > 0 ?  date('d.m.Y', $publishedUnixTimestamp) : date('d.m.Y', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          $value = date('d.m.Y', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          $value = date('H:i:s', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          $value = $publishedUnixTimestamp > 0 ?  date('H:i:s', $publishedUnixTimestamp) : date('H:i:s', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          $value = date('H:i:s', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601')) {
          $value = date('Y-m-dH:i:s', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601')) {
          $value = $publishedUnixTimestamp > 0 ?  date('Y-m-dH:i:s', $publishedUnixTimestamp) : date('Y-m-dH:i:s', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601')) {
          $value = date('Y-m-dH:i:s', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          $value = date('Y-m-d', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          $value = $publishedUnixTimestamp > 0 ?  date('Y-m-d', $publishedUnixTimestamp) : date('Y-m-d', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          $value = date('Y-m-d', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          $value = date('H:i:s', $createdDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          $value = $publishedUnixTimestamp > 0 ?  date('H:i:s', $publishedUnixTimestamp) : date('H:i:s', 0);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          $value = date('H:i:s', $updatedDateTimestamp);
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE')) {
          $datetime = new DateTime();
          $dateTimezone = new DateTimeZone($siteTimezone);
          $datetime->setTimestamp($createdDateTimestamp);
          $datetime->setTimezone($dateTimezone);
          $value = $datetime->format('c');
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_TIMEZONE')) {
          $publishedUnixTimestampFix = $publishedUnixTimestamp > 0 ? $publishedUnixTimestamp : 0;
          
          $datetime = new DateTime();
          $dateTimezone = new DateTimeZone($siteTimezone);
          $datetime->setTimestamp($publishedUnixTimestampFix);
          $datetime->setTimezone($dateTimezone);
          $value = $datetime->format('c');
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_TIMEZONE',
            $value
          );
        }

        if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE')) {
          $datetime = new DateTime();
          $dateTimezone = new DateTimeZone($siteTimezone);
          $datetime->setTimestamp($updatedDateTimestamp);
          $datetime->setTimezone($dateTimezone);
          $value = $datetime->format('c');
          
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE',
            $value
          );
        }

        $additionalFieldsData = $entry->getAdditionalFieldsData();
        if (count($additionalFieldsData) > 0) {
          foreach ($additionalFieldsData as $name => $data) {
            $variableName = 'ENTRY_ADDITIONAL_DATA_' . strtoupper($name);
            $templatesAssembled[$variableName] = $data;
          }
        }

        /**
         * @property string Собранный шаблон в виде строки
         */
        $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
          'PAGE_NAME' => 'entry',
          'PAGE_CONTENT' => ThemeCollector::assemblyFileContent(
            $this->CMSCore->theme,
            'templates/page/entry.tpl',
            $templatesAssembled
          )
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
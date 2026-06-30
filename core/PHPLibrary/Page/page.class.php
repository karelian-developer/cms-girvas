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
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\NadvoParse as NadvoParse;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\User as User;
use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;
use \DOMDocument as DOMDocument;

class PagePage implements InterfacePage
{
  public string $assembled = '';
  private ?EntityTypeContent $targetObject = null;
  public array $metaOpenGraphAllowed = [];

  /**
   * __construct
   *
   * @param  CoreInterface $CMSCore
   * @param  InterfacePage $page
   * 
   * @return void
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
          'authorID',
          'texts',
          'name',
          'createdUnixTimestamp',
          'updatedUnixTimestamp',
          'metadata'
        ]
      );
    }
  }

  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/static.css'] as $stylePath) {
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

    if ($isPublished) {
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
      $name = urldecode($this->CMSCore->urlp->getPath(1));

      if (PageStatic::existsByName($this->CMSCore, $name)) {
        $this->targetObject = PageStatic::getByName($this->CMSCore, $name);
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
    $ulElement = $document->createElement('ul');
    $ulElement->setAttribute('class', 'page-locales');

    foreach ($localesData as $localeData) {
      $itemElement = $document->createElement('li');
      $itemElement->setAttribute('class', 'page-locales__item');

      $aElement = $document->createElement('a', $localeData['title']);
      $aElement->setAttribute('class', 'page-locales__link');

      $itemElement->appendChild($aElement);

      if (!empty($localeData['iconURL'])) {
          $iconElement = $document->createElement('img');
          $iconElement->setAttribute('class', 'page-locales__locale-icon');
          $iconElement->setAttribute('src', $localeData['iconURL']);
          $itemElement->prepend($iconElement);
      }

      $ulElement->appendChild($itemElement);
    }

    $document->appendChild($ulElement);

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

    if (!is_null($this->CMSCore->urlp->getPath(1))) {
      $pageStaticName = urldecode($this->CMSCore->urlp->getPath(1));

      if (PageStatic::existsByName($this->CMSCore, $pageStaticName)) {
        $pageStatic = PageStatic::getByName($this->CMSCore, $pageStaticName);
        $pageStatic->initData(['id', 'texts', 'name', 'authorID', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

        if ($this->CMSCore->urlp->getParam('locale') === $localeName) {
          $this->CMSCore->theme->addLinkCanonical('/page/' . $pageStatic->getName());
        }

        $clientIsLogged = $this->CMSCore->client->isLogged(1);
        $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;

        if ($clientUser !== null) {
          $clientUser->initData(['metadata']);
        }

        $isPublished = $pageStatic->isPublished();

        if ($this->isVisible($isPublished, $clientUser)) {
          http_response_code(200);

          $pageStaticTitle = strip_tags($pageStatic->getTitle($localeName));
          $pageStaticSEOTitle = strip_tags($pageStatic->getSEOTitle($localeName));
          $pageStaticSEOTitle = $pageStaticSEOTitle !== ''
            ? $pageStaticSEOTitle
            : $pageStaticTitle;

          $pageStaticDescription = strip_tags($pageStatic->getTitle($localeName));
          $pageStaticSEODescription = strip_tags($pageStatic->getSEOTitle($localeName));
          $pageStaticSEODescription = $pageStaticSEODescription !== ''
            ? $pageStaticSEODescription
            : $pageStaticDescription;
          $pageStaticSEODescription = str_replace('"', '&quot;', $pageStaticSEODescription);

          $pageStaticKeywords = $pageStatic->getKeywords($localeName);
          $pageStaticKeywords = str_replace('"', '&quot;', $pageStaticKeywords);

          $this->page->breadcrumbs->add($localeData['PAGE_STATIC_PAGE_BREADCRUMPS_INDEX_LABEL'], '/');
          $this->page->breadcrumbs->add($pageStaticTitle, $pageStatic->getName());
          $this->page->breadcrumbs->assembly();

          $this->CMSCore->configurator->setMetaTitle($pageStaticSEOTitle);
          $this->CMSCore->configurator->setMetaDescription($pageStaticSEODescription);
          $this->CMSCore->configurator->setMetaKeywords($pageStaticKeywords);

          $nadvoParse = new NadvoParse();

          /**
           * @var string Содержание статической страницы
           */
          $pageStaticContent = $pageStatic->getContent($localeName);

          $siteTimezone = $this->CMSCore->configurator->getSiteTimezone();

          $createdDateTimestamp = date('d.m.Y H:i:s', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestamp = date('d.m.Y H:i:s', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestamp = date('d.m.Y H:i:s', $pageStatic->getUpdatedUnixTimestamp());

          $createdDateTimestampWithoutTime = date('d.m.Y', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestampWithoutTime = date('d.m.Y', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestampWithoutTime = date('d.m.Y', $pageStatic->getUpdatedUnixTimestamp());
  
          $createdDateTimestampWithoutData = date('H:i:s', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestampWithoutData = date('H:i:s', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestampWithoutData = date('H:i:s', $pageStatic->getUpdatedUnixTimestamp());

          $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->getUpdatedUnixTimestamp());

          $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->getUpdatedUnixTimestamp());
  
          $createdDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->getCreatedUnixTimestamp());
          $publishedDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->getPublishedUnixTimestamp());
          $updatedDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->getUpdatedUnixTimestamp());

          $createdDateTimestampISO8601TZ = new DateTime();
          $createdDateTimestampISO8601TZ->setTimestamp($pageStatic->getCreatedUnixTimestamp());
          $createdDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

          $publishedDateTimestampISO8601TZ = new DateTime();
          $publishedDateTimestampISO8601TZ->setTimestamp($pageStatic->getPublishedUnixTimestamp());
          $publishedDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

          $updatedDateTimestampISO8601TZ = new DateTime();
          $updatedDateTimestampISO8601TZ->setTimestamp($pageStatic->getUpdatedUnixTimestamp());
          $updatedDateTimestampISO8601TZ->setTimezone(new DateTimeZone($siteTimezone));

          $author = $pageStatic->getAuthor();
          $authorLogin = $author->getLogin();
          $authorName = $author->getName();
          $authorSurname = $author->getSurname();
          $authorPatronymic = $author->getPatronymic();

          $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
          $completedLocales = $this->assemblyLocalesItems($completedLocalesData);

          $pageTemplateVariables = [
            'PAGE_ID' => $pageStatic->getID(),
            'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
            'PAGE_TITLE' => $pageStaticTitle,
            'PAGE_CONTENT' => $nadvoParse->parse($pageStaticContent),
            'PAGE_PREVIEW_URL' => $pageStatic->getPreviewURL() !== '' ? $pageStatic->getPreviewURL() : PageStatic::getPreviewDefaultURL($this->CMSCore, 1024),
            'PAGE_AUTHOR_LOGIN' => $authorLogin,
            'PAGE_AUTHOR_NAME' => $authorName,
            'PAGE_AUTHOR_SURNAME' => $authorSurname,
            'PAGE_AUTHOR_PATRONYMIC' => $authorPatronymic,
            'PAGE_LOCALES_LIST' => $completedLocales,
            'PAGE_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
            'PAGE_PUBLISHED_DATE_TIMESTAMP' => $pageStatic->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestamp : date('d.m.Y H:i:s', 0),
            'PAGE_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
            'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
            'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $pageStatic->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutTime : date('d.m.Y', 0),
            'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
            'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutData,
            'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $pageStatic->getPublishedUnixTimestamp() > 0 ? $publishedDateTimestampWithoutData : date('H:i:s', 0),
            'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $updatedDateTimestampWithoutData,
            'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601' => $createdDateTimestampISO8601,
            'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $publishedDateTimestampISO8601,
            'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601' => $updatedDateTimestampISO8601,
            'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $createdDateTimestampISO8601TZ->format('c'),
            'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $publishedDateTimestampISO8601TZ->format('c'),
            'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_TIMEZONE' => $updatedDateTimestampISO8601TZ->format('c'),
            'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $createdDateTimestampISO8601WithoutTime,
            'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $publishedDateTimestampISO8601WithoutTime,
            'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $updatedDateTimestampISO8601WithoutTime,
            'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $createdDateTimestampISO8601WithoutData,
            'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $publishedDateTimestampISO8601WithoutData,
            'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $updatedDateTimestampISO8601WithoutData
          ];

          $additionalFieldsData = $pageStatic->getAdditionalFieldsData();
          if (count($additionalFieldsData) > 0) {
            foreach ($additionalFieldsData as $name => $data) {
              $variableName = 'PAGE_ADDITIONAL_DATA_' . strtoupper($name);
              $pageTemplateVariables[$variableName] = $data;
            }
          }

          /** @var string Путь до персонального шаблона */
          $personalTemplatePath = $pageStatic->existsPersonalTemplateFile() ? 'templates/' . $pageStatic->getPersonalTemplatePath() : 'templates/page/static.tpl';

          $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
            'PAGE_NAME' => 'static',
            'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, $personalTemplatePath, $pageTemplateVariables)
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
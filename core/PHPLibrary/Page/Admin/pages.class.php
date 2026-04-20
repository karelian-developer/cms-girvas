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

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Pages as Pages;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;
use \DOMImplementation as DOMImplementation;

class PagePages implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_CONTENT_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false
    ],
    'entries' => [
      'name' => 'entries',
      'iconName' => 'entries',
      'link' => '/entries',
      'permanent' => false,
      'isActive' => false
    ],
    'pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'isActive' => true
    ],
    'categories' => [
      'name' => 'categories',
      'iconName' => 'entriesCategories',
      'link' => '/entriesCategories',
      'permanent' => false,
      'isActive' => false
    ],
    'comments' => [
      'name' => 'comments',
      'iconName' => 'entriesComments',
      'link' => '/entriesComments',
      'permanent' => false,
      'isActive' => false
    ],
    'samples' => [
      'name' => 'samples',
      'iconName' => 'entriesSamples',
      'link' => '/entriesSamples',
      'permanent' => false,
      'isActive' => false
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/forms',
      'permanent' => false,
      'isActive' => false
    ],
    'blocks' => [
      'name' => 'blocks',
      'iconName' => 'contentBlocks',
      'link' => '/contentBlocks',
      'permanent' => false,
      'isActive' => false
    ]
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  /**
   * Сборка списка локализаций
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
      $itemElement->setAttribute('class', 'grid-table__locale');

      if (!empty($localeData['iconURL'])) {
        $iconElement = $document->createElement('img');
        $iconElement->setAttribute('class', 'grid-table__locale-icon');
        $iconElement->setAttribute('src', $localeData['iconURL']);
        $itemElement->prepend($iconElement);
      }

      $document->appendChild($itemElement);
    }

    return $document->saveHTML();
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/pages.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $pagesStaticTableItemsAssembled = [];
    $pagesStatic = new Pages($this->CMSCore);
    $pagesStaticLocale = $this->CMSCore->getCMSLocale('admin');
    $pagesStaticLocaleName = $this->CMSCore->locale->getName();

    $pagesStaticObjects = $pagesStatic->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $pagesStatic->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($entries);

    foreach ($pagesStaticObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'authorID']);

      $createdDateTimestamp = $object->getCreatedUnixTimestamp();
      $publishedDateTimestamp = $object->getPublishedUnixTimestamp();
      $updatedDateTimestamp = $object->getUpdatedUnixTimestamp();

      $pageStaticTitle = $object->getTitle($pagesStaticLocaleName);
      $pageStaticDescription = $object->getDescription($pagesStaticLocaleName);

      $pageStaticTitle = strip_tags($pageStaticTitle);
      $pageStaticDescription = strip_tags($pageStaticDescription);

      $authorObject = $object->getAuthor();
      if ($authorObject !== null) {
        $authorObject->initData(['login']);
      }

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);
      $SEOStatus = !empty($object->getCompletedSEOTexts())
        ? '<span style="color: green;">Оптимизировано</span>'
        : '<span style="color: red;">Не оптимизировано</span>';

      $authorLogin = $authorObject !== null ? $authorObject->getLogin() : 'User deleted';

      $templatesAssembled = [];
      $templateContent = ThemeCollector::getTemplateFileContent(
        $this->CMSCore->theme,
        'templates/page/pages/tableItem.tpl'
      );

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_INDEX')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_INDEX',
          $index
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_ID')) {
        $value = $object !== null ? $object->getID() : 0;

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_ID',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_NAME')) {
        $value = $object !== null ? $object->getName() : '';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_NAME',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_TITLE')) {
        $value = $object !== null ? $object->getTitle($localeName) : '';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_TITLE',
          str_replace(
            ThemeCollector::DECODED_ENTITIES,
            ThemeCollector::SAFE_SYMBOLS,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
          )
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_DESCRIPTION')) {
        $value = $object !== null ? $object->getDescription($localeName) : '';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_DESCRIPTION',
          str_replace(
            ThemeCollector::DECODED_ENTITIES,
            ThemeCollector::SAFE_SYMBOLS,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
          )
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_URL')) {
        $value = $object->getURL();

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_URL',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_AUTHOR_LOGIN')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_AUTHOR_LOGIN',
          $authorLogin
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_LOCALES_LIST')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_LOCALES_LIST',
          $completedLocales
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_SEO_STATUS')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_SEO_STATUS',
          $SEOStatus
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_CREATED_DATE_TIMESTAMP')) {
        $value = date('d.m.Y H:i:s', $createdDateTimestamp);
        
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_CREATED_DATE_TIMESTAMP',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP')) {
        $value = $publishedDateTimestamp > 0 ? date('d.m.Y H:i:s', $publishedDateTimestamp) : '-';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_UPDATED_DATE_TIMESTAMP')) {
        $value = date('d.m.Y H:i:s', $updatedDateTimestamp);
        
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_UPDATED_DATE_TIMESTAMP',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PUBLISHED_STATUS')) {
        $value = $object->isPublished() ? 'published' : 'not-published';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'PAGE_STATIC_PUBLISHED_STATUS',
          $value
        );
      }

      $tableItemsAssembled[] =  ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/pages/tableItem.tpl',
        $templatesAssembled
      );
    }

    $tableItemsAssembled = $tableItemsAssembled ?? [];

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/pages.tpl',
      [
        'PAGE_PAGES_STATIC_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'page_static',
        'ADMIN_PANEL_PAGES_STATIC_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/pages/table.tpl',
          [
            'ADMIN_PANEL_PAGES_STATIC_TABLE_ITEMS' => implode($tableItemsAssembled)
          ]
        )
      ]
    );
  }
}
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
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;
use \DOMImplementation as DOMImplementation;

/**
 * Страница со списком записей
 */
class PageEntries implements InterfacePage
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
      'isActive' => true
    ],
    'pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'isActive' => false
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
    ]
  ];

  /**
   * __construct
   * 
   * @param SystemCore $CMSCore
   * @param Page $page
   */
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

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    // Добавление таблицы стилей для страницы
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entries.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $entriesTableItemsAssembled = [];

    $entries = new Entries($this->CMSCore);
    $entriesLocale = $this->CMSCore->getCMSLocale('admin');
    $entriesLocaleName = $entriesLocale->getName();
    
    $entriesObjects = $entries->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $entries->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($entries);

    foreach ($entriesObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'categoryID', 'authorID']);

      $entryCategoryID = $object->getCategoryID();
      $entryCategory = new EntryCategory($this->CMSCore, $entryCategoryID);
      $entryCategory->initData(['texts']);

      $createdDateTimestamp = $object->getCreatedUnixTimestamp();
      $publishedDateTimestamp = $object->getPublishedUnixTimestamp();
      $updatedDateTimestamp = $object->getUpdatedUnixTimestamp();

      $entryTitle = $object->getTitle($entriesLocaleName);
      $entryDescription = $object->getDescription($entriesLocaleName);

      $entryTitle = strip_tags($entryTitle);
      $entryDescription = strip_tags($entryDescription);

      $entryAuthor = $object->getAuthor();
      if ($entryAuthor !== null) {
        $entryAuthor->initData(['login']);
      }

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);
      $entrySEOStatus = !empty($object->getCompletedSEOTexts())
        ? '<span style="color: green;">Оптимизировано</span>'
        : '<span style="color: red;">Не оптимизировано</span>';

      $entryAuthorLogin = $entryAuthor !== null ? $entryAuthor->getLogin() : 'User deleted';

      $templatesAssembled = [];
      $templateContent = ThemeCollector::getTemplateFileContent(
        $this->CMSCore->theme,
        'templates/page/entries/tableItem.tpl'
      );

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_INDEX')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_INDEX',
          $index
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_ID')) {
        $value = $object !== null ? $object->getID() : 0;

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_ID',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_NAME')) {
        $value = $object !== null ? $object->getName() : '';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_NAME',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_TITLE')) {
        $value = $object !== null ? $object->getTitle($localeName) : '';

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
        $value = $object !== null ? $object->getDescription($localeName) : '';

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

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_CATEGORY_TITLE')) {
        $value = $entryCategory !== null ? $entryCategory->getTitle($localeName) : '';

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

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_PUBLISHED_STATUS')) {
        $value = $object->isPublished() ? 'published' : 'not-published';

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_PUBLISHED_STATUS',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_URL')) {
        $value = $object->getURL();

        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_URL',
          $value
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_AUTHOR_LOGIN')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_AUTHOR_LOGIN',
          $entryAuthorLogin
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_LOCALES_LIST')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_LOCALES_LIST',
          $completedLocales
        );
      }

      if (ThemeCollector::existsTemplateVariable($templateContent, 'ENTRY_SEO_STATUS')) {
        ThemeCollector::addTemplateVariable(
          $templatesAssembled,
          'ENTRY_SEO_STATUS',
          $entrySEOStatus
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
        $value = $publishedDateTimestamp > 0 ? date('d.m.Y H:i:s', $publishedDateTimestamp) : '-';

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

      $entriesTableItemsAssembled[] =  ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/entries/tableItem.tpl',
        $templatesAssembled
      );
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/entries.tpl',
      [
        'PAGE_ENTRIES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'entries',
        'ADMIN_PANEL_ENTRIES_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/entries/table.tpl',
          [
            'ADMIN_PANEL_ENTRIES_TABLE_ITEMS' => implode($entriesTableItemsAssembled)
          ]
        )
      ]
    );
  }
}
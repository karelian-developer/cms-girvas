<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Feeds as Feeds;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;

class PageFeeds implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_FEEDS_NAVIGATION_%s_LABEL';

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
    'feeds' => [
      'name' => 'feeds',
      'iconName' => 'feeds',
      'link' => '/feeds',
      'permanent' => false,
      'isActive' => true
    ],
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

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/feeds.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $feedsItemsAssembled = [];
    $feeds = new Feeds($this->CMSCore);
    $feedsLocale = $this->CMSCore->getCMSLocale('admin');
    $feedsLocaleName = $feedsLocale->getName();

    $feedsObjects = $feeds->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $feeds->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($feeds);

    foreach ($feedsObjects as $index => $object) {
      $object->initData(['id', 'name', 'typeID', 'texts', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $feedID = $object->getID();
      $feedTitle = $object->getTitle($feedsLocaleName);
      $feedTitle = strip_tags($feedTitle);

      $feedName = $object->getName();
      $feedCategory = $object->getEntriesCategory(['texts']);
      $feedCategoryTitle = $feedCategory->getTitle($localeName);

      $feedSpecificationTitle = FeedBuilder::getTypeTitle($object->getTypeID());
      $indexCurrent = $index + 1;

      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocales = $this->assemblyLocalesItems($completedLocalesData);

      $feedsItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme, 'templates/page/feeds/tableItem.tpl',
        [
          'FEED_ID' => $feedID,
          'FEED_INDEX' => $indexCurrent,
          'FEED_NAME' => $feedName,
          'FEED_TITLE' => $feedTitle,
          'FEED_CATEGORY_TITLE' => $feedCategoryTitle,
          'FEED_SPECIFICATION_TITLE' => $feedSpecificationTitle,
          'FEED_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'FEED_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]
      );
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/feeds.tpl',
      [
        'PAGE_FEEDS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'feeds',
        'PAGE_FEEDS_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/feeds/table.tpl',
          [
            'PAGE_FEEDS_TABLE_ITEMS' => implode($feedsItemsAssembled)
          ]
        )
      ]
    );
  }
}
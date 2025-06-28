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
use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

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
      $object->init_data(['id', 'name', 'type_id', 'texts', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      $feedID = $object->getID();
      $feedTitle = $object->getTitle($feedsLocaleName);
      $feedTitle = strip_tags($feedTitle);

      $feedName = $object->getName();
      $feedTypeTitle = FeedBuilder::getTypeTitle($object->getTypeID());
      $indexCurrent = $index + 1;

      array_push($feedsItemsAssembled, TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/feeds/tableItem.tpl', [
        'WEB_CHANNEL_ID' => $feedID,
        'WEB_CHANNEL_INDEX' => $indexCurrent,
        'WEB_CHANNEL_NAME' => $feedName,
        'WEB_CHANNEL_TITLE' => $feedTitle,
        'WEB_CHANNEL_TYPE_TITLE' => $feedTypeTitle,
        'WEB_CHANNEL_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
        'WEB_CHANNEL_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
      ]));
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/feeds.tpl', [
      'PAGE_WEB_CHANNELS_PAGINATION' => $pagination->assembled,
      'ADMIN_PANEL_PAGE_NAME' => 'web-channels',
      'ADMIN_PANEL_WEB_CHANNELS_TABLE' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/feeds/table.tpl', [
        'ADMIN_PANEL_WEB_CHANNELS_TABLE_ITEMS' => implode($feedsItemsAssembled)
      ])
    ]);
  }
}
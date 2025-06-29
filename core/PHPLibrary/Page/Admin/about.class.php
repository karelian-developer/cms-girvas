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
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Feed\Importer as FeedImporter;

class PageAbout implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/about.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();

    $feedImporter = new FeedImporter($this->CMSCore, 'https://www.cms-girvas.ru/feed/last-news');
    $feedXML = $feedImporter->get([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ]
    ]);
    $feedItemsAssembled = [];

    if (isset($feedXML->channel->item) && $feedXML != false) {
      $countMax = 3; $itemIndex = 0;
      foreach ($feedXML->channel->item as $item) {
        array_push($feedItemsAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/index/feed/listItem.tpl', [
          'ITEM_TITLE' => $item->title,
          'ITEM_DESCRIPTION' => $item->description,
          'ITEM_LINK' => $item->link
        ]));

        if ($itemIndex == $countMax - 1) break;
        $itemIndex++;
      }
    }

    unset($feedImporter);
    unset($feedXML);

    $feedLastNewsList = $localeData['PAGE_INDEX_SIDEBAR_BLOCK_WEB_CHANNEL_ENTRIES_NOT_FOUND_LABEL'];
    if (count($feedItemsAssembled) > 0) {
      $feedLastNewsList = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/index/feed/list.tpl', [
        'WEB_CHANNEL_ITEMS' => implode($feedItemsAssembled)
      ]);
    }

    $feedImporter = new FeedImporter($this->CMSCore, 'https://www.cms-girvas.ru/feed/last-releases');
    $feedXML = $feedImporter->get([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ]
    ]);
    $feedItemsAssembled = [];
    
    if (!is_bool($feedXML)) {
      $countMax = 3; $itemIndex = 0;
      
      foreach ($feedXML->channel as $channel) {
        foreach ($channel->item as $item) {
          array_push($feedItemsAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/index/feed/listItem.tpl', [
            'ITEM_TITLE' => $item->title,
            'ITEM_DESCRIPTION' => $item->description,
            'ITEM_LINK' => $item->link
          ]));

          if ($itemIndex == $countMax - 1) break;
          $itemIndex++;
        }
      }
    }

    unset($feedImporter);
    unset($feedXML);

    $feedLastReleasesList = $localeData['PAGE_INDEX_SIDEBAR_BLOCK_WEB_CHANNEL_ENTRIES_NOT_FOUND_LABEL'];
    if (count($feedItemsAssembled) > 0) {
      $feedLastReleasesList = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/index/feed/list.tpl', [
        'WEB_CHANNEL_ITEMS' => implode($feedItemsAssembled)
      ]);
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/about.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'about',
      'WEB_CHANNEL_LATEST_NEWS_LIST' => $feedLastNewsList,
      'WEB_CHANNEL_LATEST_RELEASES_LIST' => $feedLastReleasesList,
    ]);
  }
}
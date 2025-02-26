<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Feed\Importer as FeedImporter;

  class PageAbout implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/about.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $feed_importer = new FeedImporter($this->system_core, 'https://www.cms-girvas.ru/feed/last-news');
      $feed_xml = $feed_importer->get([
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false
        ]
      ]);
      $feed_items_assembled = [];

      if (isset($feed_xml->channel->item) && $feed_xml != false) {
        $count_max = 3; $item_index = 0;
        foreach ($feed_xml->channel->item as $item) {
          array_push($feed_items_assembled, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/feed/listItem.tpl', [
            'ITEM_TITLE' => $item->title,
            'ITEM_DESCRIPTION' => $item->description,
            'ITEM_LINK' => $item->link
          ]));

          if ($item_index == $count_max - 1) break;
          $item_index++;
        }
      }

      unset($feed_importer);
      unset($feed_xml);

      $feed_last_news_list = $locale_data['PAGE_INDEX_SIDEBAR_BLOCK_WEB_CHANNEL_ENTRIES_NOT_FOUND_LABEL'];
      if (count($feed_items_assembled) > 0) {
        $feed_last_news_list = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/feed/list.tpl', [
          'WEB_CHANNEL_ITEMS' => implode($feed_items_assembled)
        ]);
      }

      $feed_importer = new FeedImporter($this->system_core, 'https://www.cms-girvas.ru/feed/last-releases');
      $feed_xml = $feed_importer->get([
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false
        ]
      ]);
      $feed_items_assembled = [];
      
      if (!is_bool($feed_xml)) {
        $count_max = 3; $item_index = 0;
        
        foreach ($feed_xml->channel as $channel) {
          foreach ($channel->item as $item) {
            array_push($feed_items_assembled, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/feed/listItem.tpl', [
              'ITEM_TITLE' => $item->title,
              'ITEM_DESCRIPTION' => $item->description,
              'ITEM_LINK' => $item->link
            ]));

            if ($item_index == $count_max - 1) break;
            $item_index++;
          }
        }
      }

      unset($feed_importer);
      unset($feed_xml);

      $feed_last_releases_list = $locale_data['PAGE_INDEX_SIDEBAR_BLOCK_WEB_CHANNEL_ENTRIES_NOT_FOUND_LABEL'];
      if (count($feed_items_assembled) > 0) {
        $feed_last_releases_list = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/index/feed/list.tpl', [
          'WEB_CHANNEL_ITEMS' => implode($feed_items_assembled)
        ]);
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/about.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'about',
        'WEB_CHANNEL_LATEST_NEWS_LIST' => $feed_last_news_list,
        'WEB_CHANNEL_LATEST_RELEASES_LIST' => $feed_last_releases_list,
      ]);
    }
  }
}

?>
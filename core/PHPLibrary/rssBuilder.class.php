<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \DOMDocument as DOMDocument;

  final class WebChannelBuilder {
    private DOMDocument $document;
    private SystemCore $system_core;
    private array $items = [];
    private string $language = 'en-us';
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;
      $this->document = new DOMDocument('1.0');
    }
    
    /**
     * Назначит язык RSS-ленты
     *
     * @param  mixed $locale_name
     * @return void
     */
    public function set_language(string $locale_name) : void {
      $this->language = str_replace('_', '-', strtolower($locale_name));
    }
    
    /**
     * Получить язык RSS-ленты
     *
     * @return string
     */
    public function get_language() : string {
      return $this->language;
    }
    
    /**
     * Добавить запись в ленту
     *
     * @param  int $id
     * @param  string $title
     * @param  string $description
     * @param  string $link
     * @param  string $image_link
     * @return void
     */
    public function add_item(string $title, string $description, string $link, int $updated_unix_timestamp) : void {
      array_push($this->items, [
        'title' => $title,
        'description' => $description,
        'link' => $link,
        'pubdate' => date('D, d M Y H:i:s T', $updated_unix_timestamp)
      ]);
    }
    
    /**
     * Сборка XML-структуры RSS-канала
     *
     * @return void
     */
    public function assembly() : void {
      $site_title = ($this->system_core->configurator->exists_database_entry_value('base_site_title')) ? $this->system_core->configurator->get_database_entry_value('base_site_title') : sprintf('%s %s', $this->system_core::CMS_TITLE, $this->system_core::CMS_VERSION);
      $site_description = ($this->system_core->configurator->exists_database_entry_value('seo_site_description')) ? $this->system_core->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $site_link = sprintf('https://%s', $this->system_core->configurator->get('domain'));

      $element_rss = $this->document->createElement('rss');
      $element_rss_attribute_version = $this->document->createAttribute('version');
      $element_rss_attribute_version->value = '2.0';

      $element_rss->appendChild($element_rss_attribute_version);

      $element_channel = $this->document->createElement('channel');
      $element_channel_title = $this->document->createElement('title', $site_title);
      $element_channel_link = $this->document->createElement('link', $site_link);
      $element_channel_description = $this->document->createElement('description', $site_description);
      $element_channel_language = $this->document->createElement('language', $this->get_language());
      $element_channel_lastbuilddate = $this->document->createElement('lastBuildDate', date('D, d M Y H:i:s T', time()));
      $element_channel_docs = $this->document->createElement('docs', 'http://blogs.law.harvard.edu/tech/rss');
      $element_channel_generator = $this->document->createElement('generator', 'CMS GIRVAS: RSS Builder');

      $element_channel->appendChild($element_channel_title);
      $element_channel->appendChild($element_channel_link);
      $element_channel->appendChild($element_channel_description);
      $element_channel->appendChild($element_channel_lastbuilddate);
      $element_channel->appendChild($element_channel_docs);
      $element_channel->appendChild($element_channel_generator);

      $items = $this->items;
      usort($items, function ($a, $b) {
        $a_pubdate_unix = strtotime($a['pubdate']);
        $b_pubdate_unix = strtotime($b['pubdate']);

        if ($a_pubdate_unix == $b_pubdate_unix) {
          return 0;
        }

        return ($a_pubdate_unix > $b_pubdate_unix) ? -1 : 1;
      });

      $element_channel_pubdate = $this->document->createElement('pubDate', $items[0]['pubdate']);
      $element_channel->appendChild($element_channel_pubdate);

      unset($items);

      foreach ($this->items as $item) {
        $element_item_description_cdata = $this->document->createCDATASection($item['description']);

        $element_item_title = $this->document->createElement('title', $item['title']);
        $element_item_description = $this->document->createElement('description');
        $element_item_link = $this->document->createElement('link', $item['link']);
        $element_item_pubdate = $this->document->createElement('pubDate', $item['pubdate']);
        $element_item_description->appendChild($element_item_description_cdata);

        $element_item = $this->document->createElement('item');
        $element_item->appendChild($element_item_title);
        $element_item->appendChild($element_item_description);
        $element_item->appendChild($element_item_link);
        $element_item->appendChild($element_item_pubdate);
        $element_channel->appendChild($element_item);
      }

      $element_rss->appendChild($element_channel);
      $this->document->appendChild($element_rss);

      $this->assembled = $this->document->saveXML();
    }
  }
}
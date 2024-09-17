<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\WebChannel\Specification {
  use \DOMElement as DOMElement;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\WebChannel\Builder as WebChannelBuilder;
  use \core\PHPLibrary\WebChannel\InterfaceSpecification as InterfaceSpecification;

  class YandexTurbo implements InterfaceSpecification {
    const TYPE_NAME = 'yandex-turbo';
    const TYPE_TITLE = 'Yandex Turbo';

    private SystemCore $system_core;
    private WebChannelBuilder $builder;
    public string $title;
    public string $description;
    public string $link;
    public string $language;
    public array $items = [];

    public function __construct(SystemCore $system_core, WebChannelBuilder $web_channel_builder) {
      $this->system_core = $system_core;
      $this->builder = $web_channel_builder;
    }

    public function set_title(string $value) : void {
      $this->title = $value;
    }

    public function set_description(string $value) : void {
      $this->description = $value;
    }

    public function set_link(string $value) : void {
      $this->link = $value;
    }

    public function set_language(string $value) : void {
      $this->language = str_replace('_', '-', strtolower($value));
    }

    public function add_item(array $data) : void {
      array_push($this->items, [
        'title' => $data['title'],
        'description' => $data['description'],
        'link' => $data['link'],
        'pubdate' => date('D, d M Y H:i:s T', $data['pubdate'])
      ]);
    }

    public function get_title() : string {
      return $this->title;
    }

    public function get_description() : string {
      return $this->description;
    }

    public function get_link() : string {
      return $this->link;
    }

    public function get_language() : string {
      return $this->language;
    }

    public function get_items() : array {
      return $this->items;
    }

    public function assembly_rss() : DOMElement|bool {
      $element_rss = $this->builder->document->createElement('rss');

      $element_rss_attribute_version = $this->builder->document->createAttribute('version');
      $element_rss_attribute_version->value = '2.0';

      $element_rss_attribute_turbo = $this->builder->document->createAttributeNS('http://turbo.yandex.ru', 'turbo:attr');
      $element_rss_attribute_yandex = $this->builder->document->createAttributeNS('http://news.yandex.ru', 'yandex:attr');

      $element_rss->appendChild($element_rss_attribute_yandex);
      $element_rss->appendChild($element_rss_attribute_turbo);
      $element_rss->appendChild($element_rss_attribute_version);

      return $element_rss;
    }

    public function assembly_channel() : DOMElement|bool {
      $site_title = ($this->system_core->configurator->exists_database_entry_value('base_site_title')) ? $this->system_core->configurator->get_database_entry_value('base_site_title') : sprintf('%s %s', $this->system_core::CMS_TITLE, $this->system_core::CMS_VERSION);
      $site_description = ($this->system_core->configurator->exists_database_entry_value('seo_site_description')) ? $this->system_core->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $site_link = sprintf('https://%s', $this->system_core->configurator->get('domain'));

      $channel_title = (!empty($this->get_title())) ? $this->get_title() : $site_title;
      $channel_description = (!empty($this->get_description())) ? $this->get_description() : $site_description;
      $channel_link = (!empty($this->get_link())) ? $this->get_link() : $site_link;

      $element_channel = $this->builder->document->createElement('channel');
      $element_channel_title = $this->builder->document->createElement('title', $channel_title);
      $element_channel_link = $this->builder->document->createElement('link', $channel_link);
      $element_channel_description = $this->builder->document->createElement('description', $channel_description);
      $element_channel_language = $this->builder->document->createElement('language', $this->builder->get_language());
      $element_channel_lastbuilddate = $this->builder->document->createElement('lastBuildDate', date('D, d M Y H:i:s T', time()));
      $element_channel_docs = $this->builder->document->createElement('docs', 'http://blogs.law.harvard.edu/tech/rss');
      $element_channel_generator = $this->builder->document->createElement('generator', 'CMS GIRVAS: Web Channel Builder');

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

      $element_channel_pubdate = $this->builder->document->createElement('pubDate', $items[0]['pubdate']);
      $element_channel->appendChild($element_channel_pubdate);

      unset($items);

      foreach ($this->items as $item) {
        $element_item_description_cdata = $this->builder->document->createCDATASection($item['description']);

        $element_item_title = $this->builder->document->createElement('title', $item['title']);
        $element_item_content = $this->builder->document->createElementNS('http://search.yahoo.com/mrss/', 'turbo:content', '');
        $element_item_link = $this->builder->document->createElement('link', $item['link']);
        $element_item_pubdate = $this->builder->document->createElement('pubDate', $item['pubdate']);
        $element_item_content->appendChild($element_item_description_cdata);

        $element_item = $this->builder->document->createElement('item');
        $element_item->setAttribute('turbo', 'true');
        $element_item->appendChild($element_item_title);
        $element_item->appendChild($element_item_content);
        $element_item->appendChild($element_item_link);
        $element_item->appendChild($element_item_pubdate);
        $element_channel->appendChild($element_item);
      }

      return $element_channel;
    }

    public function assembly() : void {
      $element_rss = $this->assembly_rss();
      $element_channel = $this->assembly_channel();

      $element_rss->appendChild($element_channel);
      $this->builder->document->appendChild($element_rss);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
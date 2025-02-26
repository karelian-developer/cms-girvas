<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed\Specification {
  use \DOMElement as DOMElement;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Feed\Builder as FeedBuilder;
  use \core\PHPLibrary\Feed\InterfaceSpecification as InterfaceSpecification;

  class Atom implements InterfaceSpecification {
    const TYPE_NAME = 'atom';
    const TYPE_TITLE = 'Atom';

    private SystemCore $system_core;
    private FeedBuilder $builder;
    public string $title = '';
    public string $description = '';
    public string $link = '';
    public string $language;
    public array $items = [];

    public function __construct(SystemCore $system_core, FeedBuilder $web_channel_builder) {
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
        'pubdate' => date('D, d M Y H:i:s O', $data['pubdate'])
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

    public function assembly_feed() : DOMElement|bool {
      $site_title = ($this->system_core->configurator->exists_database_entry_value('base_site_title')) ? $this->system_core->configurator->get_database_entry_value('base_site_title') : sprintf('%s %s', $this->system_core::CMS_TITLE, $this->system_core::CMS_VERSION);
      $site_description = ($this->system_core->configurator->exists_database_entry_value('seo_site_description')) ? $this->system_core->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $site_link = sprintf('https://%s', $this->system_core->configurator->get('domain'));

      $channel_title = (!empty($this->get_title())) ? $this->get_title() : $site_title;
      $channel_description = (!empty($this->get_description())) ? $this->get_description() : $site_description;
      $channel_link = (!empty($this->get_link())) ? $this->get_link() : $site_link;

      $element_feed = $this->builder->document->createElement('feed');
      $element_feed_attribute_xmlns = $this->builder->document->createAttribute('xmlns');
      $element_feed_attribute_xmlns->value = 'http://www.w3.org/2005/Atom';

      $element_feed->appendChild($element_feed_attribute_xmlns);

      $element_feed_title = $this->builder->document->createElement('title', $channel_title);
      $element_feed_link = $this->builder->document->createElement('link');
      $element_feed_link_attribute_href = $this->builder->document->createAttribute('href');
      $element_feed_link_attribute_href->value = $channel_link;
      $element_feed_generator = $this->builder->document->createElement('generator', 'CMS GIRVAS: Web Channel Builder');
      $element_feed_rights = $this->builder->document->createElement('rights', 'Copyright (c) 2023, www.garbalo.com');

      $element_feed_link->appendChild($element_feed_link_attribute_href);
      $element_feed->appendChild($element_feed_title);
      $element_feed->appendChild($element_feed_link);
      $element_feed->appendChild($element_feed_generator);
      $element_feed->appendChild($element_feed_rights);

      $items = $this->items;
      usort($items, function ($a, $b) {
        $a_pubdate_unix = strtotime($a['pubdate']);
        $b_pubdate_unix = strtotime($b['pubdate']);

        if ($a_pubdate_unix == $b_pubdate_unix) {
          return 0;
        }

        return ($a_pubdate_unix > $b_pubdate_unix) ? -1 : 1;
      });

      $element_feed_updated = $this->builder->document->createElement('updated', $items[0]['pubdate']);
      $element_feed->appendChild($element_feed_updated);

      unset($items);

      foreach ($this->items as $item) {
        $element_entry = $this->builder->document->createElement('entry');
        $element_entry_title = $this->builder->document->createElement('title', $item['title']);
        $element_entry_summary = $this->builder->document->createElement('summary', $item['description']);
        $element_entry_link = $this->builder->document->createElement('link');
        $element_entry_link_attribute_href = $this->builder->document->createAttribute('href');
        $element_entry_link_attribute_href->value = $item['link'];
        $element_entry_updated = $this->builder->document->createElement('updated', $item['pubdate']);

        $element_entry->appendChild($element_entry_title);
        $element_entry_link->appendChild($element_entry_link_attribute_href);
        $element_entry->appendChild($element_entry_link);
        $element_entry->appendChild($element_entry_summary);

        $element_feed->appendChild($element_entry);
      }

      return $element_feed;
    }

    public function assembly() : void {
      $element_feed = $this->assembly_feed();

      $this->builder->document->appendChild($element_feed);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
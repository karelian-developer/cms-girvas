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

  class RSS1_0 implements InterfaceSpecification {
    const TYPE_NAME = 'rss1-0';
    const TYPE_TITLE = 'RSS 1.0';

    private SystemCore $system_core;
    private FeedBuilder $builder;
    public string $title;
    public string $description;
    public string $link;
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

    public function assembly_rdf() : DOMElement|bool {
      $element_rdf = $this->builder->document->createElement('rdf:RDF');
      $element_rdf_attribute_xmlns_rdf = $this->builder->document->createAttribute('xmlns:rdf');
      $element_rdf_attribute_xmlns = $this->builder->document->createAttribute('xmlns');
      $element_rdf_attribute_xmlns_rdf->value = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
      $element_rdf_attribute_xmlns->value = 'http://purl.org/rss/1.0/';

      $element_rdf->appendChild($element_rdf_attribute_xmlns_rdf);
      $element_rdf->appendChild($element_rdf_attribute_xmlns);
      return $element_rdf;
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

      $element_channel->appendChild($element_channel_title);
      $element_channel->appendChild($element_channel_link);
      $element_channel->appendChild($element_channel_description);

      $element_items = $this->builder->document->createElement('items');
      $element_items_rdf_seq = $this->builder->document->createElement('rdf:Seq');
      $element_items->appendChild($element_items_rdf_seq);

      foreach ($this->items as $item) {
        $element_items_rdf_li = $this->builder->document->createElement('rdf:li');
        $element_items_rdf_li_attribute_resource = $this->builder->document->createAttribute('resource');
        $element_items_rdf_li_attribute_resource->value = $item['link'];

        $element_items_rdf_li->appendChild($element_items_rdf_li_attribute_resource);
        $element_items_rdf_seq->appendChild($element_items_rdf_li);
      }

      $element_channel->appendChild($element_items);

      foreach ($this->items as $item) {
        $element_item = $this->builder->document->createElement('item');
        $element_item_attribute_rdf_about = $this->builder->document->createAttribute('rdf:about');
        $element_item_attribute_rdf_about->value = $item['link'];
        $element_item->appendChild($element_item_attribute_rdf_about);

        $element_item_title = $this->builder->document->createElement('title', $item['title']);
        $element_item_description = $this->builder->document->createElement('description', $item['description']);
        $element_item_link = $this->builder->document->createElement('link', $item['link']);

        $element_item->appendChild($element_item_title);
        $element_item->appendChild($element_item_description);
        $element_item->appendChild($element_item_link);

        $element_channel->appendChild($element_item);
      }
      
      return $element_channel;
    }

    public function assembly() : void {
      $element_rdf = $this->assembly_rdf();
      $element_channel = $this->assembly_channel();

      $element_rdf->appendChild($element_channel);
      $this->builder->document->appendChild($element_rdf);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
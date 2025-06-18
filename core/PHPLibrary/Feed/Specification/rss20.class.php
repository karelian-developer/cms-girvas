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

  class RSS2_0 implements InterfaceSpecification {
    const TYPE_NAME = 'rss2-0';
    const TYPE_TITLE = 'RSS 2.0';

    private SystemCore $CMSCore;
    private FeedBuilder $builder;
    public string $title;
    public string $description;
    public string $link;
    public string $language;
    public array $items = [];

    public function __construct(SystemCore $CMSCore, FeedBuilder $feedBuilder) {
      $this->CMSCore = $CMSCore;
      $this->builder = $feedBuilder;
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

    public function assembly_rss() : DOMElement|bool {
      $RSSElement = $this->builder->document->createElement('rss');
      $RSSElementAttributeVersion = $this->builder->document->createAttribute('version');
      $RSSElementAttributeVersion->value = '2.0';

      $RSSElement->appendChild($RSSElementAttributeVersion);
      return $RSSElement;
    }

    public function assembly_channel() : DOMElement|bool {
      $siteTitle = $this->CMSCore->configurator->exists_database_entry_value('base_site_title') ? $this->CMSCore->configurator->get_database_entry_value('base_site_title') : sprintf('%s %s', $this->CMSCore::CMS_TITLE, $this->CMSCore::CMS_VERSION);
      $siteDescription = $this->CMSCore->configurator->exists_database_entry_value('seo_site_description') ? $this->CMSCore->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $siteLink = 'https://%s' . $this->CMSCore->configurator->get('domain');

      $channelTitle = !empty($this->get_title()) ? $this->get_title() : $siteTitle;
      $channelDescription = !empty($this->get_description()) ? $this->get_description() : $siteDescription;
      $channelLink = !empty($this->get_link()) ? $this->get_link() : $siteLink;

      $channelElement = $this->builder->document->createElement('channel');
      $channelTitleElement = $this->builder->document->createElement('title', $channelTitle);
      $channelLinkElement = $this->builder->document->createElement('link', $channelLink);
      $channelDescriptionElement = $this->builder->document->createElement('description', $channelDescription);
      $channelLanguageElement = $this->builder->document->createElement('language', $this->builder->get_language());
      $channelLastbuilddateElement = $this->builder->document->createElement('lastBuildDate', date('D, d M Y H:i:s T', time()));
      $channelDocsElement = $this->builder->document->createElement('docs', 'http://blogs.law.harvard.edu/tech/rss');
      $channelGeneratorElement = $this->builder->document->createElement('generator', 'CMS GIRVAS: Web Channel Builder');

      $channelElement->appendChild($channelTitleElement);
      $channelElement->appendChild($channelLinkElement);
      $channelElement->appendChild($channelDescriptionElement);
      $channelElement->appendChild($channelLastbuilddateElement);
      $channelElement->appendChild($channelDocsElement);
      $channelElement->appendChild($channelGeneratorElement);

      $items = $this->items;
      usort($items, function ($a, $b) {
        $aPubdateUnix = strtotime($a['pubdate']);
        $bPubdateUnix = strtotime($b['pubdate']);

        if ($aPubdateUnix == $bPubdateUnix) {
          return 0;
        }

        return $aPubdateUnix > $bPubdateUnix ? -1 : 1;
      });

      $channelPubdateElement = $this->builder->document->createElement('pubDate', $items[0]['pubdate']);
      $channelElement->appendChild($channelPubdateElement);

      unset($items);

      foreach ($this->items as $item) {
        $itemDescriptionCDATASection = $this->builder->document->createCDATASection($item['description']);

        $itemTitleElement = $this->builder->document->createElement('title', $item['title']);
        $itemDescriptionElement = $this->builder->document->createElement('description');
        $itemLinkElement = $this->builder->document->createElement('link', $item['link']);
        $itemPudateElement = $this->builder->document->createElement('pubDate', $item['pubdate']);
        $itemDescriptionElement->appendChild($itemDescriptionCDATASection);

        $itemElement = $this->builder->document->createElement('item');
        $itemElement->appendChild($itemTitleElement);
        $itemElement->appendChild($itemDescriptionElement);
        $itemElement->appendChild($itemLinkElement);
        $itemElement->appendChild($itemPudateElement);
        $channelElement->appendChild($itemElement);
      }

      return $channelElement;
    }

    public function assembly() : void {
      $RSSElement = $this->assembly_rss();
      $channelElement = $this->assembly_channel();

      $RSSElement->appendChild($channelElement);
      $this->builder->document->appendChild($RSSElement);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
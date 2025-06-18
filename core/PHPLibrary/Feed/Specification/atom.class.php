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

    private SystemCore $CMSCore;
    private FeedBuilder $builder;
    public string $title = '';
    public string $description = '';
    public string $link = '';
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

    public function assembly_feed() : DOMElement|bool {
      $siteTitle = $this->CMSCore->configurator->exists_database_entry_value('base_site_title') ? $this->CMSCore->configurator->get_database_entry_value('base_site_title') : $this->CMSCore::CMS_TITLE . ' ' . $this->CMSCore::CMS_VERSION;
      $siteDescription = $this->CMSCore->configurator->exists_database_entry_value('seo_site_description') ? $this->CMSCore->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $siteLink = 'https://' . $this->CMSCore->configurator->get('domain');

      $channelTitle = !empty($this->get_title()) ? $this->get_title() : $siteTitle;
      $channelDescription = !empty($this->get_description()) ? $this->get_description() : $siteDescription;
      $channelLink = !empty($this->get_link()) ? $this->get_link() : $siteLink;

      $feedElement = $this->builder->document->createElement('feed');
      $feedElementAttributeXMLns = $this->builder->document->createAttribute('xmlns');
      $feedElementAttributeXMLns->value = 'http://www.w3.org/2005/Atom';

      $feedElement->appendChild($feedElementAttributeXMLns);

      $feedTitleElement = $this->builder->document->createElement('title', $channelTitle);
      $feedLinkElement = $this->builder->document->createElement('link');
      $feedLinkElementAttributeHref = $this->builder->document->createAttribute('href');
      $feedLinkElementAttributeHref->value = $channelLink;
      $feedGeneratorElement = $this->builder->document->createElement('generator', 'CMS GIRVAS: Web Channel Builder');
      $feedRightsElement = $this->builder->document->createElement('rights', 'Copyright (c) 2025, www.garbalo.com');

      $feedLinkElement->appendChild($feedLinkElementAttributeHref);
      $feedElement->appendChild($feedTitleElement);
      $feedElement->appendChild($feedLinkElement);
      $feedElement->appendChild($feedGeneratorElement);
      $feedElement->appendChild($feedRightsElement);

      $items = $this->items;
      usort($items, function ($a, $b) {
        $aPubdateUnix = strtotime($a['pubdate']);
        $bPubdateUnix = strtotime($b['pubdate']);

        if ($aPubdateUnix == $bPubdateUnix) {
          return 0;
        }

        return ($aPubdateUnix > $bPubdateUnix) ? -1 : 1;
      });

      $feedUpdatedElement = $this->builder->document->createElement('updated', $items[0]['pubdate']);
      $feedElement->appendChild($feedUpdatedElement);

      unset($items);

      foreach ($this->items as $item) {
        $entryElement = $this->builder->document->createElement('entry');
        $entryTitleElement = $this->builder->document->createElement('title', $item['title']);
        $entrySummaryElement = $this->builder->document->createElement('summary', $item['description']);
        $entryLinkElement = $this->builder->document->createElement('link');
        $entryLinkElementAttributeHref = $this->builder->document->createAttribute('href');
        $entryLinkElementAttributeHref->value = $item['link'];
        $entryUpdatedElement = $this->builder->document->createElement('updated', $item['pubdate']);

        $entryElement->appendChild($entryTitleElement);
        $entryLinkElement->appendChild($entryLinkElementAttributeHref);
        $entryElement->appendChild($entryLinkElement);
        $entryElement->appendChild($entrySummaryElement);

        $feedElement->appendChild($entryElement);
      }

      return $feedElement;
    }

    public function assembly() : void {
      $feedElement = $this->assembly_feed();

      $this->builder->document->appendChild($feedElement);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed\Specification;

use \DOMElement as DOMElement;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;
use \core\PHPLibrary\Feed\InterfaceSpecification as InterfaceSpecification;

class RSS1_0 implements InterfaceSpecification
{
  const TYPE_NAME = 'rss1-0';
  const TYPE_TITLE = 'RSS 1.0';

  private SystemCore $CMSCore;
  private FeedBuilder $builder;
  public string $title;
  public string $description;
  public string $link;
  public string $language;
  public array $items = [];

  public function __construct(SystemCore $CMSCore, FeedBuilder $feedBuilder)
  {
    $this->CMSCore = $CMSCore;
    $this->builder = $feedBuilder;
  }

  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }

  public function setLink(string $value) : void
  {
    $this->link = $value;
  }

  public function setLanguage(string $value) : void
  {
    $this->language = str_replace('_', '-', strtolower($value));
  }

  public function addItem(array $data) : void
  {
    array_push($this->items, [
      'title' => $data['title'],
      'description' => $data['description'],
      'link' => $data['link'],
      'pubdate' => date('D, d M Y H:i:s O', $data['pubdate'])
    ]);
  }

  public function getTitle() : string
  {
    return $this->title;
  }

  public function getDescription() : string
  {
    return $this->description;
  }

  public function getLink() : string
  {
    return $this->link;
  }

  public function getLanguage() : string
  {
    return $this->language;
  }

  public function getItems() : array
  {
    return $this->items;
  }

  public function assemblyDTD() : ?DOMDocumentType
  {
    return '<!DOCTYPE rdf:RDF [
      <!ENTITY laquo "«">
      <!ENTITY raquo "»">
      <!ENTITY nbsp "&#160;">
    ]>';
  }

  public function assemblyRDF() : DOMElement|bool
  {
    $RDFElement = $this->builder->document->createElement('rdf:RDF');
    $RDFElementAttributeXMLnsRDF = $this->builder->document->createAttribute('xmlns:rdf');
    $RDFElementAttributeXMLns = $this->builder->document->createAttribute('xmlns');
    $RDFElementAttributeXMLnsRDF->value = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    $RDFElementAttributeXMLns->value = 'http://purl.org/rss/1.0/';

    $RDFElement->appendChild($RDFElementAttributeXMLnsRDF);
    $RDFElement->appendChild($RDFElementAttributeXMLns);
    return $RDFElement;
  }

  public function assemblyChannel() : DOMElement|bool
  {
    $siteTitle = $this->CMSCore->configurator->existsDatabaseEntryValue('base_site_title') ? $this->CMSCore->configurator->getDatabaseEntryValue('base_site_title') : sprintf('%s %s', $this->CMSCore::CMS_TITLE, $this->CMSCore::CMS_VERSION);
    $siteDescription = $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_description') ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_site_description') : 'Description is not exists';
    $siteLink = 'https://' . $this->CMSCore->configurator->get('domain');

    $channelTitle = !empty($this->getTitle()) ? $this->getTitle() : $siteTitle;
    $channelDescription = !empty($this->getDescription()) ? $this->getDescription() : $siteDescription;
    $channelLink = !empty($this->getLink()) ? $this->getLink() : $siteLink;

    $channelElement = $this->builder->document->createElement('channel');
    $channelTitleElement = $this->builder->document->createElement('title', $channelTitle);
    $channelLinkElement = $this->builder->document->createElement('link', $channelLink);
    $channelDescriptionElement = $this->builder->document->createElement('description', $channelDescription);

    $channelElement->appendChild($channelTitleElement);
    $channelElement->appendChild($channelLinkElement);
    $channelElement->appendChild($channelDescriptionElement);

    $itemsElement = $this->builder->document->createElement('items');
    $itemsElementRDFSeq = $this->builder->document->createElement('rdf:Seq');
    $itemsElement->appendChild($itemsElementRDFSeq);

    foreach ($this->items as $item) {
      $itemsElementRDFLi = $this->builder->document->createElement('rdf:li');
      $itemsElementRDFLiAttributeResource = $this->builder->document->createAttribute('resource');
      $itemsElementRDFLiAttributeResource->value = $item['link'];

      $itemsElementRDFLi->appendChild($itemsElementRDFLiAttributeResource);
      $itemsElementRDFSeq->appendChild($itemsElementRDFLi);
    }

    $channelElement->appendChild($itemsElement);

    foreach ($this->items as $item) {
      $itemElement = $this->builder->document->createElement('item');
      $itemElementAttributeRDFAbout = $this->builder->document->createAttribute('rdf:about');
      $itemElementAttributeRDFAbout->value = $item['link'];
      $itemElement->appendChild($itemElementAttributeRDFAbout);

      $itemTitleElement = $this->builder->document->createElement('title', $item['title']);
      $itemDescriptionElement = $this->builder->document->createElement('description', $item['description']);
      $itemLinkElement = $this->builder->document->createElement('link', $item['link']);

      $itemElement->appendChild($itemTitleElement);
      $itemElement->appendChild($itemDescriptionElement);
      $itemElement->appendChild($itemLinkElement);

      $channelElement->appendChild($itemElement);
    }
    
    return $channelElement;
  }

  public function assembly() : void
  {
    $DTDElement = $this->assemblyDTD();
    $RDFElement = $this->assemblyRDF();
    $channelElement = $this->assemblyChannel();
    $RDFElement->appendChild($channelElement);

    $this->builder->document->appendChild($RDFElement);
    $this->builder->assembled = $DTDElement . $this->builder->document->saveXML();
  }
}
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed;

use \DOMDocument as DOMDocument;
use \SimpleXMLElement as SimpleXMLElement;
use \XMLReader as XMLReader;
use \core\PHPLibrary\SystemCore as SystemCore;

final class Importer
{
  public string $feedLink = '';

  public function __construct(SystemCore $CMSCore, string $feedLink)
  {
    $this->setFeedLink($feedLink);
  }

  public function get(array $streamContext = []) : SimpleXMLElement|bool
  {
    $feedLink = $this->getFeedLink();

    $assertion = file_get_contents($feedLink, false, stream_context_create($streamContext));
    return simplexml_load_string($assertion);
  }

  private function setFeedLink(string $link) : void
  {
    $this->feedLink = $link;
  }

  public function getFeedLink() : string
  {
    return $this->feedLink;
  }
}
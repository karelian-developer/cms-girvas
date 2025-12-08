<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
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
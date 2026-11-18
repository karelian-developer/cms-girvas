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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
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

  // Таймауты в секундах для обеспечения отказоустойчивости системы
  private const CONNECTION_TIMEOUT = 5;
  private const EXECUTION_TIMEOUT = 10;

  public function __construct(SystemCore $CMSCore, string $feedLink)
  {
    $this->setFeedLink($feedLink);
  }

  /**
   * Получает и парсит XML-ленту
   * 
   * @param array $streamContext Игнорируется в новой реализации для безопасности
   * @return SimpleXMLElement|bool
   */
  public function get(array $streamContext = []) : SimpleXMLElement|bool
  {
    $feedLink = $this->getFeedLink();
    
    if (empty($feedLink)) {
      return false;
    }

    $ch = curl_init();
    
    curl_setopt_array($ch, [
      CURLOPT_URL            => $feedLink,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_CONNECTTIMEOUT => self::CONNECTION_TIMEOUT,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_USERAGENT      => 'GIRVAS-CMS-Feed-Importer/1.0',
      CURLOPT_HTTPHEADER     => [
        'Accept: application/xml, text/xml',
      ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
      return false;
    }

    return simplexml_load_string($response);
  }

  private function setFeedLink(string $link) : void
  {
    /if (filter_var($link, FILTER_VALIDATE_URL)) {
      $this->feedLink = $link;
    } else {
      $this->feedLink = '';
    }
  }

  public function getFeedLink() : string
  {
    return $this->feedLink;
  }
}
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

  // Таймауты в секундах для обеспечения отказоустойчивости
  private const CONNECTION_TIMEOUT = 5;
  private const EXECUTION_TIMEOUT = 10;

  public function __construct(SystemCore $CMSCore, string $feedLink)
  {
    $this->setFeedLink($feedLink);
  }

  /**
   * Получает и парсит XML-ленту
   * 
   * @param array $streamContext Игнорируется в новой реализации для безопасности, оставлен для обратной совместимости сигнатуры
   * @return SimpleXMLElement|bool
   */
  public function get(array $streamContext = []) : SimpleXMLElement|bool
  {
    $feedLink = $this->getFeedLink();
    
    if (empty($feedLink)) {
      return false;
    }

    $ch = curl_init();
    
    // Настройки cURL для безопасного и быстрого запроса
    curl_setopt_array($ch, [
      CURLOPT_URL            => $feedLink,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => false, // Запрет редиректов для безопасности
      CURLOPT_TIMEOUT        => self::EXECUTION_TIMEOUT,
      CURLOPT_CONNECTTIMEOUT => self::CONNECTION_TIMEOUT,
      CURLOPT_SSL_VERIFYPEER => true,  // Обязательная проверка сертификата
      CURLOPT_USERAGENT      => 'GIRVAS-CMS-Feed-Importer/1.0',
      CURLOPT_HTTPHEADER     => [
        'Accept: application/xml, text/xml',
      ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    // Проверка успешности выполнения запроса
    if ($response === false || $httpCode !== 200) {
      error_log("GIRVAS Feed Importer Error: Failed to fetch {$feedLink}. HTTP Code: {$httpCode}. cURL Error: {$error}");
      return false;
    }

    return simplexml_load_string($response);
  }

  private function setFeedLink(string $link) : void
  {
    $this->feedLink = $filter_var($link, FILTER_VALIDATE_URL) ? $link : '';
  }

  public function getFeedLink() : string
  {
    return $this->feedLink;
  }
}
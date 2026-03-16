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

namespace core\PHPLibrary\SystemCore\Notifier;

use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore\Notifier as CMSNotifier;

class Max extends CMSNotifier
{
  private string $token = '';
  private int $chatID = 0;
  private string $message = '';

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore
  ) {}

  public function setToken(string $token) : void
  {
    $this->token = $token;
  }

  public function getToken() : string
  {
    return $this->token;
  }

  public function setChatID(int $id) : void
  {
    $this->chatID = $id;
  }

  public function getChatID() : int
  {
    return $this->chatID;
  }

  public function setMessage(string $text) : void
  {
    $this->message = $text;
  }

  public function getMessage() : string
  {
    return $this->message;
  }

  public function send(string $key) : string|bool
  {
    $URL = "https://sdk.karelian-developer.ru/notifier/max/lagerta";
    
    if (empty($this->chatID) || empty($this->message)) {
      return false;
    }
    
    $params = [
      'chatID' => $this->chatID,
      'message' => $this->message,
      'key' => $key
    ];
    
    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_URL => $URL,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query($params),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2,
      CURLOPT_MAXREDIRS => 3,
      CURLOPT_FAILONERROR => false,
      CURLOPT_USERAGENT => 'CMS-GIRVAS-Notifier/1.0',
      CURLOPT_ENCODING => '',
      CURLOPT_HEADER => false,
    ]);
    
    $response = curl_exec($ch);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errorNo = curl_errno($ch);
    
    curl_close($ch);
    
    if ($errorNo !== CURLE_OK) {
      error_log("cURL Error #{$errorNo}: {$error} - URL: {$fullUrl}");
      return false;
    }
    
    if ($httpCode !== 200) {
      error_log("HTTP Error #{$httpCode} - URL: {$fullUrl}");
      return false;
    }
    
    return $response;
  }
}
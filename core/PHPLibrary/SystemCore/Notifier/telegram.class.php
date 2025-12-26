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
use \core\PHPLibrary\SystemCore\Notifier as Notifier;

class Telegram extends Notifier
{
  private string $token = '';
  private int $chatID = 0;
  private int $threatID = 0;
  private int $businessConnectionID = 0;
  private string $message = '';
  private string $parseModeName = '';

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore,
    private Notifier $platform
  ) {}

  public function getPlatform() : Notifier
  {
    return $platform;
  }

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

  public function setThreatID(int $id) : void
  {
    $this->threatID = $id;
  }

  public function getThreatID() : int
  {
    return $this->threatID;
  }

  public function setBusinessConnectionID(int $id) : void
  {
    $this->businessConnectionID = $id;
  }

  public function getBusinessConnectionID() : int
  {
    return $this->businessConnectionID;
  }

  public function setMessage(string $text) : void
  {
    $this->message = $text;
  }

  public function getMessage() : string
  {
    return $this->message;
  }

  public function setParseModeName(string $name) : void
  {
    $this->parseModeName = $name;
  }

  public function getParseModeName() : string
  {
    return $this->parseModeName !== '' ? $this->parseModeName : 'HTML';
  }

  public function send(string $key) : string|bool
  {
    $URL = "http://sdk.karelian-developer.ru/notifier/tg/lagerta";

    $params = [
      'chatID' => $this->chatID,
      'message' => $this->message,
      'key' => 'NJB2RYMi4mSwDsxrWdHU'
    ];

    return file_get_contents($URL . '?' . http_build_query($params));
  }
}
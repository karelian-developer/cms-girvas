<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Client\Session as ClientSession;

/**
 * Клиент
 */
class Client
{
  private readonly SystemCore $CMSCore;
  private string $ip;
  
  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @return void
   */
  public function __construct(SystemCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;

    $this->setIPAddress();
  }

  /**
   * Назначить IP-адрес клиенту
   *
   * @param  mixed $value
   * @return void
   */
  private function setIPAddress() : void
  {
    $this->ip = self::getRealIPAddress();
  }

  /**
   * Получить IP-адрес клиента
   *
   * @return string
   */
  public function getIPAddress() : string
  {
    return $this->ip;
  }

  /**
   * Получить реальный IP-адрес клиента
   *
   * @return string
   */
  public static function getRealIPAddress() : string
  {
    $ip = '';

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
  }

  /**
   * Получить объект сессии
   *
   * @param  int $typeID
   * @param  array $data
   * @return ClientSession
   */
  public function getSession(int $typeID, array $data = ['*']) : ClientSession
  {
    $session = ClientSession::getByIP($this->CMSCore, $this->ip, $typeID);
    $session->initData($data);

    return $session;
  }

  /**
   * Получить объект сессии по токену
   *
   * @param  int $typeID
   * @param  string $token
   * @param  array $data
   * @return ClientSession
   */
  public function getSessionByToken(int $typeID, string $token, array $data = ['*']) : ClientSession
  {
    $session = ClientSession::getByIPAndToken($this->CMSCore, $this->ip, $token, $typeID);
    $session->initData($data);

    return $session;
  }

  /**
   * Получить объект пользователя, к которому привязана сессия
   *
   * @return ?User
   */
  public function getUser(int $typeID) : ?User
  {
    $cookieTokenName = match ($typeID) {
      2 => '_grv_atoken',
      default => '_grv_utoken'
    };

    $token = $_COOKIE[$cookieTokenName] ?? '';

    $session = ClientSession::getByIPAndToken($this->CMSCore, $this->ip, $token, $typeID);
    return $session !== null ? $session->getUser() : null;
  }

  /**
   * Проверка статуса авторизации клиента по типу сессии
   *
   * @param  int $typeID
   * 
   * @return bool
   */
  public function isLogged(int $typeID) : bool
  {
    $CMSCore = $this->CMSCore;
    $CMSConfigurator = $CMSCore->configurator;

    $cookieTokenName = match ($typeID) {
      2 => '_grv_atoken',
      default => '_grv_utoken'
    };

    $token = $_COOKIE[$cookieTokenName] ?? '';
    if ($token === '') {
      return false;
    } else {
      if (ClientSession::existsByIPAndToken($CMSCore, $this->ip, $token, $typeID)) {
        $session = $this->getSessionByToken($typeID, $token, ['updatedUnixTimestamp', 'token']);

        if ($session !== null) {
          if ($token === $session->getToken()) {
            return $session->isAlive($CMSConfigurator->get('sessionExpires'));
          }
        }
      }
    }

    return false;
  }

  /**
   * Создать Cookie (Устаревшее)
   * 
   * @param SystemCore $CMSCore
   * @param string $name
   * @param ClientSession $session
   * @param int $expires
   * 
   * @return bool
   */
  public static function createCookie(SystemCore $CMSCore, string $name, ClientSession $session, int $expires) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;

    $domainForCookies = $CMSConfigurator->get('domainCookies');
    $userSessionIsSecure = $CMSConfigurator->get('SSLIsEnabled') ? true : false;
    
    if ($domainForCookies !== null) {
      return setcookie($name, $session->getToken(), [
        'expires' => $expires,
        'path' => '/',
        'domain' => $domainForCookies,
        'secure' => $userSessionIsSecure,
        'httponly' => true
      ]);
    }

    return false;
  }
  
  /**
   * Удалить Cookie (Устаревшее)
   * 
   * @param string $name
   * 
   * @return bool
   */
  public static function removeCookie(string $name) : bool
  {
    $cookie = $_COOKIE[$name] ?? '';
    
    if ($cookie !== '') {
      unset($_COOKIE[$name]);
      return setcookie($name, '', time() - 3600, '/');
    }

    return false;
  }
}
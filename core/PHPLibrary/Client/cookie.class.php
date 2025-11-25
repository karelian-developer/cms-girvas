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

namespace core\PHPLibrary\Client\Cookie;

use \core\PHPLibrary\Client\Session as ClientSession;
use \core\PHPLibrary\SystemCore as CMSCore;

class Cookie
{
  /**
   * __construct
   *
   * @param  string $name
   * 
   * @return void
   */
  public function __construct(string $name)
  {
    $this->setName($name);
    $this->initValue();
  }

  /**
   * Получить значение
   * 
   * @return mixed
   */
  public function getValue() : mixed
  {
    return $this->value;
  }

  /**
   * Получить имя
   * 
   * @return string
   */
  public function getName() : string
  {
    return $this->name;
  }

  /**
   * Установить имя
   * 
   * @return void
   */
  public function setName(string $value) : void
  {
    $this->name = $value;
  }

  /**
   * Инициализировать значение
   * 
   * @return void
   */
  public function initValue() : void
  {
    $name = $this->getName();
    $this->value = $_COOKIE[$name] ?? '';
  }

  /**
   * Создать Cookie
   * 
   * @param CMSCore $CMSCore
   * @param ClientSession $session
   * @param string $name
   * @param int $expires
   * 
   * @return bool
   */
  public static function create(CMSCore $CMSCore, ClientSession $session, string $name, int $expires) : Cookie
  {
    $domainForCookies = $CMSCore->configurator->get('domainCookies');
    $SSLIsEnabled = $CMSCore->configurator->get('SSLIsEnabled');
    $userSessionIsSecure = ($SSLIsEnabled) ? true : false;

    if (!is_null($domainForCookies)) {
      return setcookie($name, $session->get_token(), [
        'expires' => $expires,
        'path' => '/',
        'domain' => $domainForCookies,
        'secure' => $userSessionIsSecure,
        'httponly' => true
      ]);
    }

    return new Cookie($name);
  }

  /**
   * Удалить Cookie
   * 
   * @param string $name
   * 
   * @return bool
   */
  public static function remove(string $name) : bool
  {
    if (isset($_COOKIE[$name])) {
      unset($_COOKIE[$name]);
      return setcookie($name, '', time() - 3600, '/');
    }

    return false;
  }
}

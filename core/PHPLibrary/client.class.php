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

namespace core\PHPLibrary;

use \core\PHPLibrary\Client\Session as ClientSession;

/**
 * Клиент
 */
class Client
{
  private string $ip;
  
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {
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
   * Проверка заголовков прокси
   * 
   * @return string|false
   */
  public function checkVPN() : array
  {
    $score = 0;
    $reasons = [];

    $proxyHeader = $this->checkProxyHeaders();
    if ($proxyHeader) {
      $score += 45;
      $reasons[] = "proxy_header: $proxyHeader";
    }

    if ($this->checkSuspiciousUA()) {
      $score += 30;
      $reasons[] = "suspicious_user_agent";
    }

    if ($this->isDatacenterIP($this->ip)) {
      $score += 40;
      $reasons[] = "datacenter_ip";
    }

    return [
      'isVPN' => $score >= 50,
      'score' => $score,
      'reason' => implode(', ', $reasons),
      'ip' => $this->ip
    ];
  }

  /**
   * Проверка подозрительного User-Agent
   * 
   * @return bool
   */
  private function checkProxyHeaders() : string|false
  {
    $proxyHeaders = ['HTTP_VIA', 'HTTP_X_PROXY_ID', 'HTTP_X_FORWARDED_HOST'];
    
    foreach ($proxyHeaders as $header) {
      if (!empty($_SERVER[$header])) {
        return $header;
      }
    }

    return false;
  }

  /**
   * Проверка, относится ли IP к дата-центру
   * 
   * @param string $ip
   * 
   * @return bool
   */
  private function checkSuspiciousUA() : bool
  {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $suspicious = ['curl', 'wget', 'python', 'java', 'okhttp', 'vpn', 'proxy'];
    
    foreach ($suspicious as $pattern) {
      if (stripos($ua, $pattern) !== false) {
        return true;
      }
    }

    return false;
  }

  private function isDatacenterIP(string $ip) : bool
  {
    $firstOctet = (int) explode('.', $ip)[0];
    $dcRanges = [13, 20, 34, 35, 52, 54, 104, 146, 185];

    return in_array($firstOctet, $dcRanges);
  }

  /**
   * Блокировка VPN (если обнаружен)
   * 
   * @param bool $throwException
   * 
   * @return bool
   * 
   * @throws \Exception
   */
  public function blockIfVPN(bool $throwException = true) : bool
  {
    $check = $this->checkVPN();
    
    if ($check['isVPN']) {
      error_log(sprintf(
        "[VPN_BLOCK] IP: %s, Score: %d, Reason: %s, URI: %s\n",
        $check['ip'],
        $check['score'],
        $check['reason'],
        $_SERVER['REQUEST_URI'] ?? '/'
      ), 3, CMS_ROOT_DIRECTORY . '/logs/vpn-blocks.log');
      
      if ($throwException) {
        throw new \Exception('VPN/proxy detected', 403);
      }
      
      return false;
    }
    
    return true;
  }

  /**
   * Проверка принадлежности IP к CIDR сети
   * 
   * @param string $ip
   * @param string $cidr (например, '185.0.0.0/8')
   * 
   * @return bool
   */
  private function ipInCIDR(string $ip, string $cidr) : bool
  {
    if (strpos($cidr, '/') === false) {
      return $ip === $cidr;
    }

    list($subnet, $mask) = explode('/', $cidr);
    
    $ipLong = ip2long($ip);
    $subnetLong = ip2long($subnet);
    
    if ($ipLong === false || $subnetLong === false) {
      return false;
    }
    
    $maskLong = -1 << (32 - (int)$mask);
    $ipLong &= $maskLong;
    $subnetLong &= $maskLong;
    
    return $ipLong === $subnetLong;
  }

  /**
   * Проверка по черному списку
   */
  private function isInBlacklist() : bool
  {
    $blacklist = $this->getBlacklistRanges();

    foreach ($blacklist as $cidr) {
      if ($this->ipInCidr($this->ip, $cidr)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Загрузка черного списка из файла
   */
  private function getBlacklistRanges() : array
  {
    $blacklistFile = CMS_ROOT_DIRECTORY . '/core/blacklistVPNRanges.json';
    
    if (!file_exists($blacklistFile)) {
      return [];
    }
    
    $data = json_decode(file_get_contents($blacklistFile), true);
    
    return $data['ranges'] ?? [];
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
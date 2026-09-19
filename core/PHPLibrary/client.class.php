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
    $this->ip = self::getRealIPAddress($this->CMSCore);
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
   * Получить реальный IP-адрес клиента с учётом доверенных прокси
   *
   * Алгоритм:
   * 1. Если REMOTE_ADDR не входит в список trustedProxies —
   *    возвращаем REMOTE_ADDR, игнорируя заголовки (защита от подделки).
   * 2. Если REMOTE_ADDR доверенный — читаем Forwarded / X-Forwarded-For / X-Real-IP
   *    и идём справа налево, пропуская доверенные прокси.
   * 3. Первый недоверенный IP — реальный клиент.
   *
   * @param CoreInterface|null $CMSCore
   * @return string
   */
  public static function getRealIPAddress(?CoreInterface $CMSCore = null) : string
  {
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
      return '0.0.0.0';
    }

    $trustedProxies = self::getTrustedProxies($CMSCore);

    // Если REMOTE_ADDR не доверенный — игнорируем все заголовки
    if (!self::ipInRanges($remoteAddr, $trustedProxies)) {
      return $remoteAddr;
    }

    // REMOTE_ADDR доверенный — собираем цепочку IP из заголовков
    $chain = self::extractIPChainFromHeaders();

    if (empty($chain)) {
      return $remoteAddr;
    }

    // Идём справа налево, пропуская доверенные прокси
    $chainReversed = array_reverse($chain);

    foreach ($chainReversed as $ip) {
      if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        continue;
      }

      if (!self::ipInRanges($ip, $trustedProxies)) {
        return $ip;
      }
    }

    // Все IP в цепочке доверенные — возвращаем крайний левый
    foreach ($chain as $ip) {
      if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
      }
    }

    return $remoteAddr;
  }

  /**
   * Извлечь цепочку IP из заголовков запроса
   *
   * Приоритет: Forwarded (RFC 7239) → X-Forwarded-For → X-Real-IP
   *
   * @return array
   */
  private static function extractIPChainFromHeaders() : array
  {
    // 1. Forwarded (RFC 7239): for=192.0.2.60;proto=http;by=203.0.113.43
    if (!empty($_SERVER['HTTP_FORWARDED'])) {
      $forwarded = $_SERVER['HTTP_FORWARDED'];
      $chain = [];

      foreach (explode(',', $forwarded) as $part) {
        if (preg_match('/for=("?\[?)([^;\]"]+)\1/i', trim($part), $matches)) {
          $chain[] = trim($matches[2], '[]');
        }
      }

      if (!empty($chain)) {
        return $chain;
      }
    }

    // 2. X-Forwarded-For: client, proxy1, proxy2
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $chain = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
      $chain = array_filter($chain, function($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
      });

      if (!empty($chain)) {
        return array_values($chain);
      }
    }

    // 3. X-Real-IP (nginx)
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
      $ip = trim($_SERVER['HTTP_X_REAL_IP']);
      if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return [$ip];
      }
    }

    return [];
  }

  /**
   * Получить список доверенных прокси из конфигурации
   *
   * @param CoreInterface|null $CMSCore
   * @return array
   */
  private static function getTrustedProxies(?CoreInterface $CMSCore = null) : array
  {
    $defaultRanges = [
      '127.0.0.1/32',
      '::1/128',
    ];

    if ($CMSCore === null) {
      return $defaultRanges;
    }

    try {
      $configurator = $CMSCore->configurator;
      $trustedProxies = $configurator->get('trustedProxies');

      if (!is_array($trustedProxies) || empty($trustedProxies)) {
        return $defaultRanges;
      }

      return array_merge($defaultRanges, $trustedProxies);
    } catch (\Exception $e) {
      return $defaultRanges;
    }
  }

  /**
   * Проверить, входит ли IP в один из диапазонов (CIDR)
   *
   * @param string $ip
   * @param array $ranges
   * @return bool
   */
  private static function ipInRanges(string $ip, array $ranges) : bool
  {
    foreach ($ranges as $range) {
      if (self::ipInCIDR($ip, $range)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Проверка принадлежности IP к CIDR-сети
   * Поддерживает IPv4 и IPv6
   *
   * @param string $ip
   * @param string $cidr
   * @return bool
   */
  private static function ipInCIDR(string $ip, string $cidr) : bool
  {
    // Точное совпадение без маски
    if (strpos($cidr, '/') === false) {
      return $ip === $cidr;
    }

    list($subnet, $mask) = explode('/', $cidr, 2);
    $mask = (int) $mask;

    // IPv4
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
      && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      if ($mask < 0 || $mask > 32) {
        return false;
      }

      $ipLong = ip2long($ip);
      $subnetLong = ip2long($subnet);

      if ($ipLong === false || $subnetLong === false) {
        return false;
      }

      $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));
      return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    // IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
      && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      if ($mask < 0 || $mask > 128) {
        return false;
      }

      $ipBin = inet_pton($ip);
      $subnetBin = inet_pton($subnet);

      if ($ipBin === false || $subnetBin === false) {
        return false;
      }

      $bytes = intdiv($mask, 8);
      $bits = $mask % 8;

      if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
        return false;
      }

      if ($bits > 0) {
        $maskByte = (0xFF << (8 - $bits)) & 0xFF;
        if ((ord($ipBin[$bytes]) & $maskByte) !== (ord($subnetBin[$bytes]) & $maskByte)) {
          return false;
        }
      }

      return true;
    }

    return false;
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

    if ($this->isInBlacklist()) {
      return [
        'isVPN' => true,
        'score' => 100,
        'reason' => 'ip_in_blacklist',
        'ip' => $this->ip
      ];
    }

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

    $logDir = CMS_ROOT_DIRECTORY . '/logs';
    $logFile = $logDir . '/ip-blocks.log';
    
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    
    if (!file_exists($logFile)) {
      touch($logFile);
      chmod($logFile, 0644);
    }
    
    if ($check['isVPN']) {
      error_log(sprintf(
        "[VPN_BLOCK] IP: %s, Score: %d, Reason: %s, URI: %s\n",
        $check['ip'],
        $check['score'],
        $check['reason'],
        $_SERVER['REQUEST_URI'] ?? '/'
      ), 3, CMS_ROOT_DIRECTORY . '/logs/ip-blocks.log');
      
      if ($throwException) {
        throw new \Exception('VPN/proxy detected', 403);
      }
      
      return false;
    }
    
    return true;
  }

  /**
   * Проверка по черному списку
   */
  private function isInBlacklist() : bool
  {
    $blacklist = $this->getBlacklistRanges();

    foreach ($blacklist as $cidr) {
      if (self::ipInCIDR($this->ip, $cidr)) {
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
    $blacklistFile = CMS_ROOT_DIRECTORY . '/core/blacklistIPRanges.json';
    
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
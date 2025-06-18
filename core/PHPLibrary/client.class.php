<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Client\Session as ClientSession;

  /**
   * Клиент
   */
  class Client {
    private readonly SystemCore $CMSCore;
    private string $ip;
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;

      $this->set_ip_address();
    }

    /**
     * Назначить IP-адрес клиенту
     *
     * @param  mixed $value
     * @return void
     */
    private function set_ip_address() : void {
      $this->ip = self::get_real_ip_address();
    }

    /**
     * Получить IP-адрес клиента
     *
     * @return string
     */
    public function get_ip_address() : string {
      return $this->ip;
    }

    /**
     * Получить реальный IP-адрес клиента
     *
     * @return string
     */
    public static function get_real_ip_address() : string {
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
    public function get_session(int $typeID, array $data = ['*']) : ClientSession {
      $session = ClientSession::get_by_ip($this->CMSCore, $this->ip, $typeID);
      $session->init_data($data);

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
    public function get_session_by_token(int $typeID, string $token, array $data = ['*']) : ClientSession {
      $session = ClientSession::get_by_ip_and_token($this->CMSCore, $this->ip, $token, $typeID);
      $session->init_data($data);

      return $session;
    }

    /**
     * Получить объект пользователя, к которому привязана сессия
     *
     * @return User|null
     */
    public function get_user(int $typeID) : User|null {
      switch ($typeID) {
        case 2: $cookieTokenName = '_grv_atoken'; break;
        default: $cookieTokenName = '_grv_utoken';
      }

      $token = (isset($_COOKIE[$cookieTokenName])) ? $_COOKIE[$cookieTokenName] : '';

      $session = ClientSession::get_by_ip_and_token($this->CMSCore, $this->ip, $token, $typeID);
      return (!is_null($session)) ? $session->get_user() : null;
    }

    /**
     * Проверка статуса авторизации клиента по типу сессии
     *
     * @param  int $typeID
     * @return bool
     */
    public function is_logged(int $typeID) : bool {
      switch ($typeID) {
        case 2: $cookieTokenName = '_grv_atoken'; break;
        default: $cookieTokenName = '_grv_utoken';
      }

      $token = (isset($_COOKIE[$cookieTokenName])) ? $_COOKIE[$cookieTokenName] : '';

      if (isset($_COOKIE[$cookieTokenName])) {
        if (ClientSession::exists_by_ip_and_token($this->CMSCore, $this->ip, $token, $typeID)) {
          $session = $this->get_session_by_token($typeID, $token, ['updatedUnixTimestamp', 'token']);
          if (!is_null($session)) {
            if ($token == $session->get_token()) {
              if ($session->is_alive($this->CMSCore->configurator->get('sessionExpires'))) {
                return true;
              }
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
    public static function create_cookie(SystemCore $CMSCore, string $name, ClientSession $session, int $expires) : bool {
      $domainForCookies = $CMSCore->configurator->get('domainCookies');
      $userSessionIsSecure = ($CMSCore->configurator->get('SSLIsEnabled')) ? true : false;
      
      if (!is_null($domainForCookies)) {
        return setcookie($name, $session->get_token(), [
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
    public static function remove_cookie(string $name) : bool {
      if (isset($_COOKIE[$name])) {
        unset($_COOKIE[$name]);
        return setcookie($name, '', time() - 3600, '/');
      }

      return false;
    }
  }
}

?>
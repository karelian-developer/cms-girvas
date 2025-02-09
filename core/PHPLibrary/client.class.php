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
    private readonly SystemCore $system_core;
    private string $ip_address;
    
    /**
     * __construct
     *
     * @param  mixed $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;

      $this->set_ip_address($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Назначить IP-адрес клиенту
     *
     * @param  mixed $value
     * @return void
     */
    private function set_ip_address(string $value) : void {
      $this->ip_address = $value;
    }

    /**
     * Получить IP-адрес клиента
     *
     * @return string
     */
    public function get_ip_address() : string {
      return $this->ip_address;
    }

    /**
     * Получить объект сессии
     *
     * @param  int $session_type_id
     * @param  array $data_init
     * @return ClientSession
     */
    public function get_session(int $session_type_id, array $data_init = ['*']) : ClientSession {
      $session = ClientSession::get_by_ip($this->system_core, $this->ip_address, $session_type_id);
      $session->init_data($data_init);
      return $session;
    }

    /**
     * Получить объект сессии по токену
     *
     * @param  int $session_type_id
     * @param  string $token
     * @param  array $data_init
     * @return ClientSession
     */
    public function get_session_by_token(int $session_type_id, string $token, array $data_init = ['*']) : ClientSession {
      $session = ClientSession::get_by_ip_and_token($this->system_core, $this->ip_address, $token, $session_type_id);
      $session->init_data($data_init);
      return $session;
    }

    /**
     * Получить объект пользователя, к которому привязана сессия
     *
     * @return User|null
     */
    public function get_user(int $session_type_id) : User|null {
      switch ($session_type_id) {
        case 2: $client_session_cookie_token_name = '_grv_atoken'; break;
        default: $client_session_cookie_token_name = '_grv_utoken';
      }

      $client_session_token = (isset($_COOKIE[$client_session_cookie_token_name])) ? $_COOKIE[$client_session_cookie_token_name] : '';

      $session = ClientSession::get_by_ip_and_token($this->system_core, $this->ip_address, $client_session_token, $session_type_id);
      return (!is_null($session)) ? $session->get_user() : null;
    }

    /**
     * Проверка статуса авторизации клиента по типу сессии
     *
     * @param  int $session_type_id
     * @return bool
     */
    public function is_logged(int $session_type_id) : bool {
      switch ($session_type_id) {
        case 2: $client_session_cookie_token_name = '_grv_atoken'; break;
        default: $client_session_cookie_token_name = '_grv_utoken';
      }

      $client_session_token = (isset($_COOKIE[$client_session_cookie_token_name])) ? $_COOKIE[$client_session_cookie_token_name] : '';

      if (isset($_COOKIE[$client_session_cookie_token_name])) {
        if (ClientSession::exists_by_ip_and_token($this->system_core, $this->ip_address, $client_session_token, $session_type_id)) {
          $client_session = $this->get_session_by_token($session_type_id, $client_session_token, ['updated_unix_timestamp', 'token']);
          if (!is_null($client_session)) {
            if ($client_session_token == $client_session->get_token()) {
              if ($client_session->is_alive($this->system_core->configurator->get('session_expires'))) {
                return true;
              }
            }
          }
        }
      }

      return false;
    }

    /**
     * Создать Cookie
     * 
     * @param SystemCore $system_core
     * @param string $name
     * @param ClientSession $session
     * @param int $expires
     * 
     * @return bool
     */
    public static function create_cookie(SystemCore $system_core, string $name, ClientSession $session, int $expires) : bool {
      $domain_for_cookies = $system_core->configurator->get('domain_cookies');
      $user_session_is_secure = ($system_core->configurator->get('ssl_is_enabled')) ? true : false;
      
      if (!is_null($domain_for_cookies)) {
        return setcookie($name, $session->get_token(), [
          'expires' => $expires,
          'path' => '/',
          'domain' => $domain_for_cookies,
          'secure' => $user_session_is_secure,
          'httponly' => true
        ]);
      }

      return false;
    }
    
    /**
     * Удалить Cookie
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
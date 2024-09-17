<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  class EmailSender {
    private readonly SystemCore $system_core;
    private array $from_user = [];
    private string $to_user_email = '';
    private string $subject = '';
    private string $content = '';
    private array $headers = [];
    
    /**
     * __construct
     *
     * @param  mixed $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;
    }
    
    /**
     * Назначить данные отправителя
     *
     * @param  string $user_name
     * @param  string $user_email
     * @return void
     */
    public function set_from_user(string $user_name, string $user_email) : void {
      $this->from_user['name'] = $user_name;
      $this->from_user['email'] = $user_email;
    }
    
    /**
     * Получить данные отправителя
     *
     * @return array
     */
    public function get_from_user() : array {
      return $this->from_user;
    }
    
    /**
     * Назначить E-Mail получателя
     *
     * @param  string $user_email
     * @return void
     */
    public function set_to_user_email(string $user_email) : void {
      $this->to_user_email = $user_email;
    }
    
    /**
     * Получить E-Mail получателя
     *
     * @return array
     */
    public function get_to_user_email() : string {
      return $this->to_user_email;
    }
    
    /**
     * Назначить заголовок электронного письма
     *
     * @param  mixed $value
     * @return void
     */
    public function set_subject(string $value) : void {
      $this->subject = $value;
    }
    
    /**
     * Назначить содержимое электронного письма
     *
     * @param  mixed $value
     * @return void
     */
    public function set_content(string $value) : void {
      $this->content = $value;
    }
    
    /**
     * Получить заголовок электронного письма
     *
     * @return string
     */
    public function get_subject() : string {
      return $this->subject;
    }
    
    /**
     * Получить содержимое электронного письма
     *
     * @return string
     */
    public function get_content() : string {
      return $this->content;
    }
    
    /**
     * Получить массив заголовков электронного письма
     *
     * @return array
     */
    public function get_headers() : array {
      return $this->headers;
    }
    
    /**
     * Добавить заголовок электронного письма
     *
     * @param  mixed $value
     * @return void
     */
    public function add_header(string $value) : void {
      array_push($this->headers, $value);
    }
    
    /**
     * Отправить электронное письмо
     *
     * @return bool
     */
    public function send() : bool {
      $from_user = $this->get_from_user();

      return mail(
        $this->get_to_user_email(),
        $this->get_subject(),
        $this->get_content(),
        implode($this->get_headers())
      );
    }

    /**
     * Получить доменное имя системного отправителя электронной почты
     * 
     * @param SystemCore $system_core
     * 
     * @return string
     */
    public static function get_system_sender_domain(SystemCore $system_core) : string {
      return ($system_core->configurator->exists('domain_email')) ? $system_core->configurator->get('domain_email') : 'example.ru';
    }

    /**
     * Получить E-Mail системного отправителя электронной почты
     * 
     * @param SystemCore $system_core
     * 
     * @return string
     */
    public static function get_system_sender_email(SystemCore $system_core) : string {
      $sender_name = EmailSender::get_system_sender_name($system_core);
      return sprintf('%s@%s', $sender_name, EmailSender::get_system_sender_domain($system_core));
    }

    /**
     * Получить имя системного отправителя электронной почты
     * 
     * @param SystemCore $system_core
     * 
     * @return string
     */
    public static function get_system_sender_name(SystemCore $system_core) : string {
      return 'no-reply';
    }
  }
}

?>
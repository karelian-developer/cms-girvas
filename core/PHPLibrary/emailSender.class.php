<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  class EmailSender {
    private readonly SystemCore $CMSCore;
    private array $fromUser = [];
    private string $toUserEmail = '';
    private string $subject = '';
    private string $content = '';
    private array $headers = [];
    
    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
    }
    
    /**
     * Назначить данные отправителя
     *
     * @param  string $name
     * @param  string $email
     * @return void
     */
    public function set_from_user(string $name, string $email) : void {
      $this->fromUser['name'] = $name;
      $this->fromUser['email'] = $email;
    }
    
    /**
     * Получить данные отправителя
     *
     * @return array
     */
    public function get_from_user() : array {
      return $this->fromUser;
    }
    
    /**
     * Назначить E-Mail получателя
     *
     * @param  string $email
     * @return void
     */
    public function set_to_user_email(string $email) : void {
      $this->toUserEmail = $email;
    }
    
    /**
     * Получить E-Mail получателя
     *
     * @return array
     */
    public function get_to_user_email() : string {
      return $this->toUserEmail;
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
      $fromUser = $this->get_from_user();

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
     * @param SystemCore $CMSCore
     * 
     * @return string
     */
    public static function get_system_sender_domain(SystemCore $CMSCore) : string {
      return ($CMSCore->configurator->exists('domainEmail')) ? $CMSCore->configurator->get('domainEmail') : 'example.ru';
    }

    /**
     * Получить E-Mail системного отправителя электронной почты
     * 
     * @param SystemCore $CMSCore
     * 
     * @return string
     */
    public static function get_system_sender_email(SystemCore $CMSCore) : string {
      $sender_name = EmailSender::get_system_sender_name($CMSCore);
      return $sender_name . '@' . EmailSender::get_system_sender_domain($CMSCore);
    }

    /**
     * Получить имя системного отправителя электронной почты
     * 
     * @param SystemCore $CMSCore
     * 
     * @return string
     */
    public static function get_system_sender_name(SystemCore $CMSCore) : string {
      return 'no-reply';
    }
  }
}

?>
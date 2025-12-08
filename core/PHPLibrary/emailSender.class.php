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

class EmailSender
{
  private array $fromUser = [];
  private string $toUserEmail = '';
  private string $subject = '';
  private string $content = '';
  private array $headers = [];
  
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {}
  
  /**
   * Назначить данные отправителя
   *
   * @param  string $name
   * @param  string $email
   * @return void
   */
  public function setFromUser(string $name, string $email) : void
  {
    $this->fromUser['name'] = $name;
    $this->fromUser['email'] = $email;
  }
  
  /**
   * Получить данные отправителя
   *
   * @return array
   */
  public function getFromUser() : array
  {
    return $this->fromUser;
  }
  
  /**
   * Назначить E-Mail получателя
   *
   * @param  string $email
   * @return void
   */
  public function setToUserEmail(string $email) : void
  {
    $this->toUserEmail = $email;
  }
  
  /**
   * Получить E-Mail получателя
   *
   * @return array
   */
  public function getToUserEmail() : string
  {
    return $this->toUserEmail;
  }
  
  /**
   * Назначить заголовок электронного письма
   *
   * @param  mixed $value
   * @return void
   */
  public function setSubject(string $value) : void
  {
    $this->subject = $value;
  }
  
  /**
   * Назначить содержимое электронного письма
   *
   * @param  mixed $value
   * @return void
   */
  public function setContent(string $value) : void
  {
    $this->content = $value;
  }
  
  /**
   * Получить заголовок электронного письма
   *
   * @return string
   */
  public function getSubject() : string
  {
    return $this->subject;
  }
  
  /**
   * Получить содержимое электронного письма
   *
   * @return string
   */
  public function getContent() : string
  {
    return $this->content;
  }
  
  /**
   * Получить массив заголовков электронного письма
   *
   * @return array
   */
  public function getHeaders() : array
  {
    return $this->headers;
  }
  
  /**
   * Добавить заголовок электронного письма
   *
   * @param  mixed $value
   * @return void
   */
  public function addHeader(string $value) : void
  {
    array_push($this->headers, $value);
  }
  
  /**
   * Отправить электронное письмо
   *
   * @return bool
   */
  public function send() : bool
  {
    $fromUser = $this->getFromUser();

    return mail(
      $this->getToUserEmail(),
      $this->getSubject(),
      $this->getContent(),
      implode($this->getHeaders())
    );
  }

  /**
   * Получить доменное имя системного отправителя электронной почты
   * 
   * @param SystemCore $CMSCore
   * 
   * @return string
   */
  public static function getSystemSenderDomain(SystemCore $CMSCore) : string
  {
    return ($CMSCore->configurator->exists('domainEmail')) ? $CMSCore->configurator->get('domainEmail') : 'example.ru';
  }

  /**
   * Получить E-Mail системного отправителя электронной почты
   * 
   * @param SystemCore $CMSCore
   * 
   * @return string
   */
  public static function getSystemSenderEmail(SystemCore $CMSCore) : string
  {
    $sender_name = EmailSender::getSystemSenderName($CMSCore);
    return $sender_name . '@' . EmailSender::getSystemSenderDomain($CMSCore);
  }

  /**
   * Получить имя системного отправителя электронной почты
   * 
   * @param SystemCore $CMSCore
   * 
   * @return string
   */
  public static function getSystemSenderName(SystemCore $CMSCore) : string
  {
    return 'no-reply';
  }
}
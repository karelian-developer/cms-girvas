<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

/**
 * Templates
 * 
 * Класс для работы с несколькими шаблонами CMS
 * 
 * @author Andrey Shestakov <drelagas.new@yandex.ru>
 * @version 0.0.1-1
 */
class Timestamp
{
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $time
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $time
  ) {}

  /**
   * Установить время (UNIX)
   * 
   * @param int $time
   * 
   * @return void
   */
  public function setTime(int $time) : void
  {
    $this->time = $time;
  }

  /**
   * Получить время UNIX
   * 
   * @return int
   */
  public function getTime() : int
  {
    return $this->time;
  }
}
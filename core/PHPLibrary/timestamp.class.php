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
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  /**
   * Templates
   * 
   * Класс для работы с несколькими шаблонами CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  class Timestamp {
    public SystemCore $system_core;
    private int $time;

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param int $time
     */
    public function __construct(SystemCore $system_core, int $time) {
      $this->system_core = $system_core;
      $this->set_time($time);
    }

    /**
     * Установить время (UNIX)
     * 
     * @param int $time
     * 
     * @return void
     */
    public function set_time(int $time) : void {
      $this->time = $time;
    }

    /**
     * Получить время UNIX
     * 
     * @return int
     */
    public function get_time() : int {
      return $this->time;
    }
  }
}
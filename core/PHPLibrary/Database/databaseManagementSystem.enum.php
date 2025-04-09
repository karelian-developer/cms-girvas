<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database {
  
  enum DatabaseManagementSystem : string {
    case MySQL = 'mysql';
    case PostgreSQL = 'pgsql';

    public function get_string() : string {
      return match ($this) {
        self::MySQL => 'MySQL',
        self::PostgreSQL => 'PostgreSQL',
      };
    }
  }
}

?>
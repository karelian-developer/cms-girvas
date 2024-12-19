<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\Database as Database;
  use \core\PHPLibrary\Database\DatabaseManagementSystem as DatabaseManagementSystem;
  use \core\PHPLibrary\SystemCore as SystemCore;

  final class DatabaseConnector {
    private mixed $system_core = null;
    public \core\PHPLibrary\Database|null $database = null;
    
    /**
     * __construct
     *
     * @param  mixed $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core, Configurator $configurator, bool $is_test = false) {
      $this->system_core = $system_core;

      $database_configurations = $configurator->get('database');
      $this->database = new Database($database_configurations['dms']);
      $this->database->set_database_name($database_configurations['name']);
      $this->database->set_database_user($database_configurations['user']);
      $this->database->set_database_host($database_configurations['host']);
      $this->database->set_database_password($database_configurations['password']);
      
      if (!$is_test) {
        @$this->database->connect();
      }
    }

  }

}

?>
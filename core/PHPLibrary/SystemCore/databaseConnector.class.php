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
    private mixed $CMSCore = null;
    public \core\PHPLibrary\Database|null $database = null;
    
    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore, Configurator $configurator, bool $isTest = false) {
      $this->CMSCore = $CMSCore;

      $databaseConfigurations = $configurator->get('database');
      $this->database = new Database($databaseConfigurations['dms']);
      $this->database->set_database_name($databaseConfigurations['name']);
      $this->database->set_database_user($databaseConfigurations['user']);
      $this->database->set_database_host($databaseConfigurations['host']);
      $this->database->set_database_password($databaseConfigurations['password']);
      
      if (!$isTest) {
        $errorIsJSON = $CMSCore->urlp->get_path(0) === 'handler' ? true : false;
        @$this->database->connect($errorIsJSON);
      }
    }

  }

}

?>
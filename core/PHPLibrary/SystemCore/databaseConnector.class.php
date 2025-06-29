<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\Database as CMSDatabase;
use \core\PHPLibrary\Database\DatabaseManagementSystem as DatabaseManagementSystem;
use \core\PHPLibrary\SystemCore as CMSCore;

final class DatabaseConnector
{
  private CMSCore|null $CMSCore = null;
  public CMSDatabase|null $database = null;
  
  /**
   * __construct
   *
   * @param CMSCore $CMSCore
   * @param Configurator $configurator
   * @param bool $isTest
   * 
   * @return void
   */
  public function __construct(CMSCore $CMSCore, Configurator $configurator, bool $isTest = false)
  {
    $this->CMSCore = $CMSCore;

    $databaseConfigurations = $configurator->get('database');
    $this->database = new CMSDatabase($databaseConfigurations['dms']);
    $this->database->setDatabaseName($databaseConfigurations['name']);
    $this->database->setDatabaseUser($databaseConfigurations['user']);
    $this->database->setDatabaseHost($databaseConfigurations['host']);
    $this->database->setDatabasePassword($databaseConfigurations['password']);
    
    if (!$isTest) {
      $errorIsJSON = $CMSCore->urlp->getPath(0) === 'handler' ? true : false;
      @$this->database->connect($errorIsJSON);
    }
  }
}
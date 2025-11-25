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

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\Database as CMSDatabase;
use \core\PHPLibrary\Database\DatabaseManagementSystem as DatabaseManagementSystem;
use \core\PHPLibrary\SystemCore as CMSCore;

final class Connector
{
  private ?CMSCore $CMSCore = null;
  public ?CMSDatabase $database = null;
  
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
      $errorIsJSON = $CMSCore->urlp->getPath(0) === 'handler';
      @$this->database->connect($errorIsJSON);
    }
  }
}
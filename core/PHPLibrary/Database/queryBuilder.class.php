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
namespace core\PHPLibrary\Database;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementAlterTable as StatementAlterTable;
use \core\PHPLibrary\Database\QueryBuilder\StatementCreateTable as StatementCreateTable;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;
use \core\PHPLibrary\Database\QueryBuilder\StatementInsert as StatementInsert;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate as StatementUpdate;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete as StatementDelete;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

class QueryBuilder
{
  public InterfaceStatement $statement;
  public readonly CMSCore $CMSCore;
  public DMS $DMS;

  /**
   * __construct
   *
   * @param CMSCore $CMSCore
   * @param DMS $DMS
   * 
   * @return void
   */
  public function __construct(CMSCore $CMSCore, DMS $DMS = DMS::PostgreSQL) {
    $this->CMSCore = $CMSCore;
    $this->DMS = $DMS;
  }
  
  /**
   * setStatement_select
   *
   * @return void
   */
  public function setStatementCreateTable() : void {
    $this->statement = new StatementCreateTable($this);
  }
  
  /**
   * setStatementSelect
   *
   * @return void
   */
  public function setStatementSelect() : void {
    $this->statement = new StatementSelect($this);
  }
  
  /**
   * setStatementInsert
   *
   * @return void
   */
  public function setStatementInsert() : void {
    $this->statement = new StatementInsert($this);
  }
  
  /**
   * setStatementUpdate
   *
   * @return void
   */
  public function setStatementUpdate() : void {
    $this->statement = new StatementUpdate($this);
  }
  
  /**
   * setStatementDelete
   *
   * @return void
   */
  public function setStatementDelete() : void {
    $this->statement = new StatementDelete($this);
  }
  
  /**
   * setStatementAlterTable
   *
   * @return void
   */
  public function setStatementAlterTable() : void {
    $this->statement = new StatementAlterTable($this);
  }
}
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database;

use \core\PHPLibrary\SystemCore as SystemCore;
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
  public readonly SystemCore $CMSCore;

  /**
   * __construct
   *
   * @return void
   */
  public function __construct(SystemCore $CMSCore) {
    $this->CMSCore = $CMSCore;
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
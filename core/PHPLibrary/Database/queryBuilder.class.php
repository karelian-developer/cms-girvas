<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Database\QueryBuilder\StatementCreateTable as StatementCreateTable;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;
  use \core\PHPLibrary\Database\QueryBuilder\StatementInsert as StatementInsert;
  use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate as StatementUpdate;
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete as StatementDelete;

  class QueryBuilder {
    public StatementCreateTable|StatementSelect|StatementInsert|StatementUpdate|StatementDelete $statement;
    public readonly SystemCore $system_core;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;
    }
    
    /**
     * set_statement_select
     *
     * @return void
     */
    public function set_statement_create_table() : void {
      $this->statement = new StatementCreateTable($this);
    }
    
    /**
     * set_statement_select
     *
     * @return void
     */
    public function set_statement_select() : void {
      $this->statement = new StatementSelect($this);
    }
    
    /**
     * set_statement_insert
     *
     * @return void
     */
    public function set_statement_insert() : void {
      $this->statement = new StatementInsert($this);
    }
    
    /**
     * set_statement_update
     *
     * @return void
     */
    public function set_statement_update() : void {
      $this->statement = new StatementUpdate($this);
    }
    
    /**
     * set_statement_delete
     *
     * @return void
     */
    public function set_statement_delete() : void {
      $this->statement = new StatementDelete($this);
    }
  }

}

?>
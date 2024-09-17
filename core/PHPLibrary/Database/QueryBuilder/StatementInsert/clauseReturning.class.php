<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementInsert {
  use \core\PHPLibrary\Database\QueryBuilder\StatementInsert\InterfaceClause as InterfaceClause;
  use \core\PHPLibrary\Database\QueryBuilder\StatementInsert as StatementInsert;

  final class ClauseReturning implements InterfaceClause {
    private StatementInsert $statement;
    public array $columns = [];
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  StatementInsert $statement
     * @return void
     */
    public function __construct(StatementInsert $statement) {
      $this->statement = $statement;
    }
    
    /**
     * Добавить колонку значения
     *
     * @param  mixed $column_name
     * @return void
     */
    public function add_column(string $column_name) : void {
      array_push($this->columns, $column_name);
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() : void {
      $this->assembled = sprintf('RETURNING %s', implode(', ', $this->columns));
    }

  }
}

?>
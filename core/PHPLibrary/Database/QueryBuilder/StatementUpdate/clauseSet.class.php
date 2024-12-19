<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementUpdate {
  use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\InterfaceClause as InterfaceClause;
  use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate as StatementUpdate;

  final class ClauseSet implements InterfaceClause {
    private StatementUpdate $statement;
    private array $columns = [];
    private array $values = [];
    public array $tables;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $statement
     * @return void
     */
    public function __construct(StatementUpdate $statement) {
      $this->statement = $statement;
    }

    /**
     * Добавить значение столбца
     *
     * @param  mixed $column_name
     * @param  mixed $value
     * @return void
     */
    public function add_column(string $column_name, mixed $column_value = null) : void {
      array_push($this->columns, $column_name);

      if (!is_null($column_value)) {
        $this->values[$column_name] = $column_value;
      }
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      $query_array = [];

      foreach ($this->columns as $column_name) {
        $column_value = (isset($this->values[$column_name])) ? $this->values[$column_name] : ':' . $column_name;
        array_push($query_array, sprintf('%s = %s', $column_name, $column_value));
      }

      if (count($query_array) > 0) {
        $this->assembled = sprintf('SET %s', implode(', ', $query_array));
      } else {
        $this->assembled =  '';
      }
    }

  }
}

?>
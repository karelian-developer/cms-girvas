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
     * @param  mixed $name
     * @param  mixed $value
     * @return void
     */
    public function add_column(string $name, mixed $value = null) : void {
      array_push($this->columns, $name);

      if (!is_null($value)) {
        $this->values[$name] = $value;
      }
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      $queryArray = [];

      foreach ($this->columns as $name) {
        $value = (isset($this->values[$name])) ? $this->values[$name] : ':' . $name;
        array_push($queryArray, sprintf('%s = %s', $name, $value));
      }

      if (count($queryArray) > 0) {
        $this->assembled = sprintf('SET %s', implode(', ', $queryArray));
      } else {
        $this->assembled =  '';
      }
    }

  }
}

?>
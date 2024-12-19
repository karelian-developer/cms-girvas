<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementSelect {
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\InterfaceClause as InterfaceClause;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;

  final class ClauseWhere implements InterfaceClause {
    private StatementSelect $statement;
    public array $conditions = [];
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $statement
     * @return void
     */
    public function __construct(StatementSelect $statement) {
      $this->statement = $statement;
    }
    
    /**
     * set_condition
     *
     * @param  mixed $condition
     * @return void
     */
    public function add_condition(string $condition, string $conjunction = '') : void {
      array_push($this->conditions, (!empty($conjunction)) ? sprintf('%s %s', $conjunction, $condition) : $condition);
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      if (!empty($this->conditions)) {
        $this->assembled = sprintf('WHERE %s', implode(' ', $this->conditions));
      } else {
        $this->assembled = '';
      }
    }

  }
}

?>
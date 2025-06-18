<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementDelete {
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\InterfaceClause as InterfaceClause;
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete as StatementDelete;

  final class ClauseWhere implements InterfaceClause {
    private StatementDelete $statement;
    public string $condition = '';
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $statement
     * @return void
     */
    public function __construct(StatementDelete $statement) {
      $this->statement = $statement;
    }
    
    /**
     * set_condition
     *
     * @param  mixed $condition
     * @return void
     */
    public function add_condition(string $condition) : void {
      $this->condition = $condition;
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      if ($this->condition !== '') {
        $this->assembled = sprintf('WHERE %s', $this->condition);
      } else {
        $this->assembled = '';
      }
    }

  }
}

?>
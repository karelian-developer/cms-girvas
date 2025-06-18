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

  final class ClauseOrderBy implements InterfaceClause {
    const SORT_TYPE_DESC = 'DESC';
    const SORT_TYPE_ASC = 'ASC';

    private StatementSelect $statement;
    public string $column = '';
    public string $sortType = '';
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  StatementSelect $statement
     * @return void
     */
    public function __construct(StatementSelect $statement) {
      $this->statement = $statement;
    }
    
    /**
     * set_sort_type
     *
     * @param  string $value
     * @return void
     */
    public function set_sort_type(string $value) : void {
      $this->sortType = $value;
    }
    
    /**
     * set_column
     *
     * @param  mixed $value
     * @return void
     */
    public function set_column(string $value) : void {
      $this->column = $value;
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() : void {
      $this->assembled = sprintf('ORDER BY "%s" %s', $this->column, $this->sortType);
    }

  }
}

?>
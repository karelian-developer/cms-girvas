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

  final class ClauseLimit implements InterfaceClause {
    private StatementSelect $statement;
    public int $limit = 100;
    public int $offset = 0;
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
     * set_limit
     *
     * @param  int $value
     * @return void
     */
    public function set_limit(int $value) : void {
      $this->limit = $value;
    }
    
    /**
     * set_offset
     *
     * @param  int $value
     * @return void
     */
    public function set_offset(int $value) : void {
      $this->offset = $value;
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() : void {
      if ($this->limit >= 1) {
        if ($this->offset <= 0) {
          $this->assembled = sprintf('LIMIT %d', $this->limit);
        } else {
          $this->assembled = sprintf('LIMIT %d OFFSET %d', $this->limit, $this->offset);
        }
      } else {
        $this->assembled = '';
      }
    }

  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder {
  use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseFrom as ClauseFrom;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseWhere as ClauseWhere;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseOrderBy as ClauseOrderBy;
  use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseLimit as ClauseLimit;
  use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

  final class StatementSelect implements InterfaceStatement {
    public QueryBuilder $queryBuilder;
    private array $selections = [];
    public ClauseFrom|null $clauseFrom = null;
    public ClauseWhere|null $clauseWhere = null;
    public ClauseOrderBy|null $clauseOrderBy = null;
    public ClauseLimit|null $clauseLimit = null;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $queryBuilder
     * @return void
     */
    public function __construct(QueryBuilder $queryBuilder) {
      $this->queryBuilder = $queryBuilder;
    }
    
    /**
     * Установить выборку для SELECT
     *
     * @param  mixed $selection
     * @return void
     */
    public function add_selections(array $selections) : void {
      foreach ($selections as $index => $selection) {
        if (!preg_match('/\"[a-z0-9_]\"/i', $selection) && is_numeric($selection) && $selection !== '*') {
          $selections[$index] = '"' . $selection . '"';
        }
      }

      $this->selections = array_merge($this->selections, $selections);
    }
    
    /**
     * Установить предложение FROM
     *
     * @return void
     */
    public function set_clause_from() : void {
      $this->clauseFrom = new ClauseFrom($this);
    }
    
    /**
     * Установить предложение WHERE
     *
     * @return void
     */
    public function set_clause_where() : void {
      $this->clauseWhere = new ClauseWhere($this);
    }
    
    /**
     * Установить предложение ORDER BY
     *
     * @return void
     */
    public function set_clause_order_by() : void {
      $this->clauseOrderBy = new ClauseOrderBy($this);
    }
    
    /**
     * Установить предложение LIMIT
     *
     * @param  mixed $clauseLimit
     * @return void
     */
    public function set_clause_limit(int $limit, int $offset = 0) : void {
      $this->clauseLimit = new ClauseLimit($this);
      $this->clauseLimit->set_limit($limit);
      $this->clauseLimit->set_offset($offset);
    }
    
    /**
     * Сборка SQL-запроса
     *
     * @return void
     */
    public function assembly() : void {
      $queryArray = [];
      if (!empty($this->selections)) {
        array_push($queryArray, implode(', ', $this->selections));
      } else {
        array_push($queryArray, '*');
      }

      if (!is_null($this->clauseFrom)) {
        $this->clauseFrom->assembly();
        array_push($queryArray, $this->clauseFrom->assembled);
      }

      if (!is_null($this->clauseWhere)) {
        $this->clauseWhere->assembly();
        array_push($queryArray, $this->clauseWhere->assembled);
      }

      if (!is_null($this->clauseOrderBy)) {
        $this->clauseOrderBy->assembly();
        array_push($queryArray, $this->clauseOrderBy->assembled);
      }

      if (!is_null($this->clauseLimit)) {
        $this->clauseLimit->assembly();
        array_push($queryArray, $this->clauseLimit->assembled);
      }

      $this->assembled = sprintf('SELECT %s;', implode(' ', $queryArray));
    }

  }

}

?>
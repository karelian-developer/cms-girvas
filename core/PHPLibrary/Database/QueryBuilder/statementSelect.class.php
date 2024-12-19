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
    public QueryBuilder $query_builder;
    private array $selections = [];
    public ClauseFrom|null $clause_from = null;
    public ClauseWhere|null $clause_where = null;
    public ClauseOrderBy|null $clause_order_by = null;
    public ClauseLimit|null $clause_limit = null;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $query_builder
     * @return void
     */
    public function __construct(QueryBuilder $query_builder) {
      $this->query_builder = $query_builder;
    }
    
    /**
     * Установить выборку для SELECT
     *
     * @param  mixed $selection
     * @return void
     */
    public function add_selections(array $selections) : void {
      $this->selections = array_merge($this->selections, $selections);
    }
    
    /**
     * Установить предложение FROM
     *
     * @return void
     */
    public function set_clause_from() : void {
      $this->clause_from = new ClauseFrom($this);
    }
    
    /**
     * Установить предложение WHERE
     *
     * @return void
     */
    public function set_clause_where() : void {
      $this->clause_where = new ClauseWhere($this);
    }
    
    /**
     * Установить предложение ORDER BY
     *
     * @return void
     */
    public function set_clause_order_by() : void {
      $this->clause_order_by = new ClauseOrderBy($this);
    }
    
    /**
     * Установить предложение LIMIT
     *
     * @param  mixed $clause_limit
     * @return void
     */
    public function set_clause_limit(int $limit, int $offset = 0) : void {
      $this->clause_limit = new ClauseLimit($this);
      $this->clause_limit->set_limit($limit);
      $this->clause_limit->set_offset($offset);
    }
    
    /**
     * Сборка SQL-запроса
     *
     * @return void
     */
    public function assembly() : void {
      $query_array = [];
      if (!empty($this->selections)) {
        array_push($query_array, implode(', ', $this->selections));
      } else {
        array_push($query_array, '*');
      }

      if (!is_null($this->clause_from)) {
        $this->clause_from->assembly();
        array_push($query_array, $this->clause_from->assembled);
      }

      if (!is_null($this->clause_where)) {
        $this->clause_where->assembly();
        array_push($query_array, $this->clause_where->assembled);
      }

      if (!is_null($this->clause_order_by)) {
        $this->clause_order_by->assembly();
        array_push($query_array, $this->clause_order_by->assembled);
      }

      if (!is_null($this->clause_limit)) {
        $this->clause_limit->assembly();
        array_push($query_array, $this->clause_limit->assembled);
      }

      $this->assembled = sprintf('SELECT %s;', implode(' ', $query_array));
    }

  }

}

?>
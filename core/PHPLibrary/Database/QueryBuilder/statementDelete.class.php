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
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom as ClauseFrom;
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseWhere as ClauseWhere;
  use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;


  final class StatementDelete implements InterfaceStatement {
    public QueryBuilder $queryBuilder;
    public ClauseFrom|null $clauseFrom = null;
    public ClauseWhere|null $clauseWhere = null;
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
     * Сборка SQL-запроса
     *
     * @return void
     */
    public function assembly() : void {
      $queryArray = [];

      if ($this->clauseFrom !== null) {
        $this->clauseFrom->assembly();
        array_push($queryArray, $this->clauseFrom->assembled);
      }

      if ($this->clauseWhere !== null) {
        $this->clauseWhere->assembly();
        array_push($queryArray, $this->clauseWhere->assembled);
      }

      $this->assembled = sprintf('DELETE %s;', implode(' ', $queryArray));
    }
  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder;

use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom as ClauseFrom;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseWhere as ClauseWhere;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;


final class StatementDelete implements InterfaceStatement
{
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
  public function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
  }
  
  /**
   * Установить предложение FROM
   *
   * @return void
   */
  public function setClauseFrom() : void
  {
    $this->clauseFrom = new ClauseFrom($this);
  }
  
  /**
   * Установить предложение WHERE
   *
   * @return void
   */
  public function setClauseWhere() : void
  {
    $this->clauseWhere = new ClauseWhere($this);
  }

  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $queryArray = [];

    $clausesToPrecess = $this->getClausesToProcess();
    foreach ($clausesToPrecess as $clause) {
      if ($clause !== null) {
        $clause->assembly();
        $queryArray[] = $clause->assembled;
      }
    }

    $this->assembled = sprintf('DELETE %s;', implode(' ', $queryArray));
  }

  /**
   * Получение массива объектов предложений
   */
  private function getClausesToProcess() : array
  {
    return [
      $this->clauseFrom,
      $this->clauseWhere
    ];
  }
}
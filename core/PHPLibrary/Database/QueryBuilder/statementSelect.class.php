<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Database\QueryBuilder;

use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseFrom as ClauseFrom;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseWhere as ClauseWhere;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseOrderBy as ClauseOrderBy;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseLimit as ClauseLimit;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementSelect implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private array $selections = [];
  public ?ClauseFrom $clauseFrom = null;
  public ?ClauseWhere $clauseWhere = null;
  public ?ClauseOrderBy $clauseOrderBy = null;
  public ?ClauseLimit $clauseLimit = null;
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
   * Установить выборку для SELECT
   *
   * @param  mixed $selection
   * @return void
   */
  public function addSelections(array $selections) : void
  {
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');

    foreach ($selections as $index => $selection) {
      if (!preg_match('/\"[a-z0-9_]+\"/i', $selection) && !preg_match('/[a-z]+\([a-z0-9_]*[*]*\)/i', $selection) && !is_numeric($selection) && $selection !== '*') {
        $selections[$index] = match ($CMSConfigDatabase['dms']) {
          CMSDMS::MySQL => '`' . $selection . '`',
          CMSDMS::PostgreSQL => '"' . $selection . '"',
        };
      }
    }

    $this->selections = array_merge($this->selections, $selections);
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
   * Установить предложение ORDER BY
   *
   * @return void
   */
  public function setClauseOrderBy() : void
  {
    $this->clauseOrderBy = new ClauseOrderBy($this);
  }
  
  /**
   * Установить предложение LIMIT
   *
   * @param  mixed $clauseLimit
   * @return void
   */
  public function setClauseLimit(int $limit, int $offset = 0) : void
  {
    $this->clauseLimit = new ClauseLimit($this);
    $this->clauseLimit->setLimit($limit);
    $this->clauseLimit->setOffset($offset);
  }
  
  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $queryArray = [];
    $queryArray[] = !empty($this->selections) ? implode(', ', $this->selections) : '*';

    $clausesToPrecess = $this->getClausesToProcess();
    foreach ($clausesToPrecess as $clause) {
      if ($clause !== null) {
        $clause->assembly();
        $queryArray[] = $clause->assembled;
      }
    }

    $this->assembled = sprintf('SELECT %s;', implode(' ', $queryArray));
  }

  /**
   * Получение массива объектов предложений
   */
  private function getClausesToProcess() : array
  {
    return [
      $this->clauseFrom,
      $this->clauseWhere,
      $this->clauseOrderBy,
      $this->clauseLimit
    ];
  }
}
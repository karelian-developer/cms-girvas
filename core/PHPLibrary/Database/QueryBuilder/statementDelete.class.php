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
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom as ClauseFrom;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseWhere as ClauseWhere;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementDelete implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  public ?ClauseFrom $clauseFrom = null;
  public ?ClauseWhere $clauseWhere = null;
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
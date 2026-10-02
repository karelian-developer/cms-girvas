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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementUpdate;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate as StatementUpdate;

final class ClauseWhere implements InterfaceClause
{
  private StatementUpdate $statement;
  public string $condition = '';
  private array $conditions = [];
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  mixed $statement
   * @return void
   */
  public function __construct(StatementUpdate $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * addCondition
   *
   * @param string $condition
   * @param string $conjunction
   * @return void
   */
  public function addCondition(string $condition, string $conjunction = 'AND') : void
  {
    $this->conditions[] = [
      'condition' => $condition,
      'conjunction' => $conjunction,
    ];
  }
  
  /**
   * addConditionAdaptive
   *
   * @param array $conditions
   * 
   * @return void
   */
  public function addConditionAdaptive(array $conditions, string $conjunction = 'AND') : void
  {
    $dialect = $this->statement->queryBuilder->dialect;
    $condition = $dialect->resolveAdaptiveCondition($conditions);

    if ($condition === '') {
      return;
    }

    $this->addCondition($condition, $conjunction);
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    if (empty($this->conditions)) {
      $this->assembled = '';
      return;
    }

    $parts = [];
    foreach ($this->conditions as $index => $item) {
      $parts[] = $index === 0
        ? $item['condition']
        : $item['conjunction'] . ' ' . $item['condition'];
    }

    $this->assembled = 'WHERE ' . implode(' ', $parts);
  }
}
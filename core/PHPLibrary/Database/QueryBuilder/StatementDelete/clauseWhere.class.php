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

namespace core\PHPLibrary\Database\QueryBuilder\StatementDelete;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementDelete as StatementDelete;

final class ClauseWhere implements InterfaceClause
{
  private StatementDelete $statement;
  public string $condition = '';
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  mixed $statement
   * @return void
   */
  public function __construct(StatementDelete $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * set_condition
   *
   * @param  mixed $condition
   * @return void
   */
  public function addCondition(string $condition) : void
  {
    $this->condition = $condition;
  }
  
  /**
   * addConditionAdaptive
   *
   * @param array $conditions
   * 
   * @return void
   */
  public function addConditionAdaptive(array $conditions) : void
  {
    $CMSConfigurator = $this->statement->queryBuilder->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $condition = match ($CMSConfigDatabase['dms']) {
      DMS::MySQL => $conditions['mysql'] ?? '',
      DMS::PostgreSQL => $conditions['postgresql'] ?? '',
      default => ''
    };
    $this->condition = $condition;
  } 
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->assembled = !empty(trim($this->condition)) ? sprintf('WHERE %s', $this->condition) : '';
  }

}
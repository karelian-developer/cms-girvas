<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementSelect;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;

final class ClauseWhere implements InterfaceClause
{
  private StatementSelect $statement;
  public array $conditions = [];
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  mixed $statement
   * @return void
   */
  public function __construct(StatementSelect $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * addCondition
   *
   * @param string $condition
   * @param string $conjunction
   * 
   * @return void
   */
  public function addCondition(string $condition, string $conjunction = '') : void
  {
    $this->conditions[] = !empty($conjunction) ? $conjunction . ' ' . $condition : $condition;
  }
  
  /**
   * addConditionAdaptive
   *
   * @param array $conditions
   * @param string $conjunction
   * 
   * @return void
   */
  public function addConditionAdaptive(array $conditions, string $conjunction = '') : void
  {
    $CMSConfigurator = $this->statement->queryBuilder->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $condition = match ($CMSConfigDatabase['dms']) {
      DMS::MySQL => $conditions['mysql'] ?? '',
      DMS::PostgreSQL => $conditions['postgresql'] ?? '',
      default => ''
    };
    $this->conditions[] = !empty($conjunction) ? $conjunction . ' ' . $condition : $condition;
  } 
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly()
  {
    $this->assembled = !empty($this->conditions) ? sprintf('WHERE %s', implode(' ', $this->conditions)) : '';
  }

}
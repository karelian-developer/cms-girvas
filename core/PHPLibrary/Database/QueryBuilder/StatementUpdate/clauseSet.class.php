<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementUpdate;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate as StatementUpdate;

final class ClauseSet implements InterfaceClause
{
  private StatementUpdate $statement;
  private array $columns = [];
  private array $values = [];
  public array $tables;
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
   * Добавить значение столбца
   *
   * @param string $name
   * @param mixed $value
   * 
   * @return void
   */
  public function addColumn(string $name, mixed $value = null) : void
  {
    array_push($this->columns, $name);

    if ($value !== null) {
      $this->values[$name] = $value;
    }
  }

  /**
   * Добавить адаптивное значение столбца
   *
   * @param string $name
   * @param array $values
   * 
   * @return void
   */
  public function addColumnAdaptive(string $name, array $values = []) : void
  {
    $queryBuilder = $this->statement->queryBuilder;
    if (isset($values[strtolower($queryBuilder->DMS->name)])) {
      $this->columns[] = $name;

      if (!empty($values[strtolower($queryBuilder->DMS->name)])) {
        $this->values[$name] = $values[strtolower($queryBuilder->DMS->name)];
      }
    }
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    $queryArray = [];

    foreach ($this->columns as $name) {
      $value = $this->values[$name] ?? ':' . $name;

      $queryArray[] = match ($this->statement->queryBuilder->DMS) {
        DMS::MySQL => sprintf('`%s` = %s', $name, $value),
        DMS::PostgreSQL => sprintf('"%s" = %s', $name, $value),
      };
    }

    $this->assembled = count($queryArray) > 0 ? 'SET ' . implode(', ', $queryArray) : '';
  }
}
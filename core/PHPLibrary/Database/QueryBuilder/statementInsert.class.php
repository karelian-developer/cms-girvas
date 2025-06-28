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
use \core\PHPLibrary\Database\QueryBuilder\StatementInsert\ClauseReturning as ClauseReturning;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementInsert implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private array $columns = [];
  public string $tableName = '';
  public string $tablePrefix = '';
  public ClauseReturning|null $clauseReturning = null;
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

  public function setClauseReturning() : void
  {
    $this->clauseReturning = new ClauseReturning($this);
  }
  
  /**
   * Добавить значение столбца
   *
   * @param  mixed $columnName
   * @param  mixed $value
   * @return void
   */
  public function addColumn(string $columnName) : void
  {
    array_push($this->columns, $columnName);
  }
  
  /**
   * Назначить имя таблицы
   *
   * @param  string $name
   * @param  string $prefix
   * @return void
   */
  public function setTable(string $name, string $prefix = '') : void
  {
    $this->tableName = $name;
    $this->tablePrefix = $prefix;
  }
  
  /**
   * Получить наименование таблицы
   *
   * @return string
   */
  public function getTable() : string
  {
    $databaseConfigurations = $this->queryBuilder->CMSCore->configurator->get('database');
    
    $tableFullname = '';
    if (!is_null($databaseConfigurations)) {
      if ($databaseConfigurations['scheme'] !== '') {
        $tableFullname .= sprintf('%s.', $databaseConfigurations['scheme']);
      }

      if ($databaseConfigurations['prefix'] !== '' || $this->tablePrefix !== '') {
        $tablePrefix = $this->tablePrefix === '' ? $databaseConfigurations['prefix'] : $this->tablePrefix;
        $tableFullname .= $tablePrefix . '_';
      }
    }

    $tableFullname .= $this->tableName;

    return $tableFullname;
  }

  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $queryArray = [];

    $columnsValues = [];
    foreach ($this->columns as $index => $columnName) {
      if (!preg_match('/\"[a-z0-9_]+\"/i', $columnName)) {
        $this->columns[$index] = '"' . $columnName . '"';
      }

      array_push($columnsValues, ':' . $columnName);
    }

    array_push($queryArray, sprintf('(%s) VALUES (%s)', implode(', ', $this->columns), implode(', ', $columnsValues)));

    if ($this->clauseReturning !== null) {
      $this->clauseReturning->assembly();
      array_push($queryArray, $this->clauseReturning->assembled);
    }

    $this->assembled = sprintf('INSERT INTO %s %s;', $this->get_table(), implode(' ', $queryArray));
  }
}
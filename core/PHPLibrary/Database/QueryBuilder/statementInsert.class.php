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
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
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
   * @param  string $columnName
   * 
   * @return void
   */
  public function addColumn(string $name) : void
  {
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');

    $name = match ($CMSConfigDatabase['dms']) {
      CMSDMS::MySQL => '`' . $name . '`',
      CMSDMS::PostgreSQL => '"' . $name . '"'
    };

    $this->columns[] = $name;
  }
  
  /**
   * Назначить имя таблицы
   *
   * @param  string $name
   * @param  string $prefix
   * 
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
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');
    
    $tableFullname = '';
    if ($CMSConfigDatabase !== null) {
      if (
        $CMSConfigDatabase['scheme'] !== ''
        && $CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL
      ) {
        $tableFullname .= $CMSConfigDatabase['scheme'] . '.';
      }

      if ($CMSConfigDatabase['prefix'] !== '') {
        $tableFullname .= $CMSConfigDatabase['prefix'] . '_';
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
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');
    $queryArray = [];

    $columnsValues = [];
    foreach ($this->columns as $index => $columnName) {
      if (!preg_match('/\"[a-z0-9_]+\"/i', $columnName)) {
        $this->columns[$index] = match ($CMSConfigDatabase['dms']) {
          CMSDMS::MySQL => '`' . $columnName . '`',
          CMSDMS::PostgreSQL => '"' . $columnName . '"',
        };
      }

      $columnsValues[] = ':' . $columnName;
    }

    $queryArray[] = sprintf('(%s) VALUES (%s)', implode(', ', $this->columns), implode(', ', $columnsValues));

    $clausesToPrecess = $this->getClausesToProcess();
    foreach ($clausesToPrecess as $clause) {
      if ($clause !== null) {
        $clause->assembly();
        $queryArray[] = $clause->assembled;
      }
    }

    $this->assembled = sprintf('INSERT INTO %s %s;', $this->getTable(), implode(' ', $queryArray));
  }

  /**
   * Получение массива объектов предложений
   */
  private function getClausesToProcess() : array
  {
    return [
      $this->clauseReturning
    ];
  }
}
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

namespace core\PHPLibrary\Database\QueryBuilder;

use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
use \core\PHPLibrary\Database\QueryBuilder\Dialect as BaseDialect;
use \core\PHPLibrary\Database\QueryBuilder\StatementInsert\ClauseReturning as ClauseReturning;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementInsert implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private array $columns = [];
  public string $tableName = '';
  public string $tablePrefix = '';
  public ?ClauseReturning $clauseReturning = null;
  public string $assembled = '';
  private array $batchRows = [];

  /**
   * __construct
   *
   * @param  QueryBuilder $queryBuilder
   * @return void
   */
  public function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
  }

  /**
   * Установить выражение RETURNING
   * 
   * @return void
   */
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
   * Получить объект диалекта
   * 
   * @return BaseDialect
   */
  public function getDialect() : BaseDialect
  {
    return $this->queryBuilder->dialect;
  }
    
  /**
   * Получить наименование таблицы
   *
   * @return string
   */
  public function getTable() : string
  {
    $dialect = $this->queryBuilder->dialect;
    $databaseConfigurations = $this->queryBuilder->CMSCore->configurator->get('database');

    $tableFullname = '';

    if ($databaseConfigurations !== null) {
      if ($databaseConfigurations['scheme'] !== '') {
        $tableFullname .= $databaseConfigurations['scheme'] . '.';
      }

      if ($databaseConfigurations['prefix'] !== '' || $this->tablePrefix !== '') {
        $tablePrefix = $this->tablePrefix === '' ? $databaseConfigurations['prefix'] : $this->tablePrefix;
        $tableFullname .= $tablePrefix . '_';
      }
    }

    $tableFullname .= $this->tableName;

    // Экранируем каждый сегмент отдельно
    $segments = explode('.', $tableFullname);
    $quotedSegments = array_map(
      fn(string $segment) => $dialect->quoteIdentifier($segment),
      $segments
    );

    return implode('.', $quotedSegments);
  }

  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $dialect = $this->queryBuilder->dialect;
    $queryArray = [];

    if (!empty($this->batchRows)) {
      $columns = array_keys($this->batchRows[0]);

      $quotedColumns = [];
      foreach ($columns as $columnName) {
        $quotedColumns[] = $dialect->quoteIdentifier($columnName);
      }

      $valuePlaceholders = [];
      foreach ($this->batchRows as $rowIndex => $row) {
        $rowPlaceholders = [];
        foreach ($columns as $columnName) {
          $rowPlaceholders[] = ':' . $columnName . '_' . $rowIndex;
        }
        $valuePlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
      }

      $queryArray[] = sprintf(
        '(%s) VALUES %s',
        implode(', ', $quotedColumns),
        implode(', ', $valuePlaceholders)
      );
    } else {
      $columnsValues = [];
      foreach ($this->columns as $index => $columnName) {
        if (!preg_match('/^["`].+["`]$/', $columnName)) {
          $this->columns[$index] = $dialect->quoteIdentifier($columnName);
        }
        $columnsValues[] = ':' . $columnName;
      }
      $queryArray[] = sprintf(
        '(%s) VALUES (%s)',
        implode(', ', $this->columns),
        implode(', ', $columnsValues)
      );
    }

    $clausesToProcess = $this->getClausesToProcess();
    foreach ($clausesToProcess as $clause) {
      if ($clause !== null) {
        $clause->assembly();
        $queryArray[] = $clause->assembled;
      }
    }

    $this->assembled = sprintf(
      'INSERT INTO %s %s;',
      $this->getTable(),
      implode(' ', $queryArray)
    );
  }

  /**
   * Получение массива объектов предложений
   */
  private function getClausesToProcess() : array
  {
    $dialect = $this->queryBuilder->dialect;

    if (!$dialect->supportsInsertReturning()) {
      return [];
    }

    return array_filter([
      $this->clauseReturning,
    ]);
  }
}
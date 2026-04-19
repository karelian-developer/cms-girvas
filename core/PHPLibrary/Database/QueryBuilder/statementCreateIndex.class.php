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
use \core\PHPLibrary\Database\IndexType as IndexType;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementCreateIndex implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private string $indexName = '';
  private string $tableName = '';
  private array $columns = [];
  private IndexType $indexType = IndexType::BTREE;
  private bool $unique = false;
  private bool $concurrently = false;
  private bool $ifNotExists = false;
  private string $whereCondition = '';
  private ?string $expression = null;
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param QueryBuilder $queryBuilder
   * @return void
   */
  public function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
  }
  
  /**
   * Установить имя индекса
   *
   * @param string $name
   * @return void
   */
  public function setIndexName(string $name) : void
  {
    $this->indexName = $name;
  }
  
  /**
   * Установить имя таблицы
   *
   * @param string $name
   * @param string $prefix
   * @return void
   */
  public function setTableName(string $name, string $prefix = '') : void
  {
    $databaseConfig = $this->queryBuilder->CMSCore->configurator->get('database');
    
    $tableFullname = '';
    if ($databaseConfig['scheme'] !== '') {
      $tableFullname .= $databaseConfig['scheme'] . '.';
    }
    
    $tablePrefix = $prefix === '' ? $databaseConfig['prefix'] : $prefix;
    if ($tablePrefix !== '') {
      $tableFullname .= $tablePrefix . '_';
    }
    
    $tableFullname .= $name;
    $this->tableName = $tableFullname;
  }
  
  /**
   * Добавить колонку в индекс
   *
   * @param string $column
   * @param string $order
   * @return void
   */
  public function addColumn(string $column, string $order = 'ASC') : void
  {
    $this->columns[] = [
      'name' => $column,
      'order' => strtoupper($order)
    ];
  }
  
  /**
   * Установить тип индекса
   *
   * @param IndexType $type
   * @return void
   */
  public function setIndexType(IndexType $type) : void
  {
    $this->indexType = $type;
  }
  
  /**
   * Установить уникальность индекса
   *
   * @param bool $value
   * @return void
   */
  public function setUnique(bool $value = true) : void
  {
    $this->unique = $value;
  }
  
  /**
   * Установить конкурентное создание (только PostgreSQL)
   *
   * @param bool $value
   * @return void
   */
  public function setConcurrently(bool $value = true) : void
  {
    $this->concurrently = $value;
  }
  
  /**
   * Установить IF NOT EXISTS
   *
   * @param bool $value
   * @return void
   */
  public function setIfNotExists(bool $value = true) : void
  {
    $this->ifNotExists = $value;
  }
  
  /**
   * Установить WHERE-условие для частичного индекса (только PostgreSQL)
   *
   * @param string $condition
   * @return void
   */
  public function setWhereCondition(string $condition) : void
  {
    $this->whereCondition = $condition;
  }
  
  /**
   * Установить выражение для индекса (вместо колонок)
   *
   * @param string $expression
   * @return void
   */
  public function setExpression(string $expression) : void
  {
    $this->expression = $expression;
  }
  
  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');
    $parts = [];
    
    $parts[] = 'CREATE';
    
    if ($this->unique) {
      $parts[] = 'UNIQUE';
    }
    
    $parts[] = 'INDEX';
    
    if ($this->concurrently && $CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      $parts[] = 'CONCURRENTLY';
    }
    
    if ($this->ifNotExists && $CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      $parts[] = 'IF NOT EXISTS';
    }
    
    $parts[] = $this->indexName;
    $parts[] = 'ON';
    $parts[] = $this->tableName;
    
    // Тип индекса
    if ($CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      $parts[] = 'USING ' . $this->indexType->value;
    }
    
    // Колонки или выражение
    if ($this->expression !== null) {
      $parts[] = '(' . $this->expression . ')';
    } else {
      $columnDefinitions = [];
      foreach ($this->columns as $column) {
        $colDef = match ($CMSConfigDatabase['dms']) {
          CMSDMS::MySQL => '`' . $column['name'] . '`',
          CMSDMS::PostgreSQL => '"' . $column['name'] . '"'
        };
        
        if ($this->indexType === IndexType::BTREE && $column['order'] !== 'ASC') {
          $colDef .= ' ' . $column['order'];
        }
        
        $columnDefinitions[] = $colDef;
      }
      $parts[] = '(' . implode(', ', $columnDefinitions) . ')';
    }
    
    // WHERE для частичного индекса (PostgreSQL)
    if (!empty($this->whereCondition) && $CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      $parts[] = 'WHERE ' . $this->whereCondition;
    }
    
    $this->assembled = implode(' ', $parts) . ';';
  }
}
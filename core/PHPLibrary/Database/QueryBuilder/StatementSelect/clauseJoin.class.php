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

namespace core\PHPLibrary\Database\QueryBuilder\StatementSelect;

use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseFrom\Table as Table;

final class ClauseJoin implements InterfaceClause
{
  public const JOIN_TYPE_INNER = 'INNER';
  public const JOIN_TYPE_LEFT = 'LEFT';
  public const JOIN_TYPE_RIGHT = 'RIGHT';
  public const JOIN_TYPE_FULL = 'FULL';

  private StatementSelect $statement;
  private array $joins = [];
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param StatementSelect $statement
   * @return void
   */
  public function __construct(StatementSelect $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * Добавить JOIN
   *
   * @param string $type Тип JOIN (INNER, LEFT, RIGHT, FULL)
   * @param string $tableName Имя присоединяемой таблицы
   * @param string $condition Условие соединения
   * @param string $tablePrefix Префикс таблицы
   * @param string $alias Алиас таблицы
   * 
   * @return void
   */
  public function addJoin(
    string $type,
    string $tableName,
    string $condition,
    string $tablePrefix = '',
    string $alias = ''
  ) : void {
    $this->joins[] = [
      'type' => strtoupper($type),
      'table' => new Table($tableName, $tablePrefix),
      'condition' => $condition,
      'alias' => $alias
    ];
  }
  
  /**
   * Добавить INNER JOIN
   *
   * @param string $tableName
   * @param string $condition
   * @param string $tablePrefix
   * @param string $alias
   * 
   * @return void
   */
  public function addInnerJoin(
    string $tableName,
    string $condition,
    string $tablePrefix = '',
    string $alias = ''
  ) : void {
    $this->addJoin(self::JOIN_TYPE_INNER, $tableName, $condition, $tablePrefix, $alias);
  }
  
  /**
   * Добавить LEFT JOIN
   *
   * @param string $tableName
   * @param string $condition
   * @param string $tablePrefix
   * @param string $alias
   * 
   * @return void
   */
  public function addLeftJoin(
    string $tableName,
    string $condition,
    string $tablePrefix = '',
    string $alias = ''
  ) : void {
    $this->addJoin(self::JOIN_TYPE_LEFT, $tableName, $condition, $tablePrefix, $alias);
  }
  
  /**
   * Добавить RIGHT JOIN
   *
   * @param string $tableName
   * @param string $condition
   * @param string $tablePrefix
   * @param string $alias
   * 
   * @return void
   */
  public function addRightJoin(
    string $tableName,
    string $condition,
    string $tablePrefix = '',
    string $alias = ''
  ) : void {
    $this->addJoin(self::JOIN_TYPE_RIGHT, $tableName, $condition, $tablePrefix, $alias);
  }

  /**
   * Добавить адаптивное условие JOIN
   *
   * @param string $type Тип JOIN
   * @param string $tableName Имя таблицы
   * @param array $conditions Адаптивные условия ['mysql' => '...', 'postgresql' => '...']
   * @param string $tablePrefix Префикс таблицы
   * @param string $alias Алиас таблицы
   * 
   * @return void
   */
  public function addJoinAdaptive(
    string $type,
    string $tableName,
    array $conditions,
    string $tablePrefix = '',
    string $alias = ''
  ) : void {
    $CMSConfigurator = $this->statement->queryBuilder->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $condition = match ($CMSConfigDatabase['dms']) {
      \core\PHPLibrary\Database\DatabaseManagementSystem::MySQL => $conditions['mysql'] ?? '',
      \core\PHPLibrary\Database\DatabaseManagementSystem::PostgreSQL => $conditions['postgresql'] ?? '',
      default => ''
    };

    $this->addJoin($type, $tableName, $condition, $tablePrefix, $alias);
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    if (empty($this->joins)) {
      $this->assembled = '';
      return;
    }

    $databaseConfigurations = $this->statement->queryBuilder->CMSCore->configurator->get('database');
    $joinStrings = [];

    foreach ($this->joins as $join) {
      $table = $join['table'];
      $tableFullname = '';

      if (!is_null($databaseConfigurations)) {
        if ($databaseConfigurations['scheme'] !== '') {
          $tableFullname .= $databaseConfigurations['scheme'] . '.';
        }

        if ($databaseConfigurations['prefix'] !== '' || $table->getPrefix() !== '') {
          $tablePrefix = $table->getPrefix() === '' ? $databaseConfigurations['prefix'] : $table->getPrefix();
          $tableFullname .= $tablePrefix . '_';
        }
      }

      $tableFullname .= $table->getName();

      if (!empty($join['alias'])) {
        $tableFullname .= ' AS ' . $join['alias'];
      }

      $joinStrings[] = sprintf(
        '%s JOIN %s ON %s',
        $join['type'],
        $tableFullname,
        $join['condition']
      );
    }

    $this->assembled = implode(' ', $joinStrings);
  }
}
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
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\ClauseSet as ClauseSet;
use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\ClauseWhere as ClauseWhere;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementUpdate implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private array $columns = [];
  public ?ClauseSet $clauseSet = null;
  public ?ClauseWhere $clauseWhere = null;
  public string $tableName = '';
  public string $tablePrefix = '';
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
   * Установить предложение SET
   *
   * @return void
   */
  public function setClauseSet() : void
  {
    $this->clauseSet = new ClauseSet($this);
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
   * Получить имя таблицы
   *
   * @return string
   */
  public function getTable() : string
  {
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

    $clausesToPrecess = $this->getClausesToProcess();
    foreach ($clausesToPrecess as $clause) {
      if ($clause !== null) {
        $clause->assembly();
        $queryArray[] = $clause->assembled;
      }
    }

    $this->assembled = sprintf('UPDATE %s %s;', $this->getTable(), implode(' ', $queryArray));
  }

  /**
   * Получение массива объектов предложений
   */
  private function getClausesToProcess() : array
  {
    return [
      $this->clauseSet,
      $this->clauseWhere
    ];
  }
}
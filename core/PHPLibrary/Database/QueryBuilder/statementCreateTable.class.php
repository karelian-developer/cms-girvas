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
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementCreateTable implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private string $tableName = '';
  private bool $checkExists = false;
  private array $columns = [];
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
   * Установить имя создаваемой таблицы
   *
   * @param  string $name
   * @return void
   */
  public function setTableName(string $name) : void
  {
    $this->tableName = $name;
  }

  /**
   * Получить имя создаваемой таблицы
   *
   * @return string
   */
  public function getTableName() : string
  {
    $databaseConfigurations = $this->queryBuilder->CMSCore->configurator->get('database');
    
    $tableFullname = '';
    if ($databaseConfigurations !== null) {
      if ($databaseConfigurations['scheme'] !== '') {
        $tableFullname .= $databaseConfigurations['scheme'] . '.';
      }

      if ($databaseConfigurations['prefix'] !== '') {
        $tableFullname .= $databaseConfigurations['prefix'] . '_';
      }
    }

    $tableFullname .= $this->tableName;
    return $tableFullname;
  }

  public function setCheckExists(bool $value) : void
  {
    $this->checkExists = $value;
  }
  
  /**
   * Установить выборку для SELECT
   *
   * @param  mixed $selection
   * @return void
   */
  public function addColumn(string $name, string $type, string $constraint = '') : void
  {
    $array = [];
    $array[] = $name;
    $array[] = $type;
    $array[] = $constraint;
    
    $this->columns[] = implode(' ', $array);

    unset($array);
  }
  
  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void {
    $ifNotExists = $this->checkExists ? 'IF NOT EXISTS' : '';
    $this->assembled = sprintf('CREATE TABLE %s "%s" (%s);', $ifNotExists, $this->getTableName(), implode(', ', $this->columns));
  }

}
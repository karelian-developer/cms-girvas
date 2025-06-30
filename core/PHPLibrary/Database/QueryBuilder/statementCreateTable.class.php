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

  public function setCheckExists(bool $value) : void
  {
    $this->checkExists = $value;
  }
  
  /**
   * Добавить колонку
   *
   * @param  mixed $selection
   * @return void
   */
  public function addColumn(string $name, string $type, string $constraint = '') : void
  {
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');

    $array = [];
    $array[] = match ($CMSConfigDatabase['dms']) {
      CMSDMS::MySQL => '`' . $name '`',
      CMSDMS::PostgreSQL => '"' . $name '"'
    };
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
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');
    $ifNotExists = $this->checkExists ? 'IF NOT EXISTS' : '';

    $this->assembled = match ($CMSConfigDatabase['dms']) {
      CMSDMS::MySQL => sprintf('CREATE TABLE %s `%s` (%s);', $ifNotExists, $this->getTableName(), implode(', ', $this->columns)),
      CMSDMS::PostgreSQL => sprintf('CREATE TABLE %s "%s" (%s);', $ifNotExists, $this->getTableName(), implode(', ', $this->columns))
    };
  }

}
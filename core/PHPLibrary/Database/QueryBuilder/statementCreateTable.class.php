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
    $CMSConfigDatabaseScheme = trim($CMSConfigDatabase['scheme']);
    $CMSConfigDatabasePrefix = trim($CMSConfigDatabase['prefix']);

    $tableFullname = '';
    if ($CMSConfigDatabase !== null) {
      if ($CMSConfigDatabasePrefix !== '') {
        $tableFullname .= $CMSConfigDatabasePrefix . '_';
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
      CMSDMS::MySQL => '`' . $name . '`',
      CMSDMS::PostgreSQL => '"' . $name . '"'
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
    $CMSConfigDatabaseScheme = trim($CMSConfigDatabase['scheme']);
    $ifNotExists = $this->checkExists ? 'IF NOT EXISTS' : '';

    $this->assembled = match ($CMSConfigDatabase['dms']) {
      CMSDMS::MySQL => sprintf('CREATE TABLE %s `%s` (%s);', $ifNotExists, $this->getTableName(), implode(', ', $this->columns)),
      CMSDMS::PostgreSQL => sprintf('CREATE TABLE %s %s."%s" (%s);', $ifNotExists, $CMSConfigDatabaseScheme, $this->getTableName(), implode(', ', $this->columns))
    };
  }
}
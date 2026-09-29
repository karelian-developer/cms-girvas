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
   * @param  QueryBuilder $queryBuilder
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
    $dialect = $this->queryBuilder->dialect;
    $CMSConfigDatabase = $this->queryBuilder->CMSCore->configurator->get('database');

    $prefix = trim($CMSConfigDatabase['prefix']);
    $scheme = trim($CMSConfigDatabase['scheme']);

    $tableFullname = '';

    if ($scheme !== '') {
      $tableFullname .= $scheme . '.';
    }

    if ($prefix !== '') {
      $tableFullname .= $prefix . '_';
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
   * Проверять наличие базы данных
   *
   * @param  bool $value
   * @return void
   */
  public function setCheckExists(bool $value) : void
  {
    $this->checkExists = $value;
  }
  
  /**
   * Добавить колонку
   *
   * @param  string $name        Имя колонки (логическое или физическое)
   * @param  string $type        Логический или физический тип
   * @param  string $constraint  Ограничения (NOT NULL, DEFAULT, PRIMARY KEY...)
   * @return void
   */
  public function addColumn(string $name, string $type, string $constraint = '') : void
  {
    $dialect = $this->queryBuilder->dialect;

    $physicalType = $dialect->isLogicalType($type)
      ? $dialect->resolve($type)
      : $type;

    if ($dialect->isLogicalType($type) && $dialect->constraintHasDefault($constraint)) {
      if (!$dialect->supportsDefault($type)) {
        throw new \LogicException(sprintf(
          'DEFAULT is not allowed for logical type "%s" on %s',
          $type,
          $dialect->getDMS()->getString()
        ));
      }
    }

    $quotedName = $dialect->quoteIdentifier($name);

    $this->columns[] = trim(sprintf('%s %s %s', $quotedName, $physicalType, $constraint));
  }
  
  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $ifNotExists = $this->checkExists ? 'IF NOT EXISTS' : '';

    $this->assembled = sprintf(
      'CREATE TABLE %s %s (%s);',
      $ifNotExists,
      $this->getTableName(),
      implode(', ', $this->columns)
    );
  }
}
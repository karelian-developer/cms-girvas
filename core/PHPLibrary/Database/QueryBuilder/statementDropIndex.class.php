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
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementDropIndex implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private string $indexName = '';
  private bool $concurrently = false;
  private bool $ifExists = false;
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
   * Установить конкурентное удаление (только PostgreSQL)
   *
   * @param bool $value
   * @return void
   */
  public function setConcurrently(bool $value = true) : void
  {
    $this->concurrently = $value;
  }

  /**
   * Установить IF EXISTS
   *
   * @param bool $value
   * @return void
   */
  public function setIfExists(bool $value = true) : void
  {
    $this->ifExists = $value;
  }

  /**
   * Сборка SQL-запроса
   *
   * @return void
   */
  public function assembly() : void
  {
    $dialect = $this->queryBuilder->dialect;

    $parts = [];

    $parts[] = 'DROP INDEX';

    if ($this->concurrently && $dialect->supportsDropIndexConcurrently()) {
      $parts[] = 'CONCURRENTLY';
    }

    if ($this->ifExists && $dialect->supportsDropIndexIfExists()) {
      $parts[] = 'IF EXISTS';
    }

    $parts[] = $dialect->quoteIdentifier($this->indexName);

    $this->assembled = implode(' ', $parts) . ';';
  }
}
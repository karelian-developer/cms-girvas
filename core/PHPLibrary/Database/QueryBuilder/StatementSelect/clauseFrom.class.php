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

final class ClauseFrom implements InterfaceClause
{
  private StatementSelect $statement;
  public array $tables;
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  mixed $statement
   * @return void
   */
  public function __construct(StatementSelect $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * addTable
   *
   * @param  mixed $name
   * @return void
   */
  public function addTable(string $name, string $prefix = '') : void
  {
    $this->tables[$name] = new Table($name, $prefix);
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    $queryArray = [];

    $databaseConfigurations = $this->statement->queryBuilder->CMSCore->configurator->get('database');

    foreach ($this->tables as $table) {
      $tableFullname = '';

      if (!is_null($databaseConfigurations)) {
        if ($databaseConfigurations['scheme'] !== '') {
          $tableFullname .= sprintf('%s.', $databaseConfigurations['scheme']);
        }

        if ($databaseConfigurations['prefix'] !== '' || $table->getPrefix() !== '') {
          $tablePrefix = $table->getPrefix() === '' ? $databaseConfigurations['prefix'] : $table->getPrefix();
          $tableFullname .= $tablePrefix . '_';
        }
      }

      $tableFullname .= $table->getName();
      array_push($queryArray, $tableFullname);
    }

    $this->assembled = count($this->tables) > 0 ? sprintf('FROM %s', implode(', ', $queryArray)) : '';
  }
}
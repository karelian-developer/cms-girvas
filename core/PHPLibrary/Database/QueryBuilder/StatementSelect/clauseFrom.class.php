<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
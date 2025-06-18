<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder {
  use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
  use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\ClauseSet as ClauseSet;
  use \core\PHPLibrary\Database\QueryBuilder\StatementUpdate\ClauseWhere as ClauseWhere;
  use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

  final class StatementUpdate implements InterfaceStatement {
    public QueryBuilder $queryBuilder;
    private array $columns = [];
    public ClauseSet|null $clauseSet = null;
    public ClauseWhere|null $clauseWhere = null;
    public string $tableName = '';
    public string $tablePrefix = '';
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  mixed $queryBuilder
     * @return void
     */
    public function __construct(QueryBuilder $queryBuilder) {
      $this->queryBuilder = $queryBuilder;
    }
    
    /**
     * Установить предложение SET
     *
     * @return void
     */
    public function set_clause_set() : void {
      $this->clauseSet = new ClauseSet($this);
    }
    
    /**
     * Установить предложение WHERE
     *
     * @return void
     */
    public function set_clause_where() : void {
      $this->clauseWhere = new ClauseWhere($this);
    }
    
    /**
     * Назначить имя таблицы
     *
     * @param  string $name
     * @param  string $prefix
     * @return void
     */
    public function set_table(string $name, string $prefix = '') : void {
      $this->tableName = $name;
      $this->tablePrefix = $prefix;
    }
    
    /**
     * Получить имя таблицы
     *
     * @return string
     */
    public function get_table() : string {
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
    public function assembly() : void {
      $queryArray = [];

      if ($this->clauseSet !== null) {
        $this->clauseSet->assembly();
        array_push($queryArray, $this->clauseSet->assembled);
      }

      if ($this->clauseWhere !== null) {
        $this->clauseWhere->assembly();
        array_push($queryArray, $this->clauseWhere->assembled);
      }

      $this->assembled = sprintf('UPDATE %s %s;', $this->get_table(), implode(' ', $queryArray));
    }
  }

}

?>
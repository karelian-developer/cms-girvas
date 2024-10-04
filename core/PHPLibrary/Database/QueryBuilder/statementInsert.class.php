<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder {
  use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
  use \core\PHPLibrary\Database\QueryBuilder\StatementInsert\ClauseReturning as ClauseReturning;
  use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

  final class StatementInsert implements InterfaceStatement {
    public QueryBuilder $query_builder;
    private array $columns = [];
    public string $table_name = '';
    public string $table_prefix = '';
    public ClauseReturning|null $clause_returning = null;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  mixed $query_builder
     * @return void
     */
    public function __construct(QueryBuilder $query_builder) {
      $this->query_builder = $query_builder;
    }

    public function set_clause_returning() : void {
      $this->clause_returning = new ClauseReturning($this);
    }
    
    /**
     * Добавить значение столбца
     *
     * @param  mixed $column_name
     * @param  mixed $value
     * @return void
     */
    public function add_column(string $column_name) : void {
      array_push($this->columns, $column_name);
    }
    
    /**
     * Назначить имя таблицы
     *
     * @param  string $name
     * @param  string $prefix
     * @return void
     */
    public function set_table(string $name, string $prefix = '') : void {
      $this->table_name = $name;
      $this->table_prefix = $prefix;
    }
    
    /**
     * Получить наименование таблицы
     *
     * @return string
     */
    public function get_table() : string {
      $database_configurations = $this->query_builder->system_core->configurator->get('database');
      
      $table_fullname = '';
      if (!is_null($database_configurations)) {
        if ($database_configurations['scheme'] != '') {
          $table_fullname .= sprintf('%s.', $database_configurations['scheme']);
        }

        if ($database_configurations['prefix'] != '' || $this->table_prefix != '') {
          $table_prefix = ($this->table_prefix == '') ? $database_configurations['prefix'] : $this->table_prefix;
          $table_fullname .= sprintf('%s_', $table_prefix);
        }
      }

      $table_fullname .= $this->table_name;

      return $table_fullname;
    }

    /**
     * Сборка SQL-запроса
     *
     * @return void
     */
    public function assembly() : void {
      $query_array = [];

      $columns_values = [];
      foreach ($this->columns as $column_name) {
        array_push($columns_values, ':' . $column_name);
      }

      array_push($query_array, sprintf('(%s) VALUES (%s)', implode(', ', $this->columns), implode(', ', $columns_values)));

      if (!is_null($this->clause_returning)) {
        $this->clause_returning->assembly();
        array_push($query_array, $this->clause_returning->assembled);
      }

      $this->assembled = sprintf('INSERT INTO %s %s;', $this->get_table(), implode(' ', $query_array));
    }
  }

}

?>
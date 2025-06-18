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
  use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

  final class StatementCreateTable implements InterfaceStatement {
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
    public function __construct(QueryBuilder $queryBuilder) {
      $this->query_builder = $queryBuilder;
    }

    /**
     * Установить имя создаваемой таблицы
     *
     * @param  string $name
     * @return void
     */
    public function set_table_name(string $name) : void {
      $this->table_name = $name;
    }

    /**
     * Получить имя создаваемой таблицы
     *
     * @return string
     */
    public function get_table_name() : string {
      $databaseConfigurations = $this->query_builder->system_core->configurator->get('database');
      
      $tableFullname = '';
      if (!is_null($databaseConfigurations)) {
        if ($databaseConfigurations['scheme'] != '') {
          $tableFullname .= sprintf('%s.', $databaseConfigurations['scheme']);
        }
        if ($databaseConfigurations['prefix'] != '') {
          $tableFullname .= sprintf('%s_', $databaseConfigurations['prefix']);
        }
      }

      $tableFullname .= $this->table_name;
      return $tableFullname;
    }

    public function set_check_exists(bool $value) : void {
      $this->checkExists = $value;
    }
    
    /**
     * Установить выборку для SELECT
     *
     * @param  mixed $selection
     * @return void
     */
    public function add_column(string $name, string $type, string $constraint = '') : void {
      $array = [];
      array_push($array, $name);
      array_push($array, $type);
      array_push($array, $constraint);

      array_push($this->columns, implode(' ', $array));

      unset($array);
    }
    
    /**
     * Сборка SQL-запроса
     *
     * @return void
     */
    public function assembly() : void {
      $ifNotExists = ($this->checkExists) ? 'IF NOT EXISTS' : '';
      $this->assembled = sprintf('CREATE TABLE %s %s (%s);', $ifNotExists, $this->get_table_name(), implode(', ', $this->columns));
    }

  }

}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementDelete {
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\InterfaceClause as InterfaceClause;
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete as StatementDelete;
  use \core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom\Table as Table;

  final class ClauseFrom implements InterfaceClause {
    private StatementDelete $statement;
    public array $tables;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  mixed $statement
     * @return void
     */
    public function __construct(StatementDelete $statement) {
      $this->statement = $statement;
    }
    
    /**
     * add_table
     *
     * @param  mixed $table_name
     * @return void
     */
    public function add_table(string $table_name) : void {
      $this->tables[$table_name] = new Table($table_name);
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      $query_array = [];

      $database_configurations = $this->statement->query_builder->system_core->configurator->get('database');

      foreach ($this->tables as $table) {
        $table_fullname = '';
        
        if (!is_null($database_configurations)) {
          if ($database_configurations['scheme'] != '') {
            $table_fullname .= sprintf('%s.', $database_configurations['scheme']);
          }

          if ($database_configurations['prefix'] != '' || $table->get_prefix() != '') {
            $table_prefix = ($table->get_prefix() == '') ? $database_configurations['prefix'] : $table->get_prefix();
            $table_fullname .= sprintf('%s_', $table_prefix);
          }
        }

        $table_fullname .= $table->get_name();
        array_push($query_array, $table_fullname);
      }

      if (count($this->tables) > 0) {
        $this->assembled = sprintf('FROM %s', implode(', ', $query_array));
      } else {
        $this->assembled =  '';
      }
    }

  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
     * @param  mixed $name
     * @return void
     */
    public function add_table(string $name, string $prefix = '') : void {
      $this->tables[$name] = new Table($name, $prefix);
    }
    
    /**
     * assembly
     *
     * @return void
     */
    public function assembly() {
      $queryArray = [];

      $databaseConfigurations = $this->statement->queryBuilder->CMSCore->configurator->get('database');

      foreach ($this->tables as $table) {
        $tableFullname = '';
        
        if (!is_null($databaseConfigurations)) {
          if ($databaseConfigurations['scheme'] != '') {
            $tableFullname .= sprintf('%s.', $databaseConfigurations['scheme']);
          }

          if ($databaseConfigurations['prefix'] != '' || $table->get_prefix() != '') {
            $tablePrefix = ($table->get_prefix() == '') ? $databaseConfigurations['prefix'] : $table->get_prefix();
            $tableFullname .= sprintf('%s_', $tablePrefix);
          }
        }

        $tableFullname .= $table->get_name();
        array_push($queryArray, $tableFullname);
      }

      if (count($this->tables) > 0) {
        $this->assembled = sprintf('FROM %s', implode(', ', $queryArray));
      } else {
        $this->assembled =  '';
      }
    }

  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Entry {

  final class Database {
    private \core\PHPLibrary\Database $database;
    private \core\PHPLibrary\Entry $entry;
    
    /**
     * __construct
     *
     * @param  mixed $database
     * @param  mixed $entry
     * @return void
     */
    public function __construct(\core\PHPLibrary\Database $database, \core\PHPLibrary\Entry $entry) {
      $this->database = $database;
      $this->entry = $entry;
    }

    private function get_entry_id() : int {
      return $this->entry->get_id();
    }

    public function get_data(string|array $columns) : string|array|null {
      /** @var string $database_query SQL-запрос */
      $database_query = '';
      /** @var EnumDatabaseManagementSystem $database_management_system */
      $database_management_system = $this->database->get_database_management_system();
      switch ($database_management_system->value) {
        case 'mysql': $database_query = $this->database->get_file_sql('Entry/get.mysql.sql'); break;
        case 'pgsql': $database_query = $this->database->get_file_sql('Entry/get.pgsql.sql'); break;
      }

      /** @var string $database_query SQL-запрос (переопределение) */
      $database_query = (is_string($columns)) ? sprintf($database_query, $columns) : sprintf($database_query, implode(', ', $columns));
      $entry_id = $this->get_entry_id();

      $this->database->prepare($database_query);
      $this->database->bindParam(':id', $entry_id, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
			if ($result) {
        return (is_string($columns)) ? $result[$columns] : $result;
      }

      return null;
    }

    public function exists() : bool {
      /** @var string $database_query SQL-запрос */
      $database_query = '';
      /** @var EnumDatabaseManagementSystem $database_management_system */
      $database_management_system = $this->database->get_database_management_system();
      switch ($database_management_system->value) {
        case 'mysql': $database_query = $this->database->get_file_sql('Entry/exists.mysql.sql'); break;
        case 'pgsql': $database_query = $this->database->get_file_sql('Entry/exists.pgsql.sql'); break;
      }
      
      $entry_id = $this->get_entry_id();

      $this->database->prepare($database_query);
      $this->database->bindParam(':id', $entry_id, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
			return ($result) ? $result['exists'] : false;
    }

  }

}

?>
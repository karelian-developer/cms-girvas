<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
      /** @var string $databaseQuery SQL-запрос */
      $databaseQuery = '';
      /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
      $databaseManagementSystem = $this->database->get_database_management_system();
      $databaseQuery = match ($databaseManagementSystem->value) {
        'mysql' => $this->database->get_file_sql('Entry/get.mysql.sql'),
        'pgsql' => $this->database->get_file_sql('Entry/get.pgsql.sql'),
      };

      /** @var string $databaseQuery SQL-запрос (переопределение) */
      $databaseQuery = is_string($columns) ? sprintf($databaseQuery, $columns) : sprintf($databaseQuery, implode(', ', $columns));
      $entryID = $this->get_entry_id();

      $this->database->prepare($databaseQuery);
      $this->database->bindParam(':id', $entryID, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
			if ($result) {
        return is_string($columns) ? $result[$columns] : $result;
      }

      return null;
    }

    public function exists() : bool {
      /** @var string $databaseQuery SQL-запрос */
      $databaseQuery = '';
      /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
      $databaseManagementSystem = $this->database->get_database_management_system();

      $databaseQuery = match ($databaseManagementSystem->value) {
        'mysql' => $this->database->get_file_sql('Entry/exists.mysql.sql'),
        'pgsql' => $this->database->get_file_sql('Entry/exists.pgsql.sql'),
      };
      
      $entryID = $this->get_entry_id();

      $this->database->prepare($databaseQuery);
      $this->database->bindParam(':id', $entryID, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
			return ($result) ? $result['exists'] : false;
    }

  }

}

?>
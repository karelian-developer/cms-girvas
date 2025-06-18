<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Entries {

  final class Database {
    private \core\PHPLibrary\Database $database;
    private \core\PHPLibrary\Entries $entries;
    private mixed $data;
    private array $conditions = [];
    private int $limit = 100;
    private array $selectColumns = [];
    
    /**
     * __construct
     *
     * @param  mixed $database
     * @param  mixed $entries
     * @return void
     */
    public function __construct(\core\PHPLibrary\Database $database) {
      $this->database = $database;
    }

    public function get() : array {
      /** @var string $databaseQuery SQL-запрос */
      $databaseQuery = '';
      /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
      $databaseManagementSystem = $this->database->get_database_management_system();
      $databaseQuery = match ($databaseManagementSystem->value) {
        'mysql' => $this->database->get_file_sql('Entry/get.mysql.sql'),
        'pgsql' => $this->database->get_file_sql('Entry/get.pgsql.sql')
      };

      /** @var string $databaseQuery SQL-запрос (переопределение) */
      $databaseQuery = sprintf($databaseQuery, implode(', ', $this->selectColumns), implode(' AND ', $this->conditions), $this->limit);

      $this->database->prepare($databaseQuery);
      $this->database->bindParam(':categoryID', $categoryID, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
			return count($result) > 0 ? $result : [];
    }
  }
}
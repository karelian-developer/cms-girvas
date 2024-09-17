<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Entries {

  final class Database {
    private \core\PHPLibrary\Database $database;
    private \core\PHPLibrary\Entries $entries;
    private mixed $data;
    private array $conditions = [];
    private int $limit = 100;
    private array $select_columns = [];
    
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
      /** @var string $database_query SQL-запрос */
      $database_query = '';
      /** @var EnumDatabaseManagementSystem $database_management_system */
      $database_management_system = $this->database->get_database_management_system();
      switch ($database_management_system->value) {
        case 'mysql': $database_query = $this->database->get_file_sql('Entries/get.mysql.sql'); break;
        case 'pgsql': $database_query = $this->database->get_file_sql('Entries/get.pgsql.sql'); break;
      }

      /** @var string $database_query SQL-запрос (переопределение) */
      $database_query = sprintf($database_query, implode(', ', $this->select_columns), implode(' AND ', $this->conditions), $this->limit);

      $this->database->prepare($database_query);
      $this->database->bindParam(':category_id', $category_id, \PDO::PARAM_INT);
			$this->database->execute();

      $result = $database_query->fetchAll(\PDO::FETCH_ASSOC);
			return (count($result) > 0) ? $result : [];
    }
  }
}
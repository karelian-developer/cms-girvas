<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \PDOException as PDOException;

  if (!defined('IS_NOT_HACKED')) {
		die('Unauthorized access attempt detected!');
	}

  #[\AllowDynamicProperties]
  final class Report {
    public const REPORT_TYPE_ID_AP_ENTRY_CREATED = 11000000;
    public const REPORT_TYPE_ID_AP_ENTRY_EDITED = 11000001;
    public const REPORT_TYPE_ID_AP_ENTRY_DELETED = 11000002;
    public const REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL = 10000001;
    public const REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS = 10000002;

    private readonly SystemCore $CMSCore;
    private int $id;

    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore, int $id) {
      $this->CMSCore = $CMSCore;
      $this->id = $id;
    }
    
    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) {
      $columnsData = $this->get_database_columns_data($columns);
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
    }

    /**
     * Назначить идентификатор
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить идентификатор
     * 
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }

    /**
     * Получить идентификационный номер типа отчета
     *
     * @return int
     */
    public function get_type_id() : int {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['typeID'])) {
          return $metadata['typeID'];
        }
      }

      return 0;
    }

    /**
     * Получить идентификационный номер категории отчета
     *
     * @return int
     */
    public function get_category_id() : int {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['typeID'])) {
          if (in_array($metadata['typeID'], [
            self::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
            self::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
          ])) {
            return 2;
          }
        }
      }

      return 0;
    }

    /**
     * Получить метадату отчета
     *
     * @return array
     */
    public function get_metadata() : array {
      if (property_exists($this, 'metadata')) {
        return json_decode($this->metadata, true);
      }

      return [];
    }

    /**
     * Получить переменные отчета
     *
     * @return array
     */
    public function get_variables() : array {
      if (property_exists($this, 'variables')) {
        return json_decode($this->variables, true);
      }

      return [];
    }

    /**
     * Получить содержимое отчета
     *
     * @return string
     */
    public function get_content() : string {
      $reflectionClass = new \ReflectionClass('\core\PHPLibrary\SystemCore\Report');
      $reflectionClassConstants = $reflectionClass->getConstants();

      foreach ($reflectionClassConstants as $name => $value) {
        if ($value === $this->get_type_id()) {
          return sprintf('{LANG:%s}', $name);
        }
      }

      return '';
    }
    
    /**
     * Получить время создания в UNIX-формате
     *
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : 0;
    }
    
    /**
     * Получить время обновления в UNIX-формате
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : 0;
    }
    
    /**
     * Добавить переменную и ее значение
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function add_variable(string $name, mixed $value) : void {
      $this->variables[$name] = $value;
    }
    
    /**
     * Создание записи в базе данных
     *
     * @param  mixed $CMSCore
     * @param  int $type_id
     * @param  array $variables
     * @return Report
     */
    public static function create(SystemCore $CMSCore, int $type_id, array $variables = []) : Report|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('reports');
      $queryBuilder->statement->add_column('variables');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      /** @var int Время создания записи в БД в UNIX-формате */
      $createdUnixTimestamp = time();

      $metadata = ['typeID' => $type_id];
      $metadataJSON = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $variablesJSON = json_encode($variables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':variables', $variablesJSON, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':metadata', $metadataJSON, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      if ($execute) {
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return $result ? new Report($CMSCore, $result['id']) : null;
      }

      return null;
    }
    
    /**
     * Получить данные колонок записи в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('reports');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int Идентификационный номер записи */
      $id = $this->get_id();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $id, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? $result : null;
    }
  }

}

?>
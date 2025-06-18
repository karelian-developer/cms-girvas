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

    private readonly SystemCore $system_core;
    private int $id;

    /**
     * __construct
     *
     * @param  mixed $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core, int $id) {
      $this->system_core = $system_core;
      $this->id = $id;
    }
    
    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) {
      $columns_data = $this->get_database_columns_data($columns);
      foreach ($columns_data as $column_name => $column_data) {
        $this->{$column_name} = $column_data;
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['typeID'])) {
          return $metadata_array['typeID'];
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['typeID'])) {
          if (in_array($metadata_array['typeID'], [
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
      $reflection_class = new \ReflectionClass('\core\PHPLibrary\SystemCore\Report');
      $reflection_class_constants = $reflection_class->getConstants();

      foreach ($reflection_class_constants as $constant_name => $constant_value) {
        if ($constant_value == $this->get_type_id()) {
          return sprintf('{LANG:%s}', $constant_name);
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
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : 0;
    }
    
    /**
     * Получить время обновления в UNIX-формате
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : 0;
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
     * @param  mixed $system_core
     * @param  int $type_id
     * @param  array $variables
     * @return Report
     */
    public static function create(SystemCore $system_core, int $type_id, array $variables = []) : Report|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('reports');
      $query_builder->statement->add_column('variables');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clauseReturning->add_column('id');
      $query_builder->statement->assembly();

      /** @var int Время создания записи в БД в UNIX-формате */
      $created_unix_timestamp = time();

      $metadata = ['typeID' => $type_id];
      $metadata_json = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $variables_json = json_encode($variables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':variables', $variables_json, \PDO::PARAM_STR);
        $database_query->bindParam(':metadata', $metadata_json, \PDO::PARAM_STR);
        $database_query->bindParam(':created_unix_timestamp', $created_unix_timestamp, \PDO::PARAM_INT);
        $execute = $database_query->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      if ($execute) {
        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new Report($system_core, $result['id']) : null;
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clauseFrom->add_table('reports');
      $query_builder->statement->clauseFrom->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clauseWhere->add_condition('id = :id');
      $query_builder->statement->clauseWhere->assembly();
      $query_builder->statement->assembly();
      
      /** @var int Идентификационный номер записи */
      $id = $this->get_id();

      try {
        $database_connection = $this->system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':id', $id, \PDO::PARAM_INT);
        $database_query->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }
  }

}

?>
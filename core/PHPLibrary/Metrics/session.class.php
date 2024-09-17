<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Metrics {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Metrics as Metrics;
  use \core\PHPLibrary\SystemCore as SystemCore;

  /**
   * Сессия метрики CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  #[\AllowDynamicProperties]
  final class Session {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $system_core = null;
    /** @var int Временная отметка */
    public int $timestamp = 0;

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param Metrics $metrics
     * @param int $id
     */
    public function __construct(SystemCore $system_core, Metrics $metrics, int $id) {
      $this->system_core = $system_core;
      $this->metrics = $metrics;
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
     * Получить ID сессии
     * 
     * @return int
     */
    public function get_id() : int {
      if (property_exists($this, 'id')) {
        return $this->id;
      }

      return 0;
    }

    /**
     * Получить данные метрики
     * 
     * @return array
     */
    public function get_data() : array|null {
      if (property_exists($this, 'data')) {
        return json_decode($this->data, true);
      }

      return null;
    }

    /**
     * Получить данные по просмотрам
     * 
     * @return array
     */
    public function get_data_metrics_views() : array|null {
      if (property_exists($this, 'data')) {
        $data = $this->get_data();

        if (!is_null($data)) {
          if (array_key_exists('metrics', $data)) {
            if (array_key_exists('views', $data['metrics'])) {
              return $data['metrics']['views'];
            }
          }
        }
      }

      return null;
    }

    /**
     *  Получить количество просмотров
     * 
     * @return int
     */
    public function get_data_metrics_views_count() : int {
      if (property_exists($this, 'data')) {
        $data = $this->get_data_metrics_views();
        return count($data);
      }

      return 0;
    }

    /**
     * Получить данные по визитам/посещениям
     * 
     * @param int $type_id
     * 
     * @return array
     */
    public function get_data_metrics_visits(int $type_id) : array|null {
      if (property_exists($this, 'data')) {
        $data = $this->get_data();

        if (!is_null($data)) {
          if (array_key_exists('metrics', $data)) {

            switch ($type_id) {
              case 0: $visits_key_name = 'visits0'; break;
              case 1: $visits_key_name = 'visits1'; break;
              default: $visits_key_name = 'visits0';
            }

            if (array_key_exists($visits_key_name, $data['metrics'])) {
              return $data['metrics'][$visits_key_name];
            }
          }
        }
      }

      return null;
    }

    /**
     * Получить количество визитов/посещений
     * 
     * @param int $type_id
     * 
     * @return int
     */
    public function get_data_metrics_visits_count(int $type_id) : int {
      if (property_exists($this, 'data')) {
        $data = $this->get_data_metrics_visits($type_id);
        return count($data);
      }

      return 0;
    }
    
    /**
     * Получить объект сессии по его временной отметке
     *
     * @param  SystemCore $system_core
     * @param  Metrics $metrics
     * @param  int $timestamp
     * @return Session|null
     */
    public static function get_by_timestamp(SystemCore $system_core, Metrics $metrics, int $timestamp) : Session|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('metrics');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('date = :date');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':date', $timestamp, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? new Session($system_core, $metrics, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия сессии по временной отметке
     *
     * @param  SystemCore $system_core
     * @param  Metrics $metrics
     * @param  int $timestamp
     * @return bool
     */
    public static function exists_by_timestamp(SystemCore $system_core, Metrics $metrics, int $timestamp) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('metrics');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('date = :date');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':date', $timestamp, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Создание новой сессии
     * 
     * @param SystemCore $system_core
     * 
     * @return Session|null
     */
    public static function create(SystemCore $system_core, Metrics $metrics) : Session|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('metrics');
      $query_builder->statement->add_column('date');
      $query_builder->statement->add_column('data');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $created_unix_timestamp = time();
      $metrics_timestamp = $metrics->timestamp;
      $updated_unix_timestamp = $created_unix_timestamp;

      $data_json = json_encode([
        'metrics' => [
          'views' => [],
          'visits0' => [],
          'visits1' => []
        ]
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':data', $data_json, \PDO::PARAM_STR);
      $database_query->bindParam(':date', $metrics_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':created_unix_timestamp', $created_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $updated_unix_timestamp, \PDO::PARAM_INT);
      $execute = $database_query->execute();

      if ($execute) {
        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new Session($system_core, $metrics, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующей сессии
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('metrics');
      $query_builder->statement->set_clause_set();
      
      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'data'])) {
          $query_builder->statement->clause_set->add_column($data_name);
        }
      }

      if (array_key_exists('data', $data)) {
        $json_fields = [];

        foreach ($data['data'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['data'])) {
          $query_builder->statement->clause_set->add_column('data', 'data::jsonb || ' . implode(' || ', $json_fields));
        }
      }

      $query_builder->statement->clause_set->add_column('updated_unix_timestamp');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      /** @var int Текущее время в UNIX-формате */
      $updated_unix_timestamp = time();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      
      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'data'])) {
          switch (gettype($data_value)) {
            case 'boolean': $data_value_type = \PDO::PARAM_BOOL; break;
            case 'integer': $data_value_type = \PDO::PARAM_INT; break;
            case 'string': $data_value_type = \PDO::PARAM_STR; break;
            case 'null': $data_value_type = \PDO::PARAM_NULL; break;
          }

          $database_query->bindParam(':' . $data_name, $data[$data_name], $data_value_type);
        }
      }

      $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $updated_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }
    
    /**
     * Получить данные колонок в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('metrics');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int Идентификационный номер записи */
      $id = $this->get_id();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }
  }
}

?>
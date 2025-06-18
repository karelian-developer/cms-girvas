<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
    public SystemCore|null $CMSCore = null;
    /** @var int Временная отметка */
    public int $timestamp = 0;

    /**
     * __construct
     * 
     * @param SystemCore $CMSCore
     * @param Metrics $metrics
     * @param int $id
     */
    public function __construct(SystemCore $CMSCore, Metrics $metrics, int $id) {
      $this->CMSCore = $CMSCore;
      $this->metrics = $metrics;
      $this->id = $id;
    }
    
    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) : void {
      $columnsData = $this->get_database_columns_data($columns);
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
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
     * @param int $typeID
     * 
     * @return array
     */
    public function get_data_metrics_visits(int $typeID) : array|null {
      if (property_exists($this, 'data')) {
        $data = $this->get_data();

        if (!is_null($data)) {
          if (array_key_exists('metrics', $data)) {

            switch ($typeID) {
              case 0: $keyName = 'visits0'; break;
              case 1: $keyName = 'visits1'; break;
              default: $keyName = 'visits0';
            }

            if (array_key_exists($keyName, $data['metrics'])) {
              return $data['metrics'][$keyName];
            }
          }
        }
      }

      return null;
    }

    /**
     * Получить количество визитов/посещений
     * 
     * @param int $typeID
     * 
     * @return int
     */
    public function get_data_metrics_visits_count(int $typeID) : int {
      if (property_exists($this, 'data')) {
        $data = $this->get_data_metrics_visits($typeID);
        return count($data);
      }

      return 0;
    }
    
    /**
     * Получить объект сессии по его временной отметке
     *
     * @param  SystemCore $CMSCore
     * @param  Metrics $metrics
     * @param  int $timestamp
     * @return Session|null
     */
    public static function get_by_timestamp(SystemCore $CMSCore, Metrics $metrics, int $timestamp) : Session|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('metrics');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('date = :date');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':date', $timestamp, \PDO::PARAM_INT);
			$databaseQuery->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new Session($CMSCore, $metrics, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия сессии по временной отметке
     *
     * @param  SystemCore $CMSCore
     * @param  Metrics $metrics
     * @param  int $timestamp
     * @return bool
     */
    public static function exists_by_timestamp(SystemCore $CMSCore, Metrics $metrics, int $timestamp) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('metrics');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('date = :date');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':date', $timestamp, \PDO::PARAM_INT);
			$databaseQuery->execute();

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Создание новой сессии
     * 
     * @param SystemCore $CMSCore
     * 
     * @return Session|null
     */
    public static function create(SystemCore $CMSCore, Metrics $metrics) : Session|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('metrics');
      $queryBuilder->statement->add_column('date');
      $queryBuilder->statement->add_column('data');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $metricsTimestamp = $metrics->timestamp;
      $updatedUnixTimestamp = $createdUnixTimestamp;

      $dataJSON = json_encode([
        'metrics' => [
          'views' => [],
          'visits0' => [],
          'visits1' => []
        ]
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':data', $dataJSON, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':date', $metricsTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();

      if ($execute) {
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return $result ? new Session($CMSCore, $metrics, $result['id']) : null;
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
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('metrics');
      $queryBuilder->statement->set_clause_set();
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'data'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      if (array_key_exists('data', $data)) {
        $fieldsJSON = [];

        foreach ($data['data'] as $name => $value) {
          array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['data'])) {
          $queryBuilder->statement->clauseSet->add_column('data', 'data::jsonb || ' . implode(' || ', $fieldsJSON));
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('id = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int Текущее время в UNIX-формате */
      $updatedUnixTimestamp = time();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'data'])) {
          switch (gettype($value)) {
            case 'boolean': $valueType = \PDO::PARAM_BOOL; break;
            case 'integer': $valueType = \PDO::PARAM_INT; break;
            case 'string': $valueType = \PDO::PARAM_STR; break;
            case 'null': $valueType = \PDO::PARAM_NULL; break;
          }

          $databaseQuery->bindParam(':' . $name, $data[$name], $valueType);
        }
      }

      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
			$execute = $databaseQuery->execute();

      return ($execute) ? true : false;
    }
    
    /**
     * Получить данные колонок в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('metrics');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int Идентификационный номер записи */
      $id = $this->get_id();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $id, \PDO::PARAM_INT);
			$databaseQuery->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }
  }
}

?>
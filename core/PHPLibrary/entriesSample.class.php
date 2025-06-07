<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
  use \PDOException as PDOException;

  #[\AllowDynamicProperties]
  class EntriesSample implements EntityTypeContent  {
    private readonly SystemCore $system_core;
    private int $id;

    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core, int $id) {
      $this->system_core = $system_core;
      $this->set_id($id);
    }
    
    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) : void {
      $columns_data = $this->get_database_columns_data($columns);
      foreach ($columns_data as $column_name => $column_data) {
        $this->{$column_name} = $column_data;
      }
    }
    
    /**
     * Назначить идентификатор записи
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить идентификатор записи
     *
     * @param  mixed $value
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }
    
    /**
     * Получить дату создания (в UNIX-формате)
     *
     * @return int
     */
    public function get_created_unix_timestamp() : int|string {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : '';
    }
    
    /**
     * Получить дату последнего обновления (в UNIX-формате)
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int|string {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : '';
    }
    
    /**
     * Получить заголовок выборки
     *
     * @param  mixed $locale_name Наименование локализации
     * @return string
     */
    public function get_title($locale_name = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts_array = json_decode($this->texts, true);
        if (isset($texts_array[$locale_name]['title'])) {
          return $texts_array[$locale_name]['title'];
        }
      }

      return '';
    }

    /**
     * Получить описание выборки
     *
     * @param  mixed $locale_name Наименование локализации
     * @return string
     */
    public function get_description($locale_name = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts_array = json_decode($this->texts, true);
        if (isset($texts_array[$locale_name]['description'])) {
          return $texts_array[$locale_name]['description'];
        }
      }

      return '';
    }
    
    /**
     * Получить имя
     *
     * @return string
     */
    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить шаблонную переменную выборки
     *
     * @return string
     */
    public function get_template_var() : string {
      return sprintf('{ENTRIES_SAMPLE:%s}', strtoupper($this->get_name()));
    }

    /**
     * Получить лимит на записи для выборки
     * 
     * @return int
     */
    public function get_limit_count() : int {
      if (property_exists($this, 'metadata')) {
        $array = json_decode($this->metadata, true);

        if (isset($array['limitCount'])) {
          return (is_int($array['limitCount'])) ? $array['limitCount'] : 0;
        }
      }

      return 0;
    }

    /**
     * Получить ID сортировки выборки
     * 
     * @return int
     */
    public function get_sort_type_id() : int {
      if (property_exists($this, 'metadata')) {
        $array = json_decode($this->metadata, true);

        if (isset($array['sortTypeID'])) {
          return (is_int($array['sortTypeID'])) ? $array['sortTypeID'] : 1;
        }
      }

      return 1;
    }

    /**
     * Получить массив ID категорий для выборки
     * 
     * @return array
     */
    public function get_categories_ids() : array {
      if (property_exists($this, 'metadata')) {
        $array = json_decode($this->metadata, true);

        if (isset($array['categoriesIDs'])) {
          return $array['categoriesIDs'];
        }
      }

      return [];
    }

    /**
     * Получить массив объектов категорий для выборки
     * 
     * @return array
     */
    public function get_categories() : array {
      $ids_array = $this->get_categories_ids();

      if (count($ids_array) > 0) {
        $entries_categories_array = [];

        foreach ($ids_array as $id) {
          if (EntryCategory::exists_by_id($this->system_core, $id)) {
            $entries_category = new EntryCategory($this->system_core, $id);
            array_push($entries_categories_array, $entries_category);
          }
        }

        return $entries_categories_array;
      }

      return [];
    }

    /**
     * Получить массив объектов записей для выборки
     * 
     * @param  array $params_array
     * @param  bool $only_published
     * 
     * @return array
     */
    public function get_entries(array $params_array = [], $only_published = false) : array {
      $entries_categories_array = $this->get_categories();

      if (count($entries_categories_array) > 0) {
        $entries_array = [];

        foreach ($entries_categories_array as $entries_category) {
          $entries_category_array = $entries_category->get_entries($params_array, $only_published);
          
          if (count($entries_category_array) > 0) {
            foreach ($entries_category_array as $entry) {
              array_push($entries_array, $entry);
            }
          }
        }
        
        return $entries_array;
      }

      return [];
    }

    /**
     * Получить количество объектов записей для выборки
     * 
     * @return int
     */
    public function get_entries_count() : int {
      $entries = $this->get_entries();
      return count($entries);
    }
    
    /**
     * Получить данные колонок в базе данных
     *
     * @param  array $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_samples');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $id Идентификационный номер */
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

    /**
     * Проверка наличия выборки по идентификационному номеру
     *
     * @param  SystemCore $system_core
     * @param  int $id
     * @return bool
     */
    public static function exists_by_id(SystemCore $system_core, int $id) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_samples');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
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
      
      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Проверка наличия выборки по имени
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return bool
     */
    public static function exists_by_name(SystemCore $system_core, string $name) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_samples');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();
      
      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($database_query->fetchColumn()) ? true : false;
    }
    
    /**
     * Получить объект категории записи по имени
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return EntryCategory
     */
    public static function get_by_name(SystemCore $system_core, string $name) : EntriesSample|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_samples');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
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
      return ($result) ? new EntriesSample($system_core, (int)$result['id']) : null;
    }

    /**
     * Создание новой выборки
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @param  array $texts
     * @param  array $metadata
     * @return EntriesSample|null
     */
    public static function create(SystemCore $system_core, string $name, array $texts, array $metadata = []) : EntriesSample|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('entries_samples');
      $query_builder->statement->add_column('name');
      $query_builder->statement->add_column('texts');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $created_unix_timestamp = time();
      $updated_unix_timestamp = $created_unix_timestamp;

      $texts_json = (!empty($texts)) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
      $metadata_json = (!empty($metadata)) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->bindParam(':texts', $texts_json, \PDO::PARAM_STR);
        $database_query->bindParam(':metadata', $metadata_json, \PDO::PARAM_STR);
        $database_query->bindParam(':created_unix_timestamp', $created_unix_timestamp, \PDO::PARAM_INT);
        $database_query->bindParam(':updated_unix_timestamp', $updated_unix_timestamp, \PDO::PARAM_INT);
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
        return ($result) ? new EntriesSample($system_core, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующей выборки
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('entries_samples');
      $query_builder->statement->set_clause_set();

      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'texts', 'metadata'])) {
          $query_builder->statement->clause_set->add_column($data_name);
        }
      }

      if (array_key_exists('texts', $data)) {
        $json_fields = [];

        foreach ($data['texts'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['texts'])) {
          $query_builder->statement->clause_set->add_column('texts', 'texts::jsonb || ' . implode(' || ', $json_fields));
        }
      }

      if (array_key_exists('metadata', $data)) {
        $json_fields = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $query_builder->statement->clause_set->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $json_fields));
        }
      }

      $query_builder->statement->clause_set->add_column('updated_unix_timestamp');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      /** @var int $entry_updated_unix_timestamp Текущее время в UNIX-формате */
      $updated_unix_timestamp = time();

      try {
        $database_connection = $this->system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        
        foreach ($data as $data_name => $data_value) {
          if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'texts', 'metadata'])) {
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
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($execute) ? true : false;
    }
    
    /**
     * Удаление существующей выборки
     *
     * @return bool
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_samples');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      try {
        $database_connection = $this->system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
        $execute = $database_query->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($execute) ? true : false;
    }
  }
}
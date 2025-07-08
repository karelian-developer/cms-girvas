<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

<<<<<<< HEAD
namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
  use \core\PHPLibrary\Factories\Content as FactoryContent;
  use \PDOException as PDOException;

  #[\AllowDynamicProperties]
  class Entry implements EntityTypeContent {
    private readonly SystemCore $system_core;
    private int $id;
    private int $category_id;
    private int $views_count = 0;
    private string $name;
    
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
     * Установить количество просмотров
     * 
     * @param int $views
     * 
     * @return void
     */
    public function set_views_count(int $value) : void {
      $this->views_count = $value;
    }

    /**
     * Получить количество просмотров
     * 
     * @param int $value
     * 
     * @return int
     */
    public function get_views_count() : int {
      return $this->views_count;
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
     * Получить ID категории записи
     * 
     * @return int
     */
    public function get_category_id() : int {
      return $this->category_id;
    }

    /**
     * Получить временную отметку создания записи в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int|string {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : '';
    }

    /**
     * Получить временную отметку обновления записи в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int|string {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : '';
    }

    /**
     * Получить ID автора записи
     * 
     * @return int
     */
    public function get_author_id() : int|string {
      return (property_exists($this, 'author_id')) ? $this->author_id : 0;
    }

    /**
     * Получить объект категории записи
     * 
     * @param array $init_data
     * 
     * @return EntryCategory
     */
    public function get_category(array $init_data = ['*']) : EntryCategory|null {
      $entry_category_id = $this->get_category_id();

      if (EntryCategory::exists_by_id($this->system_core, $entry_category_id)) {
        $entry_category = new EntryCategory($this->system_core, $entry_category_id);
        $entry_category->init_data($init_data);

        return $entry_category;
      }

      return null;
    }

    /**
     * Получить объект автора записи
     * 
     * @param array $init_data
     * 
     * @return User
     */
    public function get_author(array $init_data = ['*']) : User|null {
      $entry_author_id = $this->get_author_id();

      if (User::exists_by_id($this->system_core, $entry_author_id)) {
        $entry_user = new User($this->system_core, $entry_author_id);
        $entry_user->init_data($init_data);

        return $entry_user;
      }

      return null;
    }
    
    /**
     * Получить заголовок записи
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
     * Получить описание записи
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
     * Получить содержимое записи
     *
     * @param  mixed $locale_name Наименование локализации
     * @return string
     */
    public function get_content($locale_name = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts_array = json_decode($this->texts, true);
        if (isset($texts_array[$locale_name]['content'])) {
          return $texts_array[$locale_name]['content'];
        }
      }

      return '';
    }
    
    /**
     * Получить ключевые слова
     *
     * @param  mixed $locale_name Наименование локализации
     * @return array
     */
    public function get_keywords($locale_name = 'en_US') : array {
      if (property_exists($this, 'texts')) {
        $texts_array = json_decode($this->texts, true);
        if (isset($texts_array[$locale_name]['keywords'])) {
          return $texts_array[$locale_name]['keywords'];
        }
      }

      return [];
    }

    /**
     * Получить URL изображения предпросмотра
     *
     * @return string
     */
    public function get_preview_url() : string {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['preview_url'])) {
          return $metadata_array['preview_url'];
        }
      }

      return '';
    }

    /**
     * Получить статус публикации записи
     *
     * @return bool
     */
    public function is_published() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['is_published'])) {
          return (bool)$metadata_array['is_published'];
        }
      }

      return false;
    }

    /**
     * Получить временную отметку публикации записи в UNIX-формате
     * 
     * @return int
     */
    public function get_published_unix_timestamp() : int {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['publishedUnixTimestamp'])) {
          return $metadata_array['publishedUnixTimestamp'];
        }
      }

      return 0;
    }

    /**
     * Получить данные по дополнительному полю
     * 
     * @param string $field_name
     * 
     * @return mixed
     */
    public function get_additional_field_data(string $field_name) : mixed {
      if (property_exists($this, 'metadata')) {
        /** @var array Массив метаданных */
        $metadata_array = json_decode($this->metadata, true);
        
        if (isset($metadata_array['additionalFields'])) {
          return (isset($metadata_array['additionalFields'][$field_name])) ? $metadata_array['additionalFields'][$field_name] : null;
        }
      }

      return null;
    }

    /**
     * Получить данные по дополнительным полям
     * 
     * @return array
     */
    public function get_additional_fields_data() : array {
      if (property_exists($this, 'metadata')) {
        /** @var array Массив метаданных */
        $metadata_array = json_decode($this->metadata, true);
        
        return (isset($metadata_array['additionalFields'])) ? $metadata_array['additionalFields'] : [];
      }

      return [];
    }

    /**
     * Получить URL дефолтной заставки
     * 
     * @param SystemCore $system_core
     * @param int $size
     * 
     * @return string
     */
    public static function get_preview_default_url(SystemCore $system_core, int $size) : string {
      return sprintf('/%s/images/entry/default_%d.png', $system_core->template->get_url(), $size);
    }
    
    /**
     * Получить имя записи
     *
     * @return void
     */
    public function get_name() {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить URL до записи
     *
     * @return void
     */
    public function get_url() {
      return sprintf('/entry/%s', $this->get_name());
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
      $query_builder->statement->clause_from->add_table('entries');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $entry_id Идентификационный номер записи */
      $entry_id = $this->get_id();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $entry_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }
    
    /**
     * Получить массив объектов комментариев
     *
     * @param array $params_array
     * @return array
     */
    public function get_comments($params_array = []) : array {
      if ($this->get_comments_count() > 0) {
        $entry_comments = new EntryComments($this->system_core);
        return $entry_comments->get_by_entry_id($this->id, $params_array);
      }

      return [];
    }
    
    /**
     * Получить количество комментариев
     *
     * @return int
     */
    public function get_comments_count() : int {
      $entry_comments = new EntryComments($this->system_core);
      return $entry_comments->get_count_by_entry_id($this->id);
    }

    /**
     * Получить очки релевантности
     * 
     * @return float
     */
    public function get_relevance_points() : float {
      $views_count = $this->get_views_count();
      $comments_count = $this->get_comments_count();

      return ($views_count * 0.5) + ($comments_count * 2);
    }
    
    /**
     * Получить объект записи по его наименованию
     *
     * @param  mixed $system_core
     * @param  mixed $entry_name
     * @return Entry
     */
    public static function get_by_name(SystemCore $system_core, string $entry_name) : Entry|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $entry_name, \PDO::PARAM_STR);
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
      return ($result) ? new Entry($system_core, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия записи
     *
     * @param  mixed $system_core
     * @param  mixed $entry_name
     * @return Entry
     */
    public static function exists_by_name(SystemCore $system_core, string $entry_name) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $entry_name, \PDO::PARAM_STR);
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
     * Проверка наличия записи по идентификационному номеру
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
      $query_builder->statement->clause_from->add_table('entries');
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
     * Удаление существующей записи
     *
     * @return bool
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries');
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
        
    /**
     * Создание новой записи
     *
     * @param  mixed $system_core
     * @param  mixed $name
     * @param  mixed $author_id
     * @param  mixed $category_id
     * @param  mixed $texts
     * @return Entry
     */
    public static function create(SystemCore $system_core, string $name, int $author_id, int $category_id, array $texts, array $metadata = []) : Entry|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('entries');
      $query_builder->statement->add_column('author_id');
      $query_builder->statement->add_column('category_id');
      $query_builder->statement->add_column('name');
      $query_builder->statement->add_column('texts');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $entry_created_unix_timestamp = time();
      $entry_updated_unix_timestamp = $entry_created_unix_timestamp;

      $metadata['preview_url'] = '';
      $metadata['is_publish'] = false;

      $texts_json = json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $metadata_json = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':author_id', $author_id, \PDO::PARAM_INT);
        $database_query->bindParam(':category_id', $category_id, \PDO::PARAM_INT);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->bindParam(':texts', $texts_json, \PDO::PARAM_STR);
        $database_query->bindParam(':metadata', $metadata_json, \PDO::PARAM_STR);
        $database_query->bindParam(':created_unix_timestamp', $entry_created_unix_timestamp, \PDO::PARAM_INT);
        $database_query->bindParam(':updated_unix_timestamp', $entry_updated_unix_timestamp, \PDO::PARAM_INT);
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
        return ($result) ? FactoryContent::create($system_core, 'entry', [
          'id' => $result['id']
        ]) : null;
      }

      return null;
    }

    /**
     * Обновление существующей записи
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('entries');
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
      $entry_updated_unix_timestamp = time();

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
        $database_query->bindParam(':updated_unix_timestamp', $entry_updated_unix_timestamp, \PDO::PARAM_INT);
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

=======
namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\Factories\Content as FactoryContent;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Entry implements EntityTypeContent
{
  private readonly SystemCore $CMSCore;
  private int $id;
  private int $categoryID;
  private int $viewsCount = 0;
  private string $name;
  
  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @return void
   */
  public function __construct(SystemCore $CMSCore, int $id)
  {
    $this->CMSCore = $CMSCore;
    $this->setID($id);
  }
  
  /**
   * Инициализация данных из БД
   *
   * @param  mixed $columns
   * @return void
   */
  public function initData(array $columns = ['*']) : void
  {
    $columnsData = $this->getDatabaseColumnsData($columns);
    foreach ($columnsData as $name => $data) {
      $this->{$name} = $data;
    }
  }
  
  /**
   * Назначить идентификатор записи
   *
   * @param  mixed $value
   * @return void
   */
  private function setID(int $value) : void
  {
    $this->id = $value;
>>>>>>> develop
  }

  /**
   * Установить количество просмотров
   * 
   * @param int $views
   * 
   * @return void
   */
  public function setViewsCount(int $value) : void
  {
    $this->viewsCount = $value;
  }

  /**
   * Получить количество просмотров
   * 
   * @param int $value
   * 
   * @return int
   */
  public function getViewsCount() : int
  {
    return $this->viewsCount;
  }
  
  /**
   * Получить идентификатор записи
   *
   * @param  mixed $value
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить ID категории записи
   * 
   * @return int
   */
  public function getCategoryID() : int
  {
    return $this->categoryID;
  }

  /**
   * Получить временную отметку создания записи в UNIX-формате
   * 
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return  $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить временную отметку обновления записи в UNIX-формате
   * 
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Получить ID автора записи
   * 
   * @return int
   */
  public function getAuthorID() : int
  {
    return $this->authorID ?? 0;
  }

  /**
   * Получить объект категории записи
   * 
   * @param array $data
   * 
   * @return EntryCategory
   */
  public function getCategory(array $data = ['*']) : EntryCategory|null
  {
    $categoryID = $this->getCategoryID();

    if (EntryCategory::existsByID($this->CMSCore, $categoryID)) {
      $category = new EntryCategory($this->CMSCore, $categoryID);
      $category->initData($data);

      return $category;
    }

    return null;
  }

  /**
   * Получить объект автора записи
   * 
   * @param array $data
   * 
   * @return User
   */
  public function getAuthor(array $data = ['*']) : User|null
  {
    $authorID = $this->getAuthorID();

    if (User::existsByID($this->CMSCore, $authorID)) {
      $user = new User($this->CMSCore, $authorID);
      $user->initData($data);

      return $user;
    }

    return null;
  }
  
  /**
   * Получить заголовок записи
   *
   * @param  mixed $localeName Наименование локализации
   * @return string
   */
  public function getTitle(string $localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['title'])) {
        return $texts[$localeName]['title'];
      }
    }

    return '';
  }

  /**
   * Получить описание записи
   *
   * @param  mixed $localeName Наименование локализации
   * @return string
   */
  public function getDescription(string $localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['description'])) {
        return $texts[$localeName]['description'];
      }
    }

    return '';
  }

  /**
   * Получить содержимое записи
   *
   * @param  mixed $localeName Наименование локализации
   * @return string
   */
  public function getContent(string $localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['content'])) {
        return $texts[$localeName]['content'];
      }
    }

    return '';
  }
  
  /**
   * Получить ключевые слова
   *
   * @param  mixed $localeName Наименование локализации
   * @return array
   */
  public function getKeywords($localeName = 'en_US') : array
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['keywords'])) {
        return $texts[$localeName]['keywords'];
      }
    }

    return [];
  }

  /**
   * Получить URL изображения предпросмотра
   *
   * @return string
   */
  public function getPreviewURL() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['previewURL'])) {
        return $metadata['previewURL'];
      }
    }

    return '';
  }

  /**
   * Получить статус публикации записи
   *
   * @return bool
   */
  public function isPublished() : bool
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['isPublished'])) {
        return (bool) $metadata['isPublished'];
      }
    }

    return false;
  }

  /**
   * Получить временную отметку публикации записи в UNIX-формате
   * 
   * @return int
   */
  public function getPublishedUnixTimestamp() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['publishedUnixTimestamp'])) {
        return $metadata['publishedUnixTimestamp'];
      }
    }

    return 0;
  }

  /**
   * Получить данные по дополнительному полю
   * 
   * @param string $fieldName
   * 
   * @return mixed
   */
  public function getAdditionalFieldData(string $fieldName) : mixed
  {
    if (property_exists($this, 'metadata')) {
      /** @var array Массив метаданных */
      $metadata = json_decode($this->metadata, true);
      
      if (isset($metadata['additionalFields'])) {
        return $metadata['additionalFields'][$fieldName] ?? null;
      }
    }

    return null;
  }

  /**
   * Получить данные по дополнительным полям
   * 
   * @return array
   */
  public function getAdditionalFieldsData() : array
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      return $metadata['additionalFields'] ?? [];
    }

    return [];
  }

  /**
   * Получить URL дефолтной заставки
   * 
   * @param SystemCore $CMSCore
   * @param int $size
   * 
   * @return string
   */
  public static function getPreviewDefaultURL(SystemCore $CMSCore, int $size) : string
  {
    return '/' . $CMSCore->theme->getURL() . '/images/entry/default_' . (string) $size . '.png';
  }
  
  /**
   * Получить имя записи
   *
   * @return void
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить URL до записи
   *
   * @return void
   */
  public function getURL() : string
  {
    return '/entry/' . $this->getName();
  }
  
  /**
   * Получить данные колонок записи в базе данных
   *
   * @param  array $columns
   * 
   * @return array|null
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $entryID Идентификационный номер записи */
    $entryID = $this->getID();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':id', $entryID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result : null;
  }
  
  /**
   * Получить массив объектов комментариев
   *
   * @param array $params
   * 
   * @return array
   */
  public function getComments(array $params = []) : array
  {
    if ($this->getCommentsCount() > 0) {
      $comments = new EntryComments($this->CMSCore);
      return $comments->getByEntryID($this->id, $params);
    }

    return [];
  }
  
  /**
   * Получить количество комментариев
   *
   * @return int
   */
  public function getCommentsCount() : int
  {
    $comments = new EntryComments($this->CMSCore);
    return $comments->getCountByEntryID($this->id);
  }

  /**
   * Получить очки релевантности
   * 
   * @return float
   */
  public function getRelevancePoints() : float
  {
    $viewsCount = $this->getViewsCount();
    $commentsCount = $this->getCommentsCount();

    return ($viewsCount * 0.5) + ($commentsCount * 2);
  }
  
  /**
   * Получить объект записи по его наименованию
   *
   * @param  mixed $CMSCore
   * @param  mixed $name
   * 
   * @return Entry
   */
  public static function getByName(SystemCore $CMSCore, string $name) : Entry|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`name` = :name',
      'postgresql' => '"name" = :name'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
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
    return $result ? new Entry($CMSCore, (int) $result['id']) : null;
  }

  /**
   * Проверка наличия записи
   *
   * @param  mixed $CMSCore
   * @param  mixed $name
   * @return Entry
   */
  public static function existsByName(SystemCore $CMSCore, string $name) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`name` = :name',
      'postgresql' => '"name" = :name'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Проверка наличия записи по идентификационному номеру
   *
   * @param  SystemCore $CMSCore
   * @param  int $id
   * @return bool
   */
  public static function existsByID(SystemCore $CMSCore, int $id) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
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

    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Удаление существующей записи
   *
   * @return bool
   */
  public function delete() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }
      
  /**
   * Создание новой записи
   *
   * @param  mixed $CMSCore
   * @param  mixed $name
   * @param  mixed $authorID
   * @param  mixed $categoryID
   * @param  mixed $texts
   * @return Entry
   */
  public static function create(SystemCore $CMSCore, string $name, int $authorID, int $categoryID, array $texts, array $metadata = []) : Entry|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('entries');
    $queryBuilder->statement->addColumn('authorID');
    $queryBuilder->statement->addColumn('categoryID');
    $queryBuilder->statement->addColumn('name');
    $queryBuilder->statement->addColumn('texts');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $createdUnixTimestamp = time();
    $updatedUnixTimestamp = $createdUnixTimestamp;

    $metadata['previewURL'] = '';
    $metadata['isPublished'] = false;

    $texts = !empty($texts) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
    $metadata = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':authorID', $authorID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':categoryID', $categoryID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':texts', $texts, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      error_log('SQL: ' . $queryBuilder->statement->assembled);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? FactoryContent::create($CMSCore, 'entry', [
        'id' => (int) $result['id']
      ]) : null;
    }

    return null;
  }

  /**
   * Обновление существующей записи
   *
   * @param  array $data Массив данных
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('entries');
    $queryBuilder->statement->setClauseSet();
    
    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    foreach (['texts', 'metadata'] as $columnName) {
      $fieldsJSON = [];
      
      if (!isset($data[$columnName])) {
        continue;
      }

      foreach ($data[$columnName] as $name => $value) {
        $valueJSON = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fieldsJSON[] = match ($queryBuilder->DMS) {
          CMSDMS::MySQL => sprintf('"%s": %s', $name, $valueJSON),
          CMSDMS::PostgreSQL => sprintf('\'{"%s": %s}\'::jsonb', $name, $valueJSON)
        };
      }

      if (!empty($data[$columnName])) {
        $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
          'mysql' => 'JSON_MERGE_PATCH(COALESCE(' . $columnName . ', \'{}\'), CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON))',
          'postgresql' => $columnName . '::jsonb || ' . implode(' || ', $fieldsJSON)
        ]);
      }
    }

    $queryBuilder->statement->clauseSet->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
    $updatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $valueTypeName = gettype($value);
          $valueType = match ($valueTypeName) {
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'null' => \PDO::PARAM_NULL,
          };

          $databaseQuery->bindParam(':' . $name, $data[$name], $valueType);
        }
      }

      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }
}
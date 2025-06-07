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
  class PageStatic implements EntityTypeContent  {
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
     * Назначить ID страницы
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить ID страницы
     *
     * @param  mixed $value
     * @return int
     */
    public function get_id() : int {
      return $this->id;
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
     * Получить временную отметку создания страницы в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : 0;
    }

    /**
     * Получить временную отметку обновления страницы в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : 0;
    }

    /**
     * Получить ID автора страницы
     * 
     * @return int
     */
    public function get_author_id() : int|string {
      return (property_exists($this, 'author_id')) ? $this->author_id : 0;
    }
    
    /**
     * Получить заголовок страницы
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
     * Получить объект автора страницы
     * 
     * @param array $init_data
     * 
     * @return User
     */
    public function get_author(array $init_data = ['*']) : User|null {
      $author_id = $this->get_author_id();

      if (User::exists_by_id($this->system_core, $author_id)) {
        $author = new User($this->system_core, $author_id);
        $author->init_data($init_data);

        return $author;
      }

      return null;
    }

    /**
     * Получить описание страницы
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
     * Получить содержимое страницы
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
     * Получить статус публикации страницы
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
     * Получить временную отметку публикации страницы в UNIX-формате
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
     * Получить путь до персонального шаблона
     * 
     * @return string
     */
    public function get_personal_template_path() : string {
      if (property_exists($this, 'metadata')) {
        /** @var array Метаданные в виде массива */
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['personalTemplatePath'])) {
          return (!empty($metadata_array['personalTemplatePath'])) ? $metadata_array['personalTemplatePath'] : 'templates/page/static.tpl';
        }
      }

      return 'templates/page/static.tpl';
    }

    /**
     * Проверить наличие файла персонального шаблона
     * 
     * @return bool
     */
    public function exists_personal_template_file() : bool {
      if (property_exists($this, 'metadata')) {
        /** @var string Путь до персонального шаблона */
        $template_path = sprintf('%s/templates/%s', $this->system_core->template->get_path(), $this->get_personal_template_path());
        return file_exists($template_path);
      }

      return false;
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
      return sprintf('/%s/images/pageStatic/default_%d.png', $system_core->template->get_url(), $size);
    }
    
    /**
     * Получить имя страницы
     *
     * @return void
     */
    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить URL до страницы
     *
     * @return void
     */
    public function get_url() {
      return sprintf('/page/%s', $this->get_name());
    }
    
    /**
     * Получить данные колонок страницы в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('pages_static');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $page_static_id Идентификационный номер страницы */
      $page_static_id = $this->get_id();

      try {
        $database_connection = $this->system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':id', $page_static_id, \PDO::PARAM_INT);
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
     * Получить объект страницы по его наименованию
     *
     * @param  mixed $system_core
     * @param  string $page_static_name
     * @return PageStatic
     */
    public static function get_by_name(SystemCore $system_core, string $page_static_name) : PageStatic|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('pages_static');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $page_static_name, \PDO::PARAM_STR);
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
      return ($result) ? new PageStatic($system_core, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия страницы
     *
     * @param  mixed $system_core
     * @param  string $page_static_name
     * @return bool
     */
    public static function exists_by_name(SystemCore $system_core, string $page_static_name) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('pages_static');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $page_static_name, \PDO::PARAM_STR);
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
     * Проверка наличия страницы по идентификационному номеру
     *
     * @param  mixed $system_core
     * @param  int $page_static_id
     * @return bool
     */
    public static function exists_by_id(SystemCore $system_core, int $page_static_id) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('pages_static');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':id', $page_static_id, \PDO::PARAM_INT);
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
     * Удаление существующей страницы
     *
     * @return bool
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('pages_static');
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
     * Создание новой страницы
     *
     * @param  mixed $system_core
     * @param  mixed $name
     * @param  mixed $author_id
     * @param  mixed $category_id
     * @param  mixed $texts
     * @return PageStatic
     */
    public static function create(SystemCore $system_core, string $name, int $author_id, array $texts, array $metadata = []) : PageStatic|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('pages_static');
      $query_builder->statement->add_column('author_id');
      $query_builder->statement->add_column('name');
      $query_builder->statement->add_column('texts');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $page_static_created_unix_timestamp = time();
      $page_static_updated_unix_timestamp = $page_static_created_unix_timestamp;

      $texts_json = (!empty($texts)) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
      $metadata_json = (!empty($metadata)) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

      try {
        $database_connection = $system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':author_id', $author_id, \PDO::PARAM_INT);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->bindParam(':texts', $texts_json, \PDO::PARAM_STR);
        $database_query->bindParam(':metadata', $metadata_json, \PDO::PARAM_STR);
        $database_query->bindParam(':created_unix_timestamp', $page_static_created_unix_timestamp, \PDO::PARAM_INT);
        $database_query->bindParam(':updated_unix_timestamp', $page_static_updated_unix_timestamp, \PDO::PARAM_INT);
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
        return ($result) ? new PageStatic($system_core, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующей страницы
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('pages_static');
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

      /** @var int $page_static_updated_unix_timestamp Текущее время в UNIX-формате */
      $page_static_updated_unix_timestamp = time();

      try {
        $database_connection = $this->system_core->database_connector->database->connection;
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        error_log($query_builder->statement->assembled);
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
        $database_query->bindParam(':updated_unix_timestamp', $page_static_updated_unix_timestamp, \PDO::PARAM_INT);
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

?>
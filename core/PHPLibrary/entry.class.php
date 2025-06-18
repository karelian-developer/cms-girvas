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
  use \core\PHPLibrary\Factories\Content as FactoryContent;
  use \PDOException as PDOException;

  #[\AllowDynamicProperties]
  class Entry implements EntityTypeContent {
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
    public function __construct(SystemCore $CMSCore, int $id) {
      $this->CMSCore = $CMSCore;
      $this->set_id($id);
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
      $this->viewsCount = $value;
    }

    /**
     * Получить количество просмотров
     * 
     * @param int $value
     * 
     * @return int
     */
    public function get_views_count() : int {
      return $this->viewsCount;
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
      return $this->categoryID;
    }

    /**
     * Получить временную отметку создания записи в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : 0;
    }

    /**
     * Получить временную отметку обновления записи в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : 0;
    }

    /**
     * Получить ID автора записи
     * 
     * @return int
     */
    public function get_author_id() : int {
      return (property_exists($this, 'authorID')) ? $this->authorID : 0;
    }

    /**
     * Получить объект категории записи
     * 
     * @param array $data
     * 
     * @return EntryCategory
     */
    public function get_category(array $data = ['*']) : EntryCategory|null {
      $categoryID = $this->get_category_id();

      if (EntryCategory::exists_by_id($this->CMSCore, $categoryID)) {
        $category = new EntryCategory($this->CMSCore, $categoryID);
        $category->init_data($data);

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
    public function get_author(array $data = ['*']) : User|null {
      $authorID = $this->get_author_id();

      if (User::exists_by_id($this->CMSCore, $authorID)) {
        $user = new User($this->CMSCore, $authorID);
        $user->init_data($data);

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
    public function get_title($localeName = 'en_US') : string {
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
    public function get_description($localeName = 'en_US') : string {
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
    public function get_content($localeName = 'en_US') : string {
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
    public function get_keywords($localeName = 'en_US') : array {
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
    public function get_preview_url() : string {
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
    public function is_published() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['isPublished'])) {
          return (bool)$metadata['isPublished'];
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
    public function get_additional_field_data(string $fieldName) : mixed {
      if (property_exists($this, 'metadata')) {
        /** @var array Массив метаданных */
        $metadata = json_decode($this->metadata, true);
        
        if (isset($metadata['additionalFields'])) {
          return (isset($metadata['additionalFields'][$fieldName])) ? $metadata['additionalFields'][$fieldName] : null;
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
        $metadata = json_decode($this->metadata, true);
        
        return (isset($metadata['additionalFields'])) ? $metadata['additionalFields'] : [];
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
    public static function get_preview_default_url(SystemCore $CMSCore, int $size) : string {
      return '/' . $CMSCore->theme->get_url() . '/images/entry/default_' . (string) $size . '.png';
    }
    
    /**
     * Получить имя записи
     *
     * @return void
     */
    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить URL до записи
     *
     * @return void
     */
    public function get_url() : string {
      return '/entry/' . $this->get_name();
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
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $entryID Идентификационный номер записи */
      $entryID = $this->get_id();

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
     * @return array
     */
    public function get_comments($params = []) : array {
      if ($this->get_comments_count() > 0) {
        $comments = new EntryComments($this->CMSCore);
        return $comments->get_by_entry_id($this->id, $params);
      }

      return [];
    }
    
    /**
     * Получить количество комментариев
     *
     * @return int
     */
    public function get_comments_count() : int {
      $comments = new EntryComments($this->CMSCore);
      return $comments->get_count_by_entry_id($this->id);
    }

    /**
     * Получить очки релевантности
     * 
     * @return float
     */
    public function get_relevance_points() : float {
      $viewsCount = $this->get_views_count();
      $commentsCount = $this->get_comments_count();

      return ($viewsCount * 0.5) + ($commentsCount * 2);
    }
    
    /**
     * Получить объект записи по его наименованию
     *
     * @param  mixed $CMSCore
     * @param  mixed $name
     * @return Entry
     */
    public static function get_by_name(SystemCore $CMSCore, string $name) : Entry|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      return ($result) ? new Entry($CMSCore, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия записи
     *
     * @param  mixed $CMSCore
     * @param  mixed $name
     * @return Entry
     */
    public static function exists_by_name(SystemCore $CMSCore, string $name) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Проверка наличия записи по идентификационному номеру
     *
     * @param  SystemCore $CMSCore
     * @param  int $id
     * @return bool
     */
    public static function exists_by_id(SystemCore $CMSCore, int $id) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

      return ($databaseQuery->fetchColumn()) ? true : false;
    }
    
    /**
     * Удаление существующей записи
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
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

      return ($execute) ? true : false;
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
    public static function create(SystemCore $CMSCore, string $name, int $authorID, int $categoryID, array $texts, array $metadata = []) : Entry|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('entries');
      $queryBuilder->statement->add_column('authorID');
      $queryBuilder->statement->add_column('categoryID');
      $queryBuilder->statement->add_column('name');
      $queryBuilder->statement->add_column('texts');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;

      $metadata['previewURL'] = '';
      $metadata['isPublished'] = false;

      $texts = (!empty($texts)) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
      $metadata = (!empty($metadata)) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

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

      if ($execute) {
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? FactoryContent::create($CMSCore, 'entry', [
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
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('entries');
      $queryBuilder->statement->set_clause_set();
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      if (array_key_exists('texts', $data)) {
        $fieldsJSON = [];

        foreach ($data['texts'] as $name => $value) {
          array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['texts'])) {
          $queryBuilder->statement->clauseSet->add_column('texts', 'texts::jsonb || ' . implode(' || ', $fieldsJSON));
        }
      }

      if (array_key_exists('metadata', $data)) {
        $fieldsJSON = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $queryBuilder->statement->clauseSet->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $fieldsJSON));
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
      $updatedUnixTimestamp = time();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        
        foreach ($data as $name => $value) {
          if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
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
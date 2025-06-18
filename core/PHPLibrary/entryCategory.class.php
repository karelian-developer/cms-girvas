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
  class EntryCategory implements EntityTypeContent {
    private readonly SystemCore $CMSCore;
    private int $id;

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
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : '';
    }
    
    /**
     * Получить дату последнего обновления (в UNIX-формате)
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int|string {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : '';
    }
    
    /**
     * Получить ID родительской категории
     *
     * @return int
     */
    public function get_parent_id() : int {
      return (property_exists($this, 'parentID')) ? $this->parentID : 0;
    }
    
    /**
     * Получить родительскую категории
     *
     * @return EntryCategory|null
     */
    public function get_parent() : EntryCategory|null {
      return ($this->get_parent_id() != 0) ? new EntryCategory($this->CMSCore, $this->get_parent_id()) : null;
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
     * Отображается ли категория на стартовой странице
     *
     * @return bool
     */
    public function is_showed_on_index_page() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['isShowedOnIndexPage'])) {
          return (bool)$metadata['isShowedOnIndexPage'];
        }
      }

      return true;
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
     * Получить имя
     *
     * @return void
     */
    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить URL до категории с записями
     *
     * @return void
     */
    public function get_url() : string {
      return '/entries/' . $this->get_name();
    }
    
    /**
     * Получить массив объектов записей
     *
     * @param  array $params
     * @param  bool $isPublished
     * 
     * @return array
     */
    public function get_entries(array $params = [], $isPublished = false) : array {
      return (new Entries($this->CMSCore))->get_by_category_id($this->id, $params, $isPublished);
    }
    
    /**
     * Получить данные колонок записи в базе данных
     *
     * @param  array $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_categories');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $entryID Идентификационный номер записи */
      $entryID = $this->get_id();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $entryID, \PDO::PARAM_INT);
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
      return ($result) ? $result : null;
    }

    /**
     * Проверка наличия категории записи по идентификационному номеру
     *
     * @param  SystemCore $CMSCore
     * @param  int $categoryID
     * @return bool
     */
    public static function exists_by_id(SystemCore $CMSCore, int $categoryID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_categories');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $categoryID, \PDO::PARAM_INT);
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
     * Проверка наличия категории записи по имени
     *
     * @param  SystemCore $CMSCore
     * @param  string $categoryName
     * @return bool
     */
    public static function exists_by_name(SystemCore $CMSCore, string $categoryName) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_categories');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();
      
      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $categoryName, \PDO::PARAM_STR);
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
     * Получить объект категории записи по имени
     *
     * @param  SystemCore $CMSCore
     * @param  string $categoryName
     * @return EntryCategory
     */
    public static function get_by_name(SystemCore $CMSCore, string $categoryName) : EntryCategory|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_categories');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $categoryName, \PDO::PARAM_STR);
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
      return ($result) ? new EntryCategory($CMSCore, (int)$result['id']) : null;
    }

    /**
     * Создание новой категории записей
     *
     * @param  SystemCore $CMSCore
     * @param  string $name
     * @param  int $parentID
     * @param  array $texts
     * @param  array $metadata
     * @return EntryCategory|null
     */
    public static function create(SystemCore $CMSCore, string $name, int $parentID, array $texts, array $metadata = []) : EntryCategory|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('entries_categories');
      $queryBuilder->statement->add_column('name');
      $queryBuilder->statement->add_column('texts');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->add_column('parentID');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;

      $texts = (!empty($texts)) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
      $metadata = (!empty($metadata)) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':parentID', $parentID, \PDO::PARAM_INT);
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
        return ($result) ? new EntryCategory($CMSCore, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующей категории записей
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('entries_categories');
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
    
    /**
     * Удаление существующей категории записей
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_categories');
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
  }
}
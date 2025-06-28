<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class EntriesSample implements EntityTypeContent
{
  private readonly SystemCore $CMSCore;
  private int $id;

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
   * Получить дату создания (в UNIX-формате)
   *
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }
  
  /**
   * Получить дату последнего обновления (в UNIX-формате)
   *
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }
  
  /**
   * Получить заголовок выборки
   *
   * @param  string $localeName Наименование локализации
   * 
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
   * Получить описание выборки
   *
   * @param  string $localeName Наименование локализации
   * 
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
   * Получить имя
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить шаблонную переменную выборки
   *
   * @return string
   */
  public function getTemplateVar() : string
  {
    return '{ENTRIES_SAMPLE:' . strtoupper($this->getName()) . '}';
  }

  /**
   * Получить лимит на записи для выборки
   * 
   * @return int
   */
  public function getLimitCount() : int
  {
    if (property_exists($this, 'metadata')) {
      $array = json_decode($this->metadata, true);

      if (isset($array['limitCount'])) {
        return is_int($array['limitCount']) ? $array['limitCount'] : 0;
      }
    }

    return 0;
  }

  /**
   * Получить ID сортировки выборки
   * 
   * @return int
   */
  public function getSortTypeID() : int
  {
    if (property_exists($this, 'metadata')) {
      $array = json_decode($this->metadata, true);

      if (isset($array['sortTypeID'])) {
        return is_int($array['sortTypeID']) ? $array['sortTypeID'] : 1;
      }
    }

    return 1;
  }

  /**
   * Получить массив ID категорий для выборки
   * 
   * @return array
   */
  public function getCategoriesIDs() : array
  {
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
  public function getCategories() : array
  {
    $IDsArray = $this->getCategoriesIDs();
    $categories = [];

    foreach ($IDsArray as $id) {
      if (EntryCategory::existsByID($this->CMSCore, $id)) {
        $category = new EntryCategory($this->CMSCore, $id);
        array_push($categories, $category);
      }
    }

    return $categories;
  }

  /**
   * Получить массив объектов записей для выборки
   * 
   * @param  array $params
   * @param  bool $isPublished
   * 
   * @return array
   */
  public function getEntries(array $params = [], $isPublished = false) : array
  {
    $categories = $this->getCategories();

    if (count($categories) > 0) {
      $entries = [];

      foreach ($categories as $category) {
        $categoryArray = $category->getEntries($params, $isPublished);
        
        if (count($categoryArray) > 0) {
          foreach ($categoryArray as $entry) {
            array_push($entries, $entry);
          }
        }
      }
      
      return $entries;
    }

    return [];
  }

  /**
   * Получить количество объектов записей для выборки
   * 
   * @return int
   */
  public function getEntriesCount() : int {
    $entries = $this->getEntries();
    return count($entries);
  }
  
  /**
   * Получить данные колонок в базе данных
   *
   * @param  array $columns
   * 
   * @return void
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_samples');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $id Идентификационный номер */
    $id = $this->getID();

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
    return ($result) ? $result : null;
  }

  /**
   * Проверка наличия выборки по идентификационному номеру
   *
   * @param  SystemCore $CMSCore
   * @param  int $id
   * 
   * @return bool
   */
  public static function existsByID(SystemCore $CMSCore, int $id) : bool
  {
    $queryBuilder = new DatabaseQueryBuilder($CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_samples');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
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
    
    return ($databaseQuery->fetchColumn()) ? true : false;
  }

  /**
   * Проверка наличия выборки по имени
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * 
   * @return bool
   */
  public static function existsByName(SystemCore $CMSCore, string $name) : bool
  {
    $queryBuilder = new DatabaseQueryBuilder($CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_samples');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"name" = :name');
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

    return ($databaseQuery->fetchColumn()) ? true : false;
  }
  
  /**
   * Получить объект категории записи по имени
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * 
   * @return EntryCategory
   */
  public static function getByName(SystemCore $CMSCore, string $name) : EntriesSample|null
  {
    $queryBuilder = new DatabaseQueryBuilder($CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_samples');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"name" = :name');
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
    return ($result) ? new EntriesSample($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Создание новой выборки
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * @param  array $texts
   * @param  array $metadata
   * 
   * @return EntriesSample|null
   */
  public static function create(SystemCore $CMSCore, string $name, array $texts, array $metadata = []) : EntriesSample|null
  {
    $queryBuilder = new DatabaseQueryBuilder($CMSCore);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('entries_samples');
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

    $texts = !empty($texts) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
    $metadata = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
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
      return $result ? new EntriesSample($CMSCore, $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующей выборки
   *
   * @param  array $data Массив данных
   * 
   * @return bool
   */
  public function update(array $data) : bool
  {
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('entries_samples');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    if (array_key_exists('texts', $data)) {
      $fieldsJSON = [];

      foreach ($data['texts'] as $name => $value) {
        array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
      }

      if (!empty($data['texts'])) {
        $queryBuilder->statement->clauseSet->addColumn('texts', 'texts::jsonb || ' . implode(' || ', $fieldsJSON));
      }
    }

    if (array_key_exists('metadata', $data)) {
      $fieldsJSON = [];

      foreach ($data['metadata'] as $name => $value) {
        array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
      }

      if (!empty($data['metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn('metadata', 'metadata::jsonb || ' . implode(' || ', $fieldsJSON));
      }
    }

    $queryBuilder->statement->clauseSet->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('id = :id');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int $entry_updated_unix_timestamp Текущее время в UNIX-формате */
    $updatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $valueTypeName = gettype($value);
          $valueType = match ($valueTypeName) {
            'boolean' => \PDO::PARAM_BOOL;
            'integer' => \PDO::PARAM_INT;
            'string' => \PDO::PARAM_STR;
            'null' => \PDO::PARAM_NULL;
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
  
  /**
   * Удаление существующей выборки
   *
   * @return bool
   */
  public function delete() : bool
  {
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_samples');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
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
}
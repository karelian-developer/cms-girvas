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
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class EntryCategory implements EntityTypeContent
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
  public function getID() : int {
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
   * Получить ID родительской категории
   *
   * @return int
   */
  public function getParentID() : int
  {
    return $this->parentID ?? 0;
  }
  
  /**
   * Получить родительскую категории
   *
   * @return EntryCategory|null
   */
  public function getParent() : EntryCategory|null
  {
    return $this->getParentID() !== 0 ? new EntryCategory($this->CMSCore, $this->getParentID()) : null;
  }
  
  /**
   * Получить заголовок записи
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
   * Отображается ли категория на стартовой странице
   *
   * @return bool
   */
  public function isShowedOnIndexPage() : bool
  {
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
   * @return void
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить URL до категории с записями
   *
   * @return void
   */
  public function getURL() : string
  {
    return '/entries/' . $this->getName();
  }
  
  /**
   * Получить массив объектов записей
   *
   * @param  array $params
   * @param  bool $isPublished
   * 
   * @return array
   */
  public function getEntries(array $params = [], $isPublished = false) : array
  {
    return (new Entries($this->CMSCore))->getByCategoryID($this->id, $params, $isPublished);
  }
  
  /**
   * Получить данные колонок записи в базе данных
   *
   * @param  array $columns
   * 
   * @return void
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $entryID Идентификационный номер записи */
    $entryID = $this->getID();

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
    return $result ? $result : null;
  }

  /**
   * Проверка наличия категории записи по идентификационному номеру
   *
   * @param  SystemCore $CMSCore
   * @param  int $categoryID
   * 
   * @return bool
   */
  public static function existsByID(SystemCore $CMSCore, int $categoryID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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
    
    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Проверка наличия категории записи по имени
   *
   * @param  SystemCore $CMSCore
   * @param  string $categoryName
   * 
   * @return bool
   */
  public static function existsByName(SystemCore $CMSCore, string $categoryName) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"name" = :name');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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

    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Получить объект категории записи по имени
   *
   * @param  SystemCore $CMSCore
   * @param  string $categoryName
   * 
   * @return EntryCategory
   */
  public static function getByName(SystemCore $CMSCore, string $categoryName) : EntryCategory|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"name" = :name');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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
    return $result ? new EntryCategory($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Создание новой категории записей
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * @param  int $parentID
   * @param  array $texts
   * @param  array $metadata
   * 
   * @return EntryCategory|null
   */
  public static function create(SystemCore $CMSCore, string $name, int $parentID, array $texts, array $metadata = []) : EntryCategory|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('entries_categories');
    $queryBuilder->statement->addColumn('name');
    $queryBuilder->statement->addColumn('texts');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->addColumn('parentID');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $createdUnixTimestamp = time();
    $updatedUnixTimestamp = $createdUnixTimestamp;

    $texts = !empty($texts) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
    $metadata = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

    error_log('SQL: ' . $queryBuilder->statement->assembled);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':texts', $texts, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':parentID', $parentID, \PDO::PARAM_INT);
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
      $queryBuilder->statement->clauseFrom->addTable('entries_categories');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
    }

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

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new EntryCategory($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующей категории записей
   *
   * @param  array $data Массив данных
   * 
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('entries_categories');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    foreach (['texts'] as $columnName) {
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
          'mysql' => 'JSON_MERGE_PRESERVE(COALESCE(' . $columnName . ', \'{}\'), CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON))',
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

    error_log('SQL: ' . $queryBuilder->statement->assembled);

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

    return ($execute) ? true : false;
  }
  
  /**
   * Удаление существующей категории записей
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
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
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
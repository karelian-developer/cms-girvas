<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \DOMDocument as DOMDocument;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class ContentBlock implements EntityTypeContent
{
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $id
  ) {}
  
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
   * Получить заголовок
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
   * Получить описание
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
   * Получить содержимое
   *
   * @param  string $localeName Наименование локализации
   * 
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
   * Получить имя
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить ID типа
   *
   * @return int
   */
  public function getTypeID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      if (isset($metadata['typeID'])) {
        return $metadata['typeID'];
      }
    }

    return 0;
  }
  
  /**
   * Получить имя шаблона
   *
   * @return string
   */
  public function getTemplateName() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      if (isset($metadata['templateName'])) {
        return $metadata['templateName'];
      }
    }

    return '';
  }
  
  /**
   * Получить имя секции для внедрения
   *
   * @return string
   */
  public function getSectionIntegrationName() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      if (isset($metadata['sectionIntegrationName'])) {
        return $metadata['sectionIntegrationName'];
      }
    }

    return '';
  }
  
  /**
   * Получить правило адресной строки
   *
   * @return string
   */
  public function getURLRule() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      if (isset($metadata['URLRule'])) {
        return $metadata['URLRule'];
      }
    }

    return '';
  }
  
  /**
   * Получить шаблонную переменную
   *
   * @return string
   */
  public function getTemplateVar() : string
  {
    return '{CONTENT_BLOCK:' . strtoupper($this->getName()) . '}';
  }

  /**
   * Получить тексты
   * 
   * @return array
   */
  public function getTexts() : array
  {
    if (property_exists($this, 'texts')) {
      return json_decode($this->texts, true);
    }

    return [];
  }

  /**
   * Получить заполненные тексты
   * 
   * @return array
   */
  public function getCompletedTexts() : array
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      return array_filter($texts, function ($locale) {
        if (!is_array($locale) || empty($locale)) {
          return false;
        };

        foreach ($locale as $key => $value) {
          if (empty($value) && in_array($key, ['title', 'description'])) {
            return false;
          }
        }

        return true;
      });
    }

    return [];
  }

  /**
   * Получить данные по заполненным локализациям
   * 
   * @param CoreInterface $CMSCore
   * 
   * @return array
   */
  public function getCompletedLocalesData(CoreInterface $CMSCore) : array
  {
    if (property_exists($this, 'texts')) {
      $texts = $this->getCompletedTexts();
      $locales = [];

      foreach ($texts as $localeName => $data) {
        $CMSLocale = new CMSLocale($CMSCore, $localeName);
        $CMSLocale->initPathes();
        $locales[$localeName] = [
          'title' => $CMSLocale->getTitle(),
          'iconURL' => $CMSLocale->getIconURL()
        ];
      }

      return $locales;
    }

    return [];
  }
  
  /**
   * Получить данные колонок в базе данных
   *
   * @param  array $columns
   * 
   * @return ?array
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : ?array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
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
   * Проверка существования по идентификационному номеру
   *
   * @param  CoreInterface $CMSCore
   * @param  int $id
   * 
   * @return bool
   */
  public static function existsByID(CoreInterface $CMSCore, int $id) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms');
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
    
    return ($databaseQuery->fetchColumn()) ? true : false;
  }

  /**
   * Проверка существования по имени
   *
   * @param  CoreInterface $CMSCore
   * @param  string $name
   * 
   * @return bool
   */
  public static function existsByName(CoreInterface $CMSCore, string $name) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms');
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

    return ($databaseQuery->fetchColumn()) ? true : false;
  }

  /**
   * Проверка существования данных формы по ID
   *
   * @param  CoreInterface $CMSCore
   * @param  int $id
   * 
   * @return bool
   */
  public static function existsDataByID(CoreInterface $CMSCore, int $id) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms_data');
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
   * Получить объект по имени
   *
   * @param  CoreInterface $CMSCore
   * @param  string $name
   * 
   * @return ?EntityTypeContent
   */
  public static function getByName(CoreInterface $CMSCore, string $name) : ?EntityTypeContent
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms');
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
    return ($result) ? new ContentBlock($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Создание новой
   *
   * @param  CoreInterface $CMSCore
   * @param  string $name
   * @param  array $texts
   * @param  array $metadata
   * 
   * @return EntityTypeContent
   */
  public static function create(CoreInterface $CMSCore, string $name, array $texts, array $metadata = []) : ?EntityTypeContent
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('forms');
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

    if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('forms');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

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
      return $result ? new ContentBlock($CMSCore, $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующей
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
    $queryBuilder->statement->setTable('forms');
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

    /** @var int $entry_updated_unix_timestamp Текущее время в UNIX-формате */
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
  
  /**
   * Удаление существующей формы
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
    $queryBuilder->statement->clauseFrom->addTable('forms');
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
   * Удаление существующих данных формы
   * 
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return bool
   */
  public static function deleteData(CoreInterface $CMSCore, int $id) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms_data');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $id, \PDO::PARAM_INT);
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
   * Сохранение данных с формы
   *
   * @param  array $data
   * 
   * @return bool
   */
  public function saveData(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('forms_data');
    $queryBuilder->statement->addColumn('formID');
    $queryBuilder->statement->addColumn('data');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $createdUnixTimestamp = time();
    $updatedUnixTimestamp = $createdUnixTimestamp;

    $data = !empty($data) ? json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
    
    try {
      $formID = $this->getID();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':formID', $formID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':data', $data, \PDO::PARAM_STR);
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
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('forms_data');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
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

    return $execute ? true : false;
  }

  /**
   * Полученить данных с формы
   * 
   * @return array
   */
  public function getData() : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['*']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('forms_data');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`formID` = :formID',
      'postgresql' => '"formID" = :formID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $formID = $this->getID();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':formID', $formID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $array = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

    if ($results) {
      foreach ($results as $data) {
        $array[] = $data;
      }
    }

    return $array;
  }
}
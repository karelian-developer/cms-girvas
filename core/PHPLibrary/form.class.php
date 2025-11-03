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
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \DOMDocument as DOMDocument;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Form implements EntityTypeContent
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
   * Получить имя
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить шаблонную переменную
   *
   * @return string
   */
  public function getTemplateVar() : string
  {
    return '{FORM:' . strtoupper($this->getName()) . '}';
  }

  /**
   * Получить элементы
   * 
   * @return array
   */
  public function getElements() : array
  {
    if (property_exists($this, 'elements')) {
      return json_decode($this->elements, true);
    }

    return [];
  }
  
  /**
   * Получить метод
   *
   * @return int
   */
  public function getMethodID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      
      if (isset($metadata['methodID'])) {
        return (int) $metadata['methodID'];
      }
    }

    return 'POST';
  }
  
  /**
   * Получить ссылку обработки
   *
   * @return string
   */
  public function getAction() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      
      if (isset($metadata['action'])) {
        return $metadata['action'];
      }
    }

    return '';
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
   * Получить количество объектов записей для выборки
   * 
   * @return int
   */
  public function getEntriesCount() : int
  {
    $entries = $this->getEntries();
    return count($entries);
  }

  public function assembly() : string {
    $CMSLocale = $this->CMSCore->locale;
    $CMSLocaleName = $CMSLocale->getName();

    $elements = $this->getElements();
    $document = new DOMDocument('1.0', 'UTF-8');

    $formName = $this->getName();

    $formElement = $document->createElement('form');
    $formElement->setAttribute('method', match ($this->getMethodID()) {
      1 => 'GET',
      2 => 'POST',
      3 => 'PUT',
      4 => 'DELETE',
      5 => 'PATCH'
    });

    $formElement->setAttribute('class', 'form form_' . $formName);
    $formElement->setAttribute('action', $this->getAction());

    usort($elements, function($a, $b) {
      return $a['sequenceNumber'] <=> $b['sequenceNumber'];
    });

    foreach ($elements as $index => $element) {
      $DOMElementName = $element['name'];
      $DOMElementTitle = $element['texts'][$CMSLocaleName]['title'];
      $DOMElementDescription = $element['texts'][$CMSLocaleName]['description'];
      $DOMElementPlaceholder = $element['texts'][$CMSLocaleName]['placeholder'];
      $DOMElementType = $element['type'];
      $DOMElementID = 'FORM_' . strtoupper(str_replace('-', '_', $formName)) . '_' . strtoupper($DOMElementName);

      $DOMElement = $DOMElementType === 'textarea' 
        ? $document->createElement('textarea')
        : $document->createElement('input');
      
      if ($DOMElementType !== 'textarea') {
        $DOMElement->setAttribute('type', $DOMElementType);
        $DOMElement->setAttribute('autocomplete', 'off');
      }
      
      $DOMElement->setAttribute('id', $DOMElementID);
      $DOMElement->setAttribute('placeholder', $DOMElementPlaceholder);
      $DOMElement->setAttribute('name', $DOMElementName);

      if (in_array($DOMElementType, ['submit', 'reset'])) {
        $DOMElement->setAttribute('value', $DOMElementTitle);
      }

      if (!in_array($DOMElementType, ['submit', 'reset', 'checkbox'])) {
        $labelElement = $document->createElement('label', $DOMElementTitle);
        $labelElement->setAttribute('for', $DOMElementID);
        $formElement->appendChild($labelElement);
      }

      if ($DOMElementType === 'checkbox') {
        $DOMElementDescription = mb_convert_encoding($DOMElementDescription, 'HTML-ENTITIES', 'UTF-8');

        $documentFragment = new DOMDocument('1.0', 'UTF-8');
        $documentFragment->loadHTML($DOMElementDescription, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $descriptionElement = $document->importNode($documentFragment->documentElement, true);

        $checkboxContainerElement = $document->createElement('div');
        $checkboxContainerLabelElement = $document->createElement('div');

        $checkboxContainerElement->setAttribute('class', 'form__input-container input-container input-container_flex-checkbox');
        $checkboxContainerLabelElement->setAttribute('class', 'input-container__label label');

        $checkboxContainerElement->appendChild($DOMElement);
        $checkboxContainerLabelElement->appendChild($descriptionElement);
        $checkboxContainerElement->appendChild($checkboxContainerLabelElement);

        $formElement->appendChild($checkboxContainerElement);
      } else {
        $formElement->appendChild($DOMElement);
      }
    }

    $document->appendChild($formElement);

    return $document->saveHTML();
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
    return ($result) ? new Form($CMSCore, (int)$result['id']) : null;
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
  public static function create(CoreInterface $CMSCore, string $name, array $texts, array $elements, array $metadata = []) : ?EntityTypeContent
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
    $elements = !empty($elements) ? json_encode($elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':texts', $texts, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':elements', $elements, \PDO::PARAM_STR);
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
      return $result ? new Form($CMSCore, $result['id']) : null;
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
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata', 'elements'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    foreach (['texts', 'metadata', 'elements'] as $columnName) {
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
        if ($columnName === 'elements') {
          $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
            'mysql' => 'CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON)',
            'postgresql' => implode(' || ', $fieldsJSON)
          ]);
        } else {
          $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
            'mysql' => 'JSON_MERGE_PATCH(COALESCE(' . $columnName . ', \'{}\'), CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON))',
            'postgresql' => $columnName . '::jsonb || ' . implode(' || ', $fieldsJSON)
          ]);
        }
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
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata', 'elements'])) {
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
   * Удаление существующей
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
}
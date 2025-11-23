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
use \core\PHPLibrary\Factories\Content as FactoryContent;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\SystemCore as CMSCore;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Entry implements EntityTypeContent
{
  private int $authorID;
  private int $categoryID;
  private int $viewsCount = 0;
  private string $name;
  
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
   * @return ?EntryCategory
   */
  public function getCategory(array $data = ['*']) : ?EntryCategory
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
   * @return ?User
   */
  public function getAuthor(array $data = ['*']) : ?User
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
          if (empty($value) && in_array($key, ['title', 'description', 'content'])) {
            return false;
          }
        }

        return true;
      });
    }

    return [];
  }

  /**
   * Получить заполненные SEO-тексты
   * 
   * @return array
   */
  public function getCompletedSEOTexts() : array
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      return array_filter($texts, function ($locale) {
        if (!is_array($locale) || empty($locale)) {
          return false;
        };

        foreach ($locale as $key => $value) {
          if (empty($value) && in_array($key, ['SEOTitle', 'SEODescription', 'keywords'])) {
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
   * Получить заголовок записи
   *
   * @param string $localeName Наименование локализации
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
   * Получить заголовок SEO-записи
   *
   * @param string Наименование локализации
   * 
   * @return string
   */
  public function getSEOTitle(string $localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['SEOTitle'])) {
        return $texts[$localeName]['SEOTitle'];
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
   * Получить SEO-описание записи
   *
   * @param string $localeName Наименование локализации
   * @return string
   */
  public function getSEODescription(string $localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      if (isset($texts[$localeName]['SEODescription'])) {
        return $texts[$localeName]['SEODescription'];
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
        return is_numeric($metadata['publishedUnixTimestamp'])
          ? (int) $metadata['publishedUnixTimestamp']
          : 0;
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
   * @param CMSCore $CMSCore
   * @param int $size
   * 
   * @return string
   */
  public static function getPreviewDefaultURL(CMSCore $CMSCore, int $size) : string
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
   * Получить предыдущую запись
   * 
   * @return ?EntityTypeContent
   */
  public function getPreviousEntry() : ?EntityTypeContent {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` < :id AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
      'postgresql' => '"id" < :id AND (metadata::jsonb->>\'isPublished\')::boolean = true'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
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
    return $result ? new Entry($this->CMSCore, (int) $result['id']) : null;
  }

  /**
   * Получить следущую запись
   * 
   * @return ?EntityTypeContent
   */
  public function getNextEntry() : ?EntityTypeContent {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` > :id AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
      'postgresql' => '"id" > :id AND (metadata::jsonb->>\'isPublished\')::boolean = true'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
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
    return $result ? new Entry($this->CMSCore, (int) $result['id']) : null;
  }
  
  /**
   * Получить объект записи по его наименованию
   *
   * @param CMSCore $CMSCore
   * @param string $name
   * 
   * @return Entry
   */
  public static function getByName(CMSCore $CMSCore, string $name) : ?Entry
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
   * @param CMSCore $CMSCore
   * @param string $name
   * 
   * @return bool
   */
  public static function existsByName(CMSCore $CMSCore, string $name) : bool
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
   * @param CMSCore $CMSCore
   * @param int $id
   * 
   * @return bool
   */
  public static function existsByID(CMSCore $CMSCore, int $id) : bool
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
   * @param  CMSCore $CMSCore
   * @param  string $name
   * @param  int $authorID
   * @param  int $categoryID
   * @param  array $texts
   * @param  array $metadata
   * 
   * @return ?Entry
   */
  public static function create(CMSCore $CMSCore, string $name, int $authorID, int $categoryID, array $texts, array $metadata = []) : ?Entry
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
        $valueJSON = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG);

        $fieldsJSON[] = match ($queryBuilder->DMS) {
          CMSDMS::MySQL => sprintf("\"%s\": %s", $name, $valueJSON),
          CMSDMS::PostgreSQL => sprintf("'{\"%s\": %s}'::jsonb", $name, $valueJSON)
        };
      }

      if (!empty($data[$columnName])) {
        $fieldsJSONImplodedMySQL = implode(', ', $fieldsJSON);
        $fieldsJSONImplodedPostgreSQL = implode(' || ', $fieldsJSON);

        $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
          'mysql' => "JSON_MERGE_PATCH(COALESCE(`$columnName`, '{}'), CAST('{$fieldsJSONImplodedMySQL}' AS JSON))",
          'postgresql' => "$columnName::jsonb || $fieldsJSONImplodedPostgreSQL"
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
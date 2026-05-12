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
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class PageStatic implements EntityTypeContent
{
  private int $categoryID;
  private int $viewsCount = 0;
  private string $name;
  private bool $isDataFullyInitialized = false;
  private array $initializedColumns = [];
  
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
    if ($this->isDataFullyInitialized) {
      return;
    }
    
    if ($columns !== ['*'] && empty(array_diff($columns, $this->initializedColumns))) {
      return;
    }
    
    $columnsToLoad = $this->isDataFullyInitialized 
      ? array_diff($columns, $this->initializedColumns) 
      : $columns;
    
    $columnsData = $this->getDatabaseColumnsData($columnsToLoad);
    
    if ($columnsData !== null) {
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
      
      if ($columns === ['*']) {
        $this->isDataFullyInitialized = true;
      } else {
        $this->initializedColumns = array_merge($this->initializedColumns, $columns);
      }
    }
  }
  
  /**
   * Получить ID страницы
   *
   * @param  mixed $value
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
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
   * Получить временную отметку создания страницы в UNIX-формате
   * 
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить временную отметку обновления страницы в UNIX-формате
   * 
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Получить ID автора страницы
   * 
   * @return int
   */
  public function getAuthorID() : int
  {
    return $this->authorID ?? 0;
  }
  
  /**
   * Получить заголовок страницы
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
   * Получить SEO-заголовок страницы
   *
   * @param  string $localeName Наименование локализации
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
   * Получить объект автора страницы
   * 
   * @param array $initData
   * 
   * @return User
   */
  public function getAuthor(array $initData = ['*']) : User|null
  {
    $authorID = $this->getAuthorID();

    if (User::existsByID($this->CMSCore, $authorID)) {
      $author = new User($this->CMSCore, $authorID);
      $author->initData($initData);

      return $author;
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
   * Получить описание страницы
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
   * Получить SEO-описание страницы
   *
   * @param  string $localeName Наименование локализации
   * 
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
   * Получить содержимое страницы
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
   * Получить ключевые слова
   *
   * @param  mixed $localeName Наименование локализации
   * 
   * @return array
   */
  public function getKeywords(string $localeName = 'en_US') : array
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
   * Получить статус публикации страницы
   *
   * @return bool
   */
  public function isPublished() : bool
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['isPublished']) && $this->getPublishedUnixTimestamp() <= time()) {
        return (bool) $metadata['isPublished'];
      }
    }

    return false;
  }

  /**
   * Получить временную отметку публикации страницы в UNIX-формате
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
   * Получить путь до персонального шаблона
   * 
   * @return string
   */
  public function getPersonalTemplatePath() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      
      if (isset($metadata['personalTemplatePath'])) {
        $personalTemplatePath = trim($metadata['personalTemplatePath']);

        return $personalTemplatePath !== '' ? $personalTemplatePath : 'templates/page/static.tpl';
      }
    }

    return 'templates/page/static.tpl';
  }

  /**
   * Проверить наличие файла персонального шаблона
   * 
   * @return bool
   */
  public function existsPersonalTemplateFile() : bool
  {
    if (property_exists($this, 'metadata')) {
      $themePath = $this->CMSCore->theme->getPath() . '/templates/' . $this->getPersonalTemplatePath();
      return file_exists($themePath);
    }

    return false;
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
    return '/' . $CMSCore->theme->getURL() . '/images/pageStatic/default_' . (string) $size . '.png';
  }
  
  /**
   * Получить имя страницы
   *
   * @return void
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }
  
  /**
   * Получить URL до страницы
   *
   * @return void
   */
  public function getURL() {
    return '/page/' . $this->getName();
  }
  
  /**
   * Получить данные колонок страницы в базе данных
   *
   * @param  mixed $columns
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
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $pageStaticID Идентификационный номер страницы */
    $pageStaticID = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $pageStaticID, \PDO::PARAM_INT);
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
   * Получить объект страницы по его наименованию
   *
   * @param  mixed $CMSCore
   * @param  string $pageStaticName
   * 
   * @return PageStatic
   */
  public static function getByName(SystemCore $CMSCore, string $pageStaticName) : PageStatic|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
      $databaseQuery->bindParam(':name', $pageStaticName, \PDO::PARAM_STR);
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
    return $result ? new PageStatic($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Проверка наличия страницы
   *
   * @param  mixed $CMSCore
   * @param  string $pageStaticName
   * 
   * @return bool
   */
  public static function existsByName(SystemCore $CMSCore, string $pageStaticName) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
      $databaseQuery->bindParam(':name', $pageStaticName, \PDO::PARAM_STR);
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
   * Проверка наличия страницы по идентификационному номеру
   *
   * @param  mixed $CMSCore
   * @param  int $pageStaticID
   * 
   * @return bool
   */
  public static function existsByID(SystemCore $CMSCore, int $pageStaticID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
      $databaseQuery->bindParam(':id', $pageStaticID, \PDO::PARAM_INT);
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
   * Удаление существующей страницы
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
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
   * Создание новой страницы
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * @param  int $authorID
   * @param  array $categoryID
   * @param  array $texts
   * 
   * @return PageStatic|null
   */
  public static function create(SystemCore $CMSCore, string $name, int $authorID, array $texts, array $metadata = []) : PageStatic|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('pages_static');
    $queryBuilder->statement->addColumn('authorID');
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
      $databaseQuery->bindParam(':authorID', $authorID, \PDO::PARAM_INT);
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
      $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
      return $result ? new PageStatic($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующей страницы
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
    $queryBuilder->statement->setTable('pages_static');
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
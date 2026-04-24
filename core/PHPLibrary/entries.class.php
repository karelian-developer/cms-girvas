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

use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\QueryBuilder\Expression\CaseExpression as CaseExpression;

final class Entries
{
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {}
      
  /**
   * Получить все объекты записей
   *
   * @param   array $params
   * @param   bool 
   * @return  array
   */
  public function getAll(array $params = [], $isPublished = false) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($isPublished) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => '(metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = (is_integer($params['limit'][0])) ? $params['limit'][0] : 0;
        $offset = (is_integer($params['limit'][1])) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->execute();

    $entries = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($entries, new Entry($this->CMSCore, $data['id']));
      }
    }

    return $entries;
  }

  /**
   * Получить объекты записей для нескольких категорий
   *
   * @param array $categoriesIDs Массив ID категорий
   * @param array $params Параметры (limit, offset)
   * @param bool $isPublished Только опубликованные
   * @return array Массив объектов Entry
   */
  public function getByCategoriesIDs(array $categoriesIDs, array $params = [], bool $isPublished = false) : array
  {
    if (empty($categoriesIDs)) {
      return [];
    }

    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    
    // Формируем IN-условие для массива ID
    $placeholders = [];
    foreach ($categoriesIDs as $index => $categoryID) {
      $placeholders[] = ':categoryID' . $index;
    }
    
    $inCondition = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => '"categoryID" IN (' . implode(', ', $placeholders) . ')',
      CMSDMS::MySQL => '`categoryID` IN (' . implode(', ', $placeholders) . ')'
    };
    
    $queryBuilder->statement->clauseWhere->addCondition($inCondition);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    
    foreach ($categoriesIDs as $index => $categoryID) {
      $databaseQuery->bindParam(':categoryID' . $index, $categoriesIDs[$index], \PDO::PARAM_INT);
    }
    
    $databaseQuery->execute();

    $entries = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        $entries[] = new Entry($this->CMSCore, $data['id']);
      }
    }

    return $entries;
  }

  /**
   * Получить количество записей для нескольких категорий
   *
   * @param array $categoriesIDs Массив ID категорий
   * @param bool $isPublished Только опубликованные
   * @return int Количество записей
   */
  public function getCountByCategoriesIDs(array $categoriesIDs, bool $isPublished = false) : int
  {
    if (empty($categoriesIDs)) {
      return 0;
    }

    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    
    $placeholders = [];
    foreach ($categoriesIDs as $index => $categoryID) {
      $placeholders[] = ':categoryID' . $index;
    }
    
    $inCondition = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => '"categoryID" IN (' . implode(', ', $placeholders) . ')',
      CMSDMS::MySQL => '`categoryID` IN (' . implode(', ', $placeholders) . ')'
    };
    
    $queryBuilder->statement->clauseWhere->addCondition($inCondition);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    
    foreach ($categoriesIDs as $index => $categoryID) {
      $databaseQuery->bindParam(':categoryID' . $index, $categoriesIDs[$index], \PDO::PARAM_INT);
    }
    
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? (int)$result['count'] : 0;
  }

  /**
   * Поиск записей по строке запроса с учётом локализации
   *
   * @param string $searchQuery Строка поискового запроса
   * @param string $localeName Имя локали (например: 'ru_RU', 'en_US')
   * @param array $params Дополнительные параметры (limit, offset)
   * @param bool $isPublished Только опубликованные записи
   * 
   * @return array Массив объектов Entry, отсортированных по релевантности
   */
  public function search(
    string $searchQuery,
    string $localeName = 'en_US',
    array $params = [],
    bool $isPublished = false
  ) : array {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    // Разбиваем запрос на слова, убираем знаки препинания
    $words = preg_split('/[\s,.;!?()\[\]{}<>]+/', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
    
    // Если слов нет — возвращаем пустой массив
    if (empty($words)) {
        return [];
    }
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    
    // Вес для различных полей при поиске
    $weights = [
        'title' => 10,
        'SEOTitle' => 8,
        'description' => 5,
        'SEODescription' => 5,
        'content' => 3
    ];
    
    // Создаём CASE-выражения для каждого слова и каждого поля
    $allCaseExpressions = [];
    
    foreach ($words as $wordIndex => $word) {
        $paramName = 'word' . $wordIndex;
        
        foreach ($weights as $field => $weight) {
            $jsonPath = sprintf("texts->'%s'->>'%s'", $localeName, $field);
            $allCaseExpressions[] = $queryBuilder->createCase()
                ->whenJsonLike($jsonPath, $paramName, $weight)
                ->else(0);
        }
        
        // Поиск по ключевым словам
        $keywordsPath = sprintf("texts->'%s'->'keywords'", $localeName);
        $allCaseExpressions[] = $queryBuilder->createCase()
            ->whenJsonArrayContains($keywordsPath, $paramName, 6)
            ->else(0);
    }
    
    $relevanceExpression = CaseExpression::sum($allCaseExpressions, 'relevance');
    $queryBuilder->statement->addSelections(['*', $relevanceExpression]);
    
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    // Формируем WHERE-условия: каждое слово должно быть найдено хотя бы в одном поле
    $wordConditions = [];
    
    foreach ($words as $wordIndex => $word) {
      $paramName = 'word' . $wordIndex;
      $fieldConditions = [];
      
      foreach (array_keys($weights) as $field) {
        $fieldConditions[] = match ($CMSConfigDatabase['dms']) {
          CMSDMS::PostgreSQL => sprintf(
            "texts->'%s'->>'%s' ILIKE '%%' || :%s || '%%'",
            $localeName,
            $field,
            $paramName
          ),
          CMSDMS::MySQL => sprintf(
            "JSON_UNQUOTE(JSON_EXTRACT(texts, '$.%s.%s')) LIKE CONCAT('%%', :%s, '%%')",
            $localeName,
            $field,
            $paramName
          )
        };
      }
      
      $fieldConditions[] = match ($CMSConfigDatabase['dms']) {
        CMSDMS::PostgreSQL => sprintf(
          "EXISTS (SELECT 1 FROM jsonb_array_elements_text(texts->'%s'->'keywords') AS kw WHERE kw ILIKE '%%' || :%s || '%%')",
          $localeName,
          $paramName
        ),
        CMSDMS::MySQL => sprintf(
          "JSON_SEARCH(texts, 'one', :%s, NULL, '$.%s.keywords[*]') IS NOT NULL",
          $paramName,
          $localeName
        )
      };
      
      $wordConditions[] = '(' . implode(' OR ', $fieldConditions) . ')';
    }
    
    // Все слова должны быть найдены (AND)
    $whereString = '(' . implode(' AND ', $wordConditions) . ')';
    
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition($whereString);
    
    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }
    
    $queryBuilder->statement->clauseWhere->assembly();
    
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('relevance');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->clauseOrderBy->assembly();
    
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    
    $queryBuilder->statement->assembly();
    
    // Модифицируем ORDER BY для поддержки второй колонки (id)
    $queryBuilder->statement->assembled = str_replace(
      'ORDER BY "relevance" DESC',
      'ORDER BY relevance DESC, id',
      $queryBuilder->statement->assembled
    );
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    
    // Биндим параметры для каждого слова
    foreach ($words as $wordIndex => $word) {
      $paramName = ':word' . $wordIndex;
      $databaseQuery->bindParam($paramName, $words[$wordIndex], \PDO::PARAM_STR);
    }
    
    $databaseQuery->execute();
    
    $entries = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
          $entries[] = new Entry($this->CMSCore, $data['id']);
      }
    }
    
    return $entries;
  }

  /**
   * Получить записи за определённый период
   *
   * @param int $startTimestamp Начальная метка времени
   * @param int $endTimestamp Конечная метка времени
   * @param string $localeName Имя локали
   * @param array $params Дополнительные параметры (limit, offset, categoryID)
   * @param bool $isPublished Только опубликованные записи
   * @return array Массив объектов Entry
   */
  public function getByDateRange(
    int $startTimestamp,
    int $endTimestamp,
    string $localeName = 'en_US',
    array $params = [],
    bool $isPublished = false
  ) : array {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    // WHERE: фильтр по дате
    $queryBuilder->statement->setClauseWhere();
    
    $dateCondition = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => sprintf(
        '"createdUnixTimestamp" >= %d AND "createdUnixTimestamp" <= %d',
        $startTimestamp,
        $endTimestamp
      ),
      CMSDMS::MySQL => sprintf(
        '`createdUnixTimestamp` >= %d AND `createdUnixTimestamp` <= %d',
        $startTimestamp,
        $endTimestamp
      )
    };
    
    $queryBuilder->statement->clauseWhere->addCondition($dateCondition);
    
    // Дополнительный фильтр по категории
    if (isset($params['categoryID']) && $params['categoryID'] > 0) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND `categoryID` = :categoryID',
        'postgresql' => 'AND "categoryID" = :categoryID'
      ]);
    }
    
    // Фильтр по опубликованности
    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }
    
    $queryBuilder->statement->clauseWhere->assembly();
    
    // ORDER BY
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->clauseOrderBy->assembly();
    
    // LIMIT и OFFSET
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    
    $queryBuilder->statement->assembly();
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    
    if (isset($params['categoryID']) && $params['categoryID'] > 0) {
      $databaseQuery->bindParam(':categoryID', $params['categoryID'], \PDO::PARAM_INT);
    }
    
    $databaseQuery->execute();
    
    $entries = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        $entries[] = new Entry($this->CMSCore, $data['id']);
      }
    }
    
    return $entries;
  }

  /**
   * Получить количество записей за определённый период
   *
   * @param int $startTimestamp Начальная метка времени
   * @param int $endTimestamp Конечная метка времени
   * @param array $params Дополнительные параметры (categoryID)
   * @param bool $isPublished Только опубликованные записи
   * @return int Количество записей
   */
  public function getCountByDateRange(
    int $startTimestamp,
    int $endTimestamp,
    array $params = [],
    bool $isPublished = false
  ) : int {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    $queryBuilder->statement->setClauseWhere();
    
    $dateCondition = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => sprintf(
        '"createdUnixTimestamp" >= %d AND "createdUnixTimestamp" <= %d',
        $startTimestamp,
        $endTimestamp
      ),
      CMSDMS::MySQL => sprintf(
        '`createdUnixTimestamp` >= %d AND `createdUnixTimestamp` <= %d',
        $startTimestamp,
        $endTimestamp
      )
    };
    
    $queryBuilder->statement->clauseWhere->addCondition($dateCondition);
    
    if (isset($params['categoryID']) && $params['categoryID'] > 0) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND `categoryID` = :categoryID',
        'postgresql' => 'AND "categoryID" = :categoryID'
      ]);
    }
    
    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }
    
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    
    if (isset($params['categoryID']) && $params['categoryID'] > 0) {
      $databaseQuery->bindParam(':categoryID', $params['categoryID'], \PDO::PARAM_INT);
    }
    
    $databaseQuery->execute();
    
    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? (int)$result['count'] : 0;
  }

  /**
   * Получить список доступных годов с записями
   *
   * @param bool $isPublished Только опубликованные записи
   * @return array Массив годов
   */
  public function getAvailableYears(bool $isPublished = false) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    
    $yearExpression = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => 'EXTRACT(YEAR FROM to_timestamp("createdUnixTimestamp")) AS year',
      CMSDMS::MySQL => 'YEAR(FROM_UNIXTIME(`createdUnixTimestamp`)) AS year'
    };
    
    $queryBuilder->statement->addSelections(['DISTINCT ' . $yearExpression, 'COUNT(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    if ($isPublished) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => '(metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }
    
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('year');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->clauseOrderBy->assembly();
    $queryBuilder->statement->assembly();
    
    // GROUP BY
    $queryBuilder->statement->assembled = str_replace(
      'ORDER BY',
      'GROUP BY year ORDER BY',
      $queryBuilder->statement->assembled
    );
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->execute();
    
    return $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Получить список месяцев с записями за указанный год
   *
   * @param int $year Год
   * @param bool $isPublished Только опубликованные записи
   * @return array Массив месяцев
   */
  public function getAvailableMonths(int $year, bool $isPublished = false) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $startTimestamp = mktime(0, 0, 0, 1, 1, $year);
    $endTimestamp = mktime(23, 59, 59, 12, 31, $year);
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    
    $monthExpression = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => 'EXTRACT(MONTH FROM to_timestamp("createdUnixTimestamp")) AS month',
      CMSDMS::MySQL => 'MONTH(FROM_UNIXTIME(`createdUnixTimestamp`)) AS month'
    };
    
    $queryBuilder->statement->addSelections(['DISTINCT ' . $monthExpression, 'COUNT(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    $queryBuilder->statement->setClauseWhere();
    
    $dateCondition = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => sprintf(
        '"createdUnixTimestamp" >= %d AND "createdUnixTimestamp" <= %d',
        $startTimestamp,
        $endTimestamp
      ),
      CMSDMS::MySQL => sprintf(
        '`createdUnixTimestamp` >= %d AND `createdUnixTimestamp` <= %d',
        $startTimestamp,
        $endTimestamp
      )
    };
    
    $queryBuilder->statement->clauseWhere->addCondition($dateCondition);
    
    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }
    
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('month');
    $queryBuilder->statement->clauseOrderBy->setSortType('ASC');
    $queryBuilder->statement->clauseOrderBy->assembly();
    $queryBuilder->statement->assembly();
    
    // GROUP BY
    $queryBuilder->statement->assembled = str_replace(
      'ORDER BY',
      'GROUP BY month ORDER BY',
      $queryBuilder->statement->assembled
    );
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->execute();
    
    return $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
  }
      
  /**
   * Получить объекты записей для определенной категории
   *
   * @param  int $id
   * @param  array $params
   * @return array
   */
  public function getByCategoryID(int $id, array $params = [], $isPublished = false) : array
  {
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
      'mysql' => '`categoryID` = :categoryID',
      'postgresql' => '"categoryID" = :categoryID'
    ]);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':categoryID', $id, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $entries = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($entries, new Entry($this->CMSCore, $data['id']));
      }
    }

    return $entries;
  }

  /**
   * Получить количество записей по поисковому запросу
   *
   * @param string $searchQuery Строка поискового запроса
   * @param string $localeName Имя локали (например: 'ru_RU', 'en_US')
   * @param bool $isPublished Только опубликованные записи
   * 
   * @return int Количество найденных записей
   */
  public function getCountBySearch(
    string $searchQuery,
    string $localeName = 'en_US',
    bool $isPublished = false
  ) : int {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    $weights = ['title', 'SEOTitle', 'description', 'SEODescription', 'content'];
    
    $whereConditions = [];
    
    foreach ($weights as $field) {
      $whereConditions[] = match ($CMSConfigDatabase['dms']) {
        CMSDMS::PostgreSQL => sprintf(
          "texts->'%s'->>'%s' ILIKE '%%' || :searchQuery || '%%'",
          $localeName,
          $field
        ),
        CMSDMS::MySQL => sprintf(
          "JSON_UNQUOTE(JSON_EXTRACT(texts, '$.%s.%s')) LIKE CONCAT('%%', :searchQuery, '%%')",
          $localeName,
          $field
        )
      };
    }
    
    $whereConditions[] = match ($CMSConfigDatabase['dms']) {
      CMSDMS::PostgreSQL => sprintf(
        "EXISTS (SELECT 1 FROM jsonb_array_elements_text(texts->'%s'->'keywords') AS kw WHERE kw ILIKE '%%' || :searchQuery || '%%')",
        $localeName
      ),
      CMSDMS::MySQL => sprintf(
        "JSON_SEARCH(texts, 'one', :searchQuery, NULL, '$.%s.keywords[*]') IS NOT NULL",
        $localeName
      )
    };
    
    $whereString = '(' . implode(' OR ', $whereConditions) . ')';
    
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition($whereString);
    
    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }
    
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':searchQuery', $searchQuery, \PDO::PARAM_STR);
    $databaseQuery->execute();
    
    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? (int)$result['count'] : 0;
  }
      
  /**
   * Получить количество записей для определенной категории
   *
   * @param  int $id
   * @return int
   */
  public function getCountByCategoryID(int $id, $isPublished = false) : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`categoryID` = :categoryID',
      'postgresql' => '"categoryID" = :categoryID'
    ]);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':categoryID', $id, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return ($result) ? $result['count'] : 0;
  }
      
  /**
   * Получить общее количество записей
   *
   * @return int
   */
  public function getCountTotal($isPublished = false) : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($isPublished) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => '(metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result['count'] : 0;
  }
}
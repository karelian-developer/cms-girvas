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
    
    $caseExpressions = [];
    
    foreach ($weights as $field => $weight) {
      $jsonPath = sprintf("texts->'%s'->>'%s'", $localeName, $field);
      $caseExpressions[] = $queryBuilder->createCase()
        ->whenJsonLike($jsonPath, 'searchQuery', $weight)
        ->else(0);
    }
    
    // Поиск по ключевым словам (вес 6)
    $keywordsPath = sprintf("texts->'%s'->'keywords'", $localeName);
    $caseExpressions[] = $queryBuilder->createCase()
      ->whenJsonArrayContains($keywordsPath, 'searchQuery', 6)
      ->else(0);
    
    $relevanceExpression = CaseExpression::sum($caseExpressions, 'relevance');
    
    $queryBuilder->statement->addSelections(['*', $relevanceExpression]);
    
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    
    $whereConditions = [];
    
    foreach (array_keys($weights) as $field) {
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

    error_log('=== SEARCH QUERY ===');
    error_log('Search value: ' . $searchQuery);
    error_log('Locale: ' . $localeName);
    error_log('SQL: ' . $queryBuilder->statement->assembled);
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':searchQuery', $searchQuery, \PDO::PARAM_STR);
    
    $executed = $databaseQuery->execute();
    error_log('Execute result: ' . ($executed ? 'true' : 'false'));
    error_log('Row count: ' . $databaseQuery->rowCount());
    
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    error_log('Results: ' . json_encode($results, JSON_UNESCAPED_UNICODE));
    
    // Модифицируем ORDER BY для поддержки второй колонки (id)
    $queryBuilder->statement->assembled = str_replace(
      'ORDER BY "relevance" DESC',
      'ORDER BY relevance DESC, id',
      $queryBuilder->statement->assembled
    );
    
    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':searchQuery', $searchQuery, \PDO::PARAM_STR);
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
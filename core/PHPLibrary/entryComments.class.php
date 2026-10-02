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
use \PDOException as PDOException;

final class EntryComments
{
  /**
   * Допустимые правила сортировки
   */
  private const SORT_RULES = [
    'by_createdtimestamp_increase' => ['column' => 'createdUnixTimestamp', 'direction' => 'ASC'],
    'by_createdtimestamp_decrease' => ['column' => 'createdUnixTimestamp', 'direction' => 'DESC'],
    'by_updatedtimestamp_increase' => ['column' => 'updatedUnixTimestamp', 'direction' => 'ASC'],
    'by_updatedtimestamp_decrease' => ['column' => 'updatedUnixTimestamp', 'direction' => 'DESC'],
  ];

  public const DEFAULT_SORT_RULE = 'by_createdtimestamp_decrease';

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
   * Получить все объекты комментариев
   *
   * @param  array  $params
   * @param  string $searchValue — поиск по content
   * @param  string $sortRule    — одно из SORT_RULES
   * @return array
   */
  public function getAll(
    array $params = [],
    string $searchValue = '',
    string $sortRule = self::DEFAULT_SORT_RULE
  ) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();

    // Поиск по content
    $hasSearch = $searchValue !== '';
    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql'      => '`content` LIKE :search',
        'postgresql' => '"content" ILIKE :search'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    // Сортировка
    $sortConfig = self::SORT_RULES[$sortRule] ?? self::SORT_RULES[self::DEFAULT_SORT_RULE];

    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn($sortConfig['column']);
    $queryBuilder->statement->clauseOrderBy->setSortType($sortConfig['direction']);

    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      if ($hasSearch) {
        $databaseQuery->bindValue(':search', '%' . $searchValue . '%', \PDO::PARAM_STR);
      }

      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $entriesComments = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($entriesComments, new EntryComment($this->CMSCore, $data['id']));
      }
    }

    return $entriesComments;
  }
      
  /**
   * Получить объекты комментариев для определенной записи
   *
   * @param  int $entryID
   * @param  array $params
   * @return array
   */
  public function getByEntryID(int $entryID, array $params = []) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf('%s = :entryID', $queryBuilder->dialect->quoteIdentifier('entryID'))
    );
    if (array_key_exists('parentID', $params)) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => sprintf('AND JSON_EXTRACT(`metadata`, \'$.parentID\') = %d', $params['parentID']),
        'postgresql' => sprintf('AND (metadata::jsonb->\'parentID\')::int = %d', $params['parentID'])
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    if (array_key_exists('limit', $params)) {
      if (is_array($params['limit'])) {
        $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
        $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
        $queryBuilder->statement->setClauseLimit($limit, $offset);
      }
    }

    if (array_key_exists('orderBy', $params)) {
      if (isset($params['orderBy']['column']) && isset($params['orderBy']['sort'])) {
        $queryBuilder->statement->setClauseOrderBy();
        $queryBuilder->statement->clauseOrderBy->setColumn($params['orderBy']['column']);
        $queryBuilder->statement->clauseOrderBy->setSortType($params['orderBy']['sort']);
        $queryBuilder->statement->clauseOrderBy->assembly();
      }
    }

    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':entryID', $entryID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $entriesComments = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($entriesComments, new EntryComment($this->CMSCore, $data['id']));
      }
    }

    return $entriesComments;
  }
      
  /**
   * Получить количество комментариев для определенной записи
   *
   * @param  int $entryID
   * @return int
   */
  public function getCountByEntryID(int $entryID) : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf('%s = :entryID', $queryBuilder->dialect->quoteIdentifier('entryID'))
    );
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':entryID', $entryID, \PDO::PARAM_INT);
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
    return $result ? $result['count'] : 0;
  }
      
  /**
   * Получить общее количество комментариев (с учётом поиска по content)
   *
   * @param  string $searchValue
   * @return int
   */
  public function getCountTotal(string $searchValue = '') : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();

    $hasSearch = $searchValue !== '';
    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql'      => '`content` LIKE :search',
        'postgresql' => '"content" ILIKE :search'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      if ($hasSearch) {
        $databaseQuery->bindValue(':search', '%' . $searchValue . '%', \PDO::PARAM_STR);
      }

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
    return $result ? (int) $result['count'] : 0;
  }
}
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
use \PDOException as PDOException;

final class EntryComments
{
  private SystemCore $CMSCore;

  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * @return void
   */
  public function __construct(SystemCore $CMSCore) {
    $this->CMSCore = $CMSCore;
  }
      
  /**
   * Получить все объекты комментариев
   *
   * @param  array $params
   * @return array
   */
  public function getAll(array $params = []) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('id');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
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
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`entryID` = :entryID',
      'postgresql' => '"entryID" = :entryID'
    ]);
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
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`entryID` = :entryID',
      'postgresql' => '"entryID" = :entryID'
    ]);
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
   * Получить общее количество комментариев
   *
   * @return int
   */
  public function getCountTotal() : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
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

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result['count'] : 0;
  }


}
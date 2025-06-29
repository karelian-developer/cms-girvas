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

final class Entries
{
  private SystemCore $CMSCore;
  
  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * @return void
   */
  public function __construct(SystemCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;
  }
      
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
      $queryBuilder->statement->clauseWhere->addCondition('(metadata::jsonb->>\'isPublished\')::boolean = true');
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
   * Получить объекты записей для определенной категории
   *
   * @param  int $id
   * @param  array $params
   * @return array
   */
  public function getByCategoryID(int $id, array $params = [], $isPublished = false) : array
  {
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"categoryID" = :categoryID');

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addCondition('AND (metadata::jsonb->>\'isPublished\')::boolean = true');
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
   * Получить количество записей для определенной категории
   *
   * @param  int $id
   * @return int
   */
  public function getCountByCategoryID(int $id, $isPublished = false) : int
  {
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*)']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"categoryID" = :categoryID');

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addCondition('AND (metadata::jsonb->>\'isPublished\')::boolean = true');
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
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*)']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($isPublished) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('(metadata::jsonb->>\'isPublished\')::boolean = true');
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
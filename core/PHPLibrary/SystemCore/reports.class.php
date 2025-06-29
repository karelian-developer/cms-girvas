<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\SystemCore as SystemCore;

final class Reports
{
  private readonly SystemCore $CMSCore;

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
   * Получить все объекты отчетов
   *
   * @param  array $paramsArray
   * 
   * @return array
   */
  public function getAll(array $paramsArray = []) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('created_unix_timestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    if (array_key_exists('limit', $paramsArray)) {
      if (is_array($paramsArray['limit'])) {
        $limit = is_integer($paramsArray['limit'][0]) ? $paramsArray['limit'][0] : 0;
        $offset = is_integer($paramsArray['limit'][1]) ? $paramsArray['limit'][1] : 0;
        $queryBuilder->statement->set_clause_limit($limit, $offset);
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

    $resultArray = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($resultArray, new Report($this->CMSCore, $data['id']));
      }
    }

    return $resultArray;
  }

  /**
   * Получить объекты отчетов определенного типа
   *
   * @param  int $typeID
   * @param  array $paramsArray
   * 
   * @return array
   */
  public function getByTypeIDs(array $typeIDs, array $paramsArray = []) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $conditionTypeIDs = [];
    foreach ($typeIDs as $typeID) {
      array_push($conditionTypeIDs, sprintf('(metadata::jsonb->>\'typeID\')::int = %d', $typeID));
    }

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(implode(' OR ', $conditionTypeIDs));
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('created_unix_timestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    if (array_key_exists('limit', $paramsArray)) {
      if (is_array($paramsArray['limit'])) {
        $limit = is_integer($paramsArray['limit'][0]) ? $paramsArray['limit'][0] : 0;
        $offset = is_integer($paramsArray['limit'][1]) ? $paramsArray['limit'][1] : 0;
        $queryBuilder->statement->set_clause_limit($limit, $offset);
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

    $resultArray = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($resultArray, new Report($this->CMSCore, $data['id']));
      }
    }

    return $resultArray;
  }
}
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
   * Получить объекты отчетов за конкретный период конкретного типа
   * 
   * @param CMSCore $CMSCore
   * @param int $typeID
   * @param int $startPeriodUnix
   * @param int $endPeriodUnix
   * 
   * @return array
   */
  public static function getByPeriod(CMSCore $CMSCore, int $typeID, int $startPeriodUnix, int $endPeriodUnix) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`createdUnixTimestamp` BETWEEN :startPeriodUnix AND :endPeriodUnix AND JSON_UNQUOTE(JSON_EXTRACT(metadata, \'$.typeID\')) IS NOT NULL AND CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, \'$.typeID\')) AS UNSIGNED) = :typeID',
      'postgresql' => '"createdUnixTimestamp" BETWEEN :startPeriodUnix AND :endPeriodUnix AND (metadata::jsonb->>\'typeID\') IS NOT NULL AND (metadata::jsonb->>\'typeID\')::integer = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':startPeriodUnix', $startPeriodUnix, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':endPeriodUnix', $endPeriodUnix, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $reports = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($reports, new Report($this->CMSCore, $data['id']));
      }
    }

    return $reports;
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
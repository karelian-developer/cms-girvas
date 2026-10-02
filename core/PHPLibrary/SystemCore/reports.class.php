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

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;

final class Reports
{
  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore
  ) {}

  /**
   * Получить все объекты отчетов
   *
   * @param  array $paramsArray
   * 
   * @return array
   */
  public function getAll(array $paramsArray = [], array $columnsScope = ['id']) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columnsScope);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');

    if (array_key_exists('limit', $paramsArray)) {
      if (is_array($paramsArray['limit'])) {
        $limit = is_integer($paramsArray['limit'][0]) ? $paramsArray['limit'][0] : 0;
        $offset = is_integer($paramsArray['limit'][1]) ? $paramsArray['limit'][1] : 0;
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
   * @param CoreInterface $CMSCore
   * @param int $typeID
   * @param int $startPeriodUnix
   * @param int $endPeriodUnix
   * 
   * @return array
   */
  public static function getByPeriod(CoreInterface $CMSCore, int $typeID, int $startPeriodUnix, int $endPeriodUnix, array $columnsScope = ['id']) : array
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columnsScope);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    $dialect = $queryBuilder->dialect;

    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s BETWEEN :startPeriodUnix AND :endPeriodUnix AND %s IS NOT NULL AND %s = :typeID',
        $dialect->quoteIdentifier('createdUnixTimestamp'),
        $dialect->jsonExtractInt('metadata', 'typeID'),
        $dialect->jsonExtractInt('metadata', 'typeID')
      )
    );

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
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
        $reports[] = new Report($CMSCore, $data['id']);
      }
    }

    return $reports;
  }

  /**
   * Получить объекты отчетов за конкретный период конкретного типа
   * 
   * @param CoreInterface $CMSCore
   * @param int $startPeriodUnix
   * @param int $endPeriodUnix
   * 
   * @return array
   */
  public static function getAllByPeriod(CoreInterface $CMSCore, int $startPeriodUnix, int $endPeriodUnix, array $columnsScope = ['id']) : array
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s BETWEEN :startPeriodUnix AND :endPeriodUnix',
        $queryBuilder->dialect->quoteIdentifier('createdUnixTimestamp')
      )
    );
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':startPeriodUnix', $startPeriodUnix, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':endPeriodUnix', $endPeriodUnix, \PDO::PARAM_INT);
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
        $report = new Report($CMSCore, $data['id']);
        $report->initData($columnsScope);

        $reports[] = $report;
      }
    }

    return $reports;
  }

  /**
   * Получить отчёты, связанные с пользователем (как субъектом ПДн)
   *
   * @param CoreInterface $CMSCore
   * @param int $userID
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public static function getAllByUser(
    CoreInterface $CMSCore,
    int $userID,
    int $limit = 10000,
    int $offset = 0
  ) : array {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    $dialect = $queryBuilder->dialect;
    $jsonColumns = ['targetUserID', 'userID', 'subjectUserID', 'viewedByID'];
    $parts = [];
    $bindings = [];
    
    foreach ($jsonColumns as $i => $key) {
      $param = ':userID_' . $i;
      $parts[] = sprintf('%s = %s', $dialect->jsonExtractInt('variables', $key), $param);
      $bindings[$param] = $userID;
    }

    $queryBuilder->statement->clauseWhere->addCondition('(' . implode(' OR ', $parts) . ')');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->setClauseLimit($limit, $offset);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      foreach ($bindings as $param => $value) {
        $databaseQuery->bindValue($param, $value, \PDO::PARAM_INT);
      }
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $reports = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        $reports[] = new Report($CMSCore, (int) $data['id']);
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

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    
    $conditionTypeIDs = [];
    foreach ($typeIDs as $typeID) {
      $conditionTypeIDs[] = sprintf(
        '%s = %d',
        $queryBuilder->dialect->jsonExtractInt('metadata', 'typeID'),
        (int) $typeID
      );
    }

    $conditionTypeIDsImploded = implode(' OR ', $conditionTypeIDs);

    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition($conditionTypeIDsImploded);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('createdUnixTimestamp');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');

    if (array_key_exists('limit', $paramsArray)) {
      if (is_array($paramsArray['limit'])) {
        $limit = is_integer($paramsArray['limit'][0]) ? $paramsArray['limit'][0] : 0;
        $offset = is_integer($paramsArray['limit'][1]) ? $paramsArray['limit'][1] : 0;
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

    $resultArray = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        $resultArray[] = new Report($this->CMSCore, $data['id']);
      }
    }

    return $resultArray;
  }
}
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
use \core\PHPLibrary\SystemCore\Report as Report;
use \PDOException as PDOException;

final class Users
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
   * Получить все объекты пользователей
   * 
   * @param array $params
   * @param User|null $viewer Текущий пользователь (для логирования)
   * 
   * @return array
   */
  public function getAll(array $params = [], ?User $viewer = null) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
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
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $users = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($users, new User($this->CMSCore, $data['id']));
      }
    }

    if ($viewer !== null) {
      Report::create(
        $this->CMSCore,
        Report::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
        [
          'action' => 'list_view',
          'viewedByID' => $viewer->getID(),
          'viewedByLogin' => $viewer->getLogin(),
          'count' => count($users),
          'ip' => $this->CMSCore->client->getIPAddress()
        ]
      );
    }

    return $users;
  }

  /**
   * Получить количество пользователей для определенной группы
   *
   * @param  int $groupID
   * 
   * @return int
   */
  public function getCountByGroupID(int $groupID) : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.groupID\') = :groupID',
      'postgresql' => '(metadata::jsonb->>\'groupID\')::int = :groupID'
    ]);

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':groupID', $groupID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
  }

  /**
   * Получить общее количество
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
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
  }
}
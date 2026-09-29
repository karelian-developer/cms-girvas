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
   * Соответствие правил сортировки и SQL-выражений (адаптивно под DMS)
   */
  private const SORT_RULES = [
    'by_createdtimestamp_increase' => ['column' => 'createdUnixTimestamp', 'direction' => 'ASC'],
    'by_createdtimestamp_decrease' => ['column' => 'createdUnixTimestamp', 'direction' => 'DESC'],
    'by_alphabet_increase' => ['column' => 'login', 'direction' => 'ASC'],
    'by_alphabet_decrease' => ['column' => 'login', 'direction' => 'DESC'],
  ];

  public function __construct(
    private CoreInterface $CMSCore
  ) {}

  /**
   * Получить все объекты пользователей
   *
   * @param array $params
   *   - limit: [int $limit, int $offset]
   *   - search: string — подстрока для поиска по login/email
   *   - sort: string — одно из SORT_RULES
   * @param User|null $viewer
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

    // Поиск
    $searchValue = $params['search'] ?? '';
    $hasSearch = is_string($searchValue) && $searchValue !== '';

    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $dialect = $queryBuilder->dialect;

      $queryBuilder->statement->clauseWhere->addCondition(
        sprintf(
          '(%s OR %s)',
          $dialect->stringLike('login', 'search', true),
          $dialect->stringLike('email', 'search', true)
        )
      );
      $queryBuilder->statement->clauseWhere->assembly();
    }

    // Сортировка
    $sortRule = $params['sort'] ?? 'by_createdtimestamp_decrease';
    $sortConfig = self::SORT_RULES[$sortRule] ?? self::SORT_RULES['by_createdtimestamp_decrease'];

    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn($sortConfig['column']);
    $queryBuilder->statement->clauseOrderBy->setSortType($sortConfig['direction']);

    if (array_key_exists('limit', $params) && is_array($params['limit'])) {
      $limit = is_integer($params['limit'][0]) ? $params['limit'][0] : 0;
      $offset = is_integer($params['limit'][1]) ? $params['limit'][1] : 0;
      $queryBuilder->statement->setClauseLimit($limit, $offset);
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
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $users = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        $users[] = new User($this->CMSCore, (int) $data['id']);
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
          'search' => $searchValue,
          'sort' => $sortRule,
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
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :groupID',
        $queryBuilder->dialect->jsonExtractInt('metadata', 'groupID')
      )
    );
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
   * Получить общее количество пользователей.
   * Если передан $searchValue — считаем только совпадающих по login/email.
   *
   * @param string $searchValue
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
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($searchValue !== '') {
      $queryBuilder->statement->setClauseWhere();
      $dialect = $queryBuilder->dialect;

      $queryBuilder->statement->clauseWhere->addCondition(
        sprintf(
          '(%s OR %s)',
          $dialect->stringLike('login', 'search', true),
          $dialect->stringLike('email', 'search', true)
        )
      );
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

    if ($searchValue !== '') {
      $databaseQuery->bindValue(':search', '%' . $searchValue . '%', \PDO::PARAM_STR);
    }

    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return (int) ($result['count'] ?? 0);
  }
}
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

final class UsersGroups
{
  /**
   * Допустимые правила сортировки
   */
  private const SORT_RULES = [
    'by_createdtimestamp_increase' => ['column' => 'createdUnixTimestamp', 'direction' => 'ASC'],
    'by_createdtimestamp_decrease' => ['column' => 'createdUnixTimestamp', 'direction' => 'DESC'],
    'by_updatedtimestamp_increase' => ['column' => 'updatedUnixTimestamp', 'direction' => 'ASC'],
    'by_updatedtimestamp_decrease' => ['column' => 'updatedUnixTimestamp', 'direction' => 'DESC'],
    'by_alphabet_increase'         => ['column' => 'name',                 'direction' => 'ASC'],
    'by_alphabet_decrease'         => ['column' => 'name',                 'direction' => 'DESC'],
  ];

  public function __construct(
    private CoreInterface $CMSCore
  ) {}

  /**
   * Получить все объекты групп пользователей
   *
   * @param array $params
   *   - limit:  [int $limit, int $offset]
   *   - search: string — подстрока для поиска по name
   *   - sort:   string — одно из SORT_RULES
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
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();

    // Поиск по name
    $searchValue = $paramsArray['search'] ?? '';
    $hasSearch = is_string($searchValue) && $searchValue !== '';

    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql'      => '`name` LIKE :search',
        'postgresql' => '"name" ILIKE :search'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    // Сортировка
    $sortRule = $paramsArray['sort'] ?? 'by_createdtimestamp_decrease';
    $sortConfig = self::SORT_RULES[$sortRule] ?? self::SORT_RULES['by_createdtimestamp_decrease'];

    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn($sortConfig['column']);
    $queryBuilder->statement->clauseOrderBy->setSortType($sortConfig['direction']);

    // Лимит
    if (array_key_exists('limit', $paramsArray) && is_array($paramsArray['limit'])) {
      $limit = is_integer($paramsArray['limit'][0]) ? $paramsArray['limit'][0] : 0;
      $offset = is_integer($paramsArray['limit'][1]) ? $paramsArray['limit'][1] : 0;
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

    $usersGroups = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

    if ($results) {
      foreach ($results as $data) {
        $usersGroups[] = new UserGroup($this->CMSCore, (int) $data['id']);
      }
    }

    return $usersGroups;
  }

  /**
   * Получить общее количество.
   * Если $searchValue передан — считаем только совпадающие по name.
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
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($searchValue !== '') {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql'      => '`name` LIKE :search',
        'postgresql' => '"name" ILIKE :search'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      if ($searchValue !== '') {
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

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return (int) ($result['count'] ?? 0);
  }
}
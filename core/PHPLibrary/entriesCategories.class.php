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

final class EntriesCategories
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

  public const DEFAULT_SORT_RULE = 'by_createdtimestamp_decrease';

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
  ) {}
  
  /**
   * Получить массив объектов всех категорий
   * 
   * @param array   $params
   * @param string  $searchValue — подстрока для поиска по name
   * @param string  $sortRule    — одно из SORT_RULES
   * 
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
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();

    // Поиск по name
    $hasSearch = $searchValue !== '';
    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition(
        $queryBuilder->dialect->stringLike('name', 'search', true)
      );
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

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

    if ($hasSearch) {
      $databaseQuery->bindValue(':search', '%' . $searchValue . '%', \PDO::PARAM_STR);
    }

    $databaseQuery->execute();

    $array = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($array, new EntryCategory($this->CMSCore, $data['id']));
      }
    }

    return $array;
  }
      
  /**
   * Получить общее количество (с учётом поиска)
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
    $queryBuilder->statement->clauseFrom->addTable('entries_categories');
    $queryBuilder->statement->clauseFrom->assembly();

    $hasSearch = $searchValue !== '';
    if ($hasSearch) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition(
        $queryBuilder->dialect->stringLike('name', 'search', true)
      );
      $queryBuilder->statement->clauseWhere->assembly();
    }

    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

    if ($hasSearch) {
      $databaseQuery->bindValue(':search', '%' . $searchValue . '%', \PDO::PARAM_STR);
    }

    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? (int) $result['count'] : 0;
  }
}
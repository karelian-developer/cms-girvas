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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \PDOException as PDOException;

final class Pages
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
    private CoreInterface $CMSCore
  ) {}

  /**
   * Получить все объекты страниц
   *
   * @param   array   $paramsArray
   * @param   bool    $isPublised
   * @param   string  $searchValue — подстрока для поиска по name
   * @param   string  $sortRule    — одно из SORT_RULES
   * @return  array
   */
  public function getAll(
    array $paramsArray = [],
    $isPublised = false,
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
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
    $queryBuilder->statement->clauseFrom->assembly();

    // Условия WHERE собираем в массив — поиск и публикация могут применяться одновременно
    $hasWhere = false;

    if ($isPublised) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition(
        $queryBuilder->dialect->jsonExtractBoolean('metadata', 'isPublished')
      );
      $hasWhere = true;
    }

    // Поиск по name
    $hasSearch = $searchValue !== '';
    if ($hasSearch) {
      if (!$hasWhere) {
        $queryBuilder->statement->setClauseWhere();
        $hasWhere = true;
      }

      $conjunction = $isPublised ? 'AND' : '';

      $queryBuilder->statement->clauseWhere->addCondition(
        $queryBuilder->dialect->stringLike('name', 'search', true),
        $conjunction
      );
    }

    if ($hasWhere) {
      $queryBuilder->statement->clauseWhere->assembly();
    }

    // Сортировка
    $sortConfig = self::SORT_RULES[$sortRule] ?? self::SORT_RULES[self::DEFAULT_SORT_RULE];

    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn($sortConfig['column']);
    $queryBuilder->statement->clauseOrderBy->setSortType($sortConfig['direction']);

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

    $pages = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $data) {
        array_push($pages, new PageStatic($this->CMSCore, $data['id']));
      }
    }

    return $pages;
  }

  /**
   * Получить общее количество (с учётом поиска по name)
   *
   * @param   string $searchValue
   * @return  int
   */
  public function getCountTotal(string $searchValue = '') : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('pages_static');
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
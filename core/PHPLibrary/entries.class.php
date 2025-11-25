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

final class Entries
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
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => '(metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
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
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`categoryID` = :categoryID',
      'postgresql' => '"categoryID" = :categoryID'
    ]);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
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
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`categoryID` = :categoryID',
      'postgresql' => '"categoryID" = :categoryID'
    ]);

    if ($isPublished) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'AND JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => 'AND (metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
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
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries');
    $queryBuilder->statement->clauseFrom->assembly();

    if ($isPublished) {
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'JSON_EXTRACT(`metadata`, \'$.isPublished\') = 1',
        'postgresql' => '(metadata::jsonb->>\'isPublished\')::boolean = true'
      ]);
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
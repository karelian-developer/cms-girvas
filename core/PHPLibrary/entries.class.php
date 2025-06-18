<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;

  final class Entries {
    private SystemCore $CMSCore;
    
    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
    }
        
    /**
     * Получить все объекты записей
     *
     * @param   array $params
     * @param   bool 
     * @return  array
     */
    public function get_all(array $params = [], $isPublished = false) : array {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();

      if ($isPublished) {
        $queryBuilder->statement->set_clause_where();
        $queryBuilder->statement->clauseWhere->add_condition('(metadata::jsonb->>\'isPublished\')::boolean = true');
        $queryBuilder->statement->clauseWhere->assembly();
      }

      $queryBuilder->statement->set_clause_order_by();
      $queryBuilder->statement->clauseOrderBy->set_column('createdUnixTimestamp');
      $queryBuilder->statement->clauseOrderBy->set_sort_type('DESC');
      if (array_key_exists('limit', $params)) {
        if (is_array($params['limit'])) {
          $limit = (is_integer($params['limit'][0])) ? $params['limit'][0] : 0;
          $offset = (is_integer($params['limit'][1])) ? $params['limit'][1] : 0;
          $queryBuilder->statement->set_clause_limit($limit, $offset);
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
    public function get_by_category_id(int $id, array $params = [], $isPublished = false) : array {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"categoryID" = :categoryID');

      if ($isPublished) {
        $queryBuilder->statement->clauseWhere->add_condition('AND (metadata::jsonb->>\'isPublished\')::boolean = true');
      }

      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_order_by();
      $queryBuilder->statement->clauseOrderBy->set_column('createdUnixTimestamp');
      $queryBuilder->statement->clauseOrderBy->set_sort_type('DESC');
      if (array_key_exists('limit', $params)) {
        if (is_array($params['limit'])) {
          $limit = (is_integer($params['limit'][0])) ? $params['limit'][0] : 0;
          $offset = (is_integer($params['limit'][1])) ? $params['limit'][1] : 0;
          $queryBuilder->statement->set_clause_limit($limit, $offset);
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
    public function get_count_by_category_id(int $id, $isPublished = false) : int {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['count(*)']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"categoryID" = :categoryID');

      if ($isPublished) {
        $queryBuilder->statement->clauseWhere->add_condition('AND (metadata::jsonb->>\'isPublished\')::boolean = true');
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
    public function get_count_total($isPublished = false) : int {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['count(*)']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries');
      $queryBuilder->statement->clauseFrom->assembly();

      if ($isPublished) {
        $queryBuilder->statement->set_clause_where();
        $queryBuilder->statement->clauseWhere->add_condition('(metadata::jsonb->>\'isPublished\')::boolean = true');
        $queryBuilder->statement->clauseWhere->assembly();
      }

      $queryBuilder->statement->assembly();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
			$databaseQuery->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result['count'] : 0;
    }

  }

}

?>
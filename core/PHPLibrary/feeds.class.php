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
  use \PDOException as PDOException;

  final class Feeds {
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
    
    public function get_all(array $params = []) : array {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('web_channels');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_order_by();
      $queryBuilder->statement->clauseOrderBy->set_column('id');
      $queryBuilder->statement->clauseOrderBy->set_sort_type('DESC');
      if (array_key_exists('limit', $params)) {
        if (is_array($params['limit'])) {
          $limit = (is_integer($params['limit'][0])) ? $params['limit'][0] : 0;
          $offset = (is_integer($params['limit'][1])) ? $params['limit'][1] : 0;
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

      $feeds = [];
      $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
      if ($results) {
        foreach ($results as $data) {
          array_push($feeds, new Feed($this->CMSCore, $data['id']));
        }
      }

      return $feeds;
    }
        
    /**
     * Получить общее количество
     *
     * @return int
     */
    public function get_count_total() : int {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['count(*)']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('web_channels');
      $queryBuilder->statement->clauseFrom->assembly();
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

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result['count'] : 0;
    }

  }

}

?>
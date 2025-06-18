<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Pages as Pages;
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Metrics\Session as MetricsSession;
  use \PDOException as PDOException;

  /**
   * Метрики CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  #[\AllowDynamicProperties]
  final class Metrics {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $CMSCore = null;
    /** @var string Временная отметка */
    public int $timestamp = 0;

    /**
     * __construct
     * 
     * @param SystemCore $CMSCore
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
    }

    /**
     * Установить временную отметку
     * 
     * @param int $value
     * 
     * @return void
     */
    public function set_timestamp(int $value) : void {
      $this->timestamp = $value;
    }

    /**
     * Получить сессию метрики
     * 
     * @param int $timestamp
     * 
     * @return Session
     */
    public function get_session_by_timestamp(int $timestamp) : MetricsSession|null {
      $timestamp = strtotime(date('Y/m/d', $timestamp));

      if (MetricsSession::exists_by_timestamp($this->CMSCore, $this, $timestamp)) {
        return MetricsSession::get_by_timestamp($this->CMSCore, $this, $timestamp);
      }

      return null;
    }

    /**
     * Получить массив объектов сессий метрики во временных рамках
     * 
     * @param int $timestampStart
     * @param int $timestampEnd
     * 
     * @return array
     */
    public function get_sessions_by_timestamp_range(int $timestampStart, int $timestampEnd) : array {
      $timestampStart = strtotime(date('Y/m/d', $timestampStart));
      $timestampEnd = strtotime(date('Y/m/d', $timestampEnd));

      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('metrics');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"date" >= :dateStart AND "date" <= :dateEnd');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':dateStart', $timestampStart, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':dateEnd', $timestampEnd, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $sessions = [];
      $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

      if ($results) {
        foreach ($results as $data) {
          array_push($sessions, new MetricsSession($this->CMSCore, $this, $data['id']));
        }
      }

      return $sessions;
    }

    /**
     * Получить просмотры по записям
     * 
     * @param int $timestamp
     * 
     * @return array
     */
    public function get_entries_views_by_timestamp(int $timestamp) : array {
      $entries = (new Entries($this->CMSCore, true))->get_all([], true);
      $entriesResult = [];

      foreach ($entries as $index => $object) {
        $object->init_data(['name']);
      }

      if (!empty($entries)) {
        $metricsSession = $this->get_session_by_timestamp($timestamp);
        if (!is_null($metricsSession)) {
          $metricsSession->init_data(['data']);

          $metricsViews = $metricsSession->get_data_metrics_views();

          if (!is_null($metricsViews)) {
            foreach ($metricsViews as $views_token => $views_data) {
              $viewsURLs = $views_data['urls'];

              if (!empty($viewsURLs)) {
                foreach ($viewsURLs as $url => $count) {
                  $URLParsed = parse_url($url);
                  $pathParts = explode('/', $URLParsed['path']);

                  if ($pathParts[1] == 'entry') {
                    foreach ($entries as $index => $object) {
                      if ($object->get_name() == $pathParts[2]) {
                        if (in_array($object, $entriesResult)) {
                          $currentViews = $object->get_views_count();
                          $object->set_views_count($currentViews + $count);
                        } else {
                          $object->set_views_count($count);
                          array_push($entriesResult, $object);
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      return $entriesResult;
    }

    /**
     * Получить просмотры по страницам
     * 
     * @param int $timestamp
     * 
     * @return array
     */
    public function get_pages_views_by_timestamp(int $timestamp) : array {
      $pages = (new Pages($this->CMSCore, true))->get_all([], true);
      $pagesResult = [];

      foreach ($pages as $index => $object) {
        $object->init_data(['name']);
      }

      if (!empty($pages)) {
        $metricsSession = $this->get_session_by_timestamp($timestamp);
        if (!is_null($metricsSession)) {
          $metricsSession->init_data(['data']);

          $metricsViews = $metricsSession->get_data_metrics_views();
          if (!is_null($metricsViews)) {
            foreach ($metricsViews as $views_token => $views_data) {
              $viewsURLs = $views_data['urls'];
              if (!empty($viewsURLs)) {
                foreach ($viewsURLs as $url => $count) {
                  $URLParsed = parse_url($url);
                  $pathParts = explode('/', $URLParsed['path']);

                  if ($pathParts[1] === 'page') {
                    foreach ($pages as $index => $object) {
                      if ($object->get_name() == $pathParts[2]) {
                        if (in_array($object, $pagesResult)) {
                          $currentViews = $object->get_views_count();
                          $object->set_views_count($currentViews + $count);
                        } else {
                          $object->set_views_count($count);
                          array_push($pagesResult, $object);
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      return $pagesResult;
    }
  }
}

?>
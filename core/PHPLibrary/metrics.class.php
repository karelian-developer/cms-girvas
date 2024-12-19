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

  /**
   * Метрики CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  #[\AllowDynamicProperties]
  final class Metrics {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $system_core = null;
    /** @var string Временная отметка */
    public int $timestamp = 0;

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;
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

      if (MetricsSession::exists_by_timestamp($this->system_core, $this, $timestamp)) {
        return MetricsSession::get_by_timestamp($this->system_core, $this, $timestamp);
      }

      return null;
    }

    /**
     * Получить массив объектов сессий метрики во временных рамках
     * 
     * @param int $timestamp_start
     * @param int $timestamp_end
     * 
     * @return array
     */
    public function get_sessions_by_timestamp_range(int $timestamp_start, int $timestamp_end) : array {
      $timestamp_start = strtotime(date('Y/m/d', $timestamp_start));
      $timestamp_end = strtotime(date('Y/m/d', $timestamp_end));

      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('metrics');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('date >= :date_start AND date <= :date_end');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':date_start', $timestamp_start, \PDO::PARAM_INT);
      $database_query->bindParam(':date_end', $timestamp_end, \PDO::PARAM_INT);
			$database_query->execute();

      $sessions = [];
      $results = $database_query->fetchAll(\PDO::FETCH_ASSOC);

      if ($results) {
        foreach ($results as $data) {
          array_push($sessions, new MetricsSession($this->system_core, $this, $data['id']));
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
      $entries = (new Entries($this->system_core, true))->get_all([], true);
      $entries_result = [];

      foreach ($entries as $entry_index => $entry_object) {
        $entry_object->init_data(['name']);
      }

      if (!empty($entries)) {
        $metrics_session = $this->get_session_by_timestamp($timestamp);
        if (!is_null($metrics_session)) {
          $metrics_session->init_data(['data']);

          $metrics_views = $metrics_session->get_data_metrics_views();
          if (!is_null($metrics_views)) {
            foreach ($metrics_views as $views_token => $views_data) {
              $views_urls = $views_data['urls'];
              if (!empty($views_urls)) {
                foreach ($views_urls as $url => $views_count) {
                  $url_parsed = parse_url($url);
                  $path_parts = explode('/', $url_parsed['path']);

                  if ($path_parts[1] == 'entry') {
                    foreach ($entries as $entry_index => $entry_object) {
                      if ($entry_object->get_name() == $path_parts[2]) {
                        if (in_array($entry_object, $entries_result)) {
                          $current_views = $entry_object->get_views_count();
                          $entry_object->set_views_count($current_views + $views_count);
                        } else {
                          $entry_object->set_views_count($views_count);
                          array_push($entries_result, $entry_object);
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

      return $entries_result;
    }

    /**
     * Получить просмотры по страницам
     * 
     * @param int $timestamp
     * 
     * @return array
     */
    public function get_pages_views_by_timestamp(int $timestamp) : array {
      $pages = (new Pages($this->system_core, true))->get_all([], true);
      $pages_result = [];

      foreach ($pages as $page_index => $page_object) {
        $page_object->init_data(['name']);
      }

      if (!empty($pages)) {
        $metrics_session = $this->get_session_by_timestamp($timestamp);
        if (!is_null($metrics_session)) {
          $metrics_session->init_data(['data']);

          $metrics_views = $metrics_session->get_data_metrics_views();
          if (!is_null($metrics_views)) {
            foreach ($metrics_views as $views_token => $views_data) {
              $views_urls = $views_data['urls'];
              if (!empty($views_urls)) {
                foreach ($views_urls as $url => $views_count) {
                  $url_parsed = parse_url($url);
                  $path_parts = explode('/', $url_parsed['path']);

                  if ($path_parts[1] == 'page') {
                    foreach ($pages as $page_index => $page_object) {
                      if ($page_object->get_name() == $path_parts[2]) {
                        if (in_array($page_object, $pages_result)) {
                          $current_views = $page_object->get_views_count();
                          $page_object->set_views_count($current_views + $views_count);
                        } else {
                          $page_object->set_views_count($views_count);
                          array_push($pages_result, $page_object);
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

      return $pages_result;
    }
  }
}

?>
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
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик»
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Pages as Pages;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Metrics\Session as MetricsSession;
use \PDOException as PDOException;

/**
 * Метрики CMS
 * 
 * @author Andrey Shestakov
 * @version 0.0.1
 */
#[\AllowDynamicProperties]
final class Metrics
{
  /** @var int Временная отметка */
  public int $timestamp = 0;

  /**
   * Конструктор
   *
   * @param CoreInterface $CMSCore
   */
  public function __construct(
    public CoreInterface $CMSCore
  ) {}

  /**
   * Установить временную отметку
   *
   * @param int $value
   * @return void
   */
  public function setTimestamp(int $value): void
  {
    $this->timestamp = $value;
  }

  /**
   * Получить временную отметку
   *
   * @return int
   */
  public function getTimestamp(): int
  {
    return $this->timestamp;
  }

  /**
   * Получить сессию метрики по дате
   *
   * @param int $timestamp
   * @return MetricsSession|null
   */
  public function getSessionByTimestamp(int $timestamp): ?MetricsSession
  {
    $timestamp = strtotime(date('Y/m/d', $timestamp));

    if (MetricsSession::existsByTimestamp($this->CMSCore, $this, $timestamp)) {
      return MetricsSession::getByTimestamp($this->CMSCore, $this, $timestamp);
    }

    return null;
  }

  /**
   * Получить массив сессий метрики за период
   *
   * @param int $timestampStart
   * @param int $timestampEnd
   * @return array
   */
  public function getSessionsByTimestampRange(int $timestampStart, int $timestampEnd): array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $timestampStart = strtotime(date('Y/m/d', $timestampStart));
    $timestampEnd = strtotime(date('Y/m/d', $timestampEnd));

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);

    // SELECT
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);

    // FROM
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('metrics');
    $queryBuilder->statement->clauseFrom->assembly();

    // WHERE
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`date` >= :dateStart AND `date` <= :dateEnd',
      'postgresql' => '"date" >= :dateStart AND "date" <= :dateEnd'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();

    // ORDER BY
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('date');
    $queryBuilder->statement->clauseOrderBy->setSortType('ASC');
    $queryBuilder->statement->clauseOrderBy->assembly();

    // Финальная сборка
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
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $sessions = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

    if ($results) {
      foreach ($results as $data) {
        $sessions[] = new MetricsSession($this->CMSCore, $this, $data['id']);
      }
    }

    return $sessions;
  }

  /**
   * Получить просмотры по записям за день
   *
   * @param int $timestamp
   * @return array
   */
  public function getEntriesViewsByTimestamp(int $timestamp): array
  {
    $entries = (new Entries($this->CMSCore, true))->getAll([], true);

    if (empty($entries)) {
      return [];
    }

    // Инициализируем данные записей
    foreach ($entries as $object) {
      $object->initData(['name', 'id']);
    }

    // Индексируем записи по name для быстрого поиска
    $entriesByName = [];
    foreach ($entries as $object) {
      $entriesByName[$object->getName()] = $object;
    }

    $metricsSession = $this->getSessionByTimestamp($timestamp);
    if ($metricsSession === null) {
      return [];
    }

    $metricsSession->initData(['data']);
    $metricsViews = $metricsSession->getDataMetricsViews();

    if ($metricsViews === null) {
      return [];
    }

    $entriesResult = [];

    foreach ($metricsViews as $viewsToken => $viewsData) {
      $viewsURLs = $viewsData['urls'] ?? [];

      foreach ($viewsURLs as $url => $count) {
        $URLParsed = parse_url($url);

        if (!isset($URLParsed['path'])) {
          continue;
        }

        $pathParts = explode('/', trim($URLParsed['path'], '/'));

        if (isset($pathParts[0]) && $pathParts[0] === 'entry' && isset($pathParts[1])) {
          $entryName = $pathParts[1];

          if (isset($entriesByName[$entryName])) {
            $entry = $entriesByName[$entryName];
            $entryId = $entry->getId();

            if (isset($entriesResult[$entryId])) {
              $entriesResult[$entryId]->setViewsCount(
                $entriesResult[$entryId]->getViewsCount() + $count
              );
            } else {
              $entry->setViewsCount($count);
              $entriesResult[$entryId] = $entry;
            }
          }
        }
      }
    }

    // Сортируем по убыванию просмотров
    usort($entriesResult, function($a, $b) {
      return $b->getViewsCount() - $a->getViewsCount();
    });

    return array_values($entriesResult);
  }

  /**
   * Получить просмотры по страницам за день
   *
   * @param int $timestamp
   * @return array
   */
  public function getPagesViewsByTimestamp(int $timestamp): array
  {
    $pages = (new Pages($this->CMSCore, true))->getAll([], true);

    if (empty($pages)) {
      return [];
    }

    // Инициализируем данные страниц
    foreach ($pages as $object) {
      $object->initData(['name', 'id']);
    }

    // Индексируем страницы по name для быстрого поиска
    $pagesByName = [];
    foreach ($pages as $object) {
      $pagesByName[$object->getName()] = $object;
    }

    $metricsSession = $this->getSessionByTimestamp($timestamp);
    if ($metricsSession === null) {
      return [];
    }

    $metricsSession->initData(['data']);
    $metricsViews = $metricsSession->getDataMetricsViews();

    if ($metricsViews === null) {
      return [];
    }

    $pagesResult = [];

    foreach ($metricsViews as $viewsToken => $viewsData) {
      $viewsURLs = $viewsData['urls'] ?? [];

      foreach ($viewsURLs as $url => $count) {
        $URLParsed = parse_url($url);

        if (!isset($URLParsed['path'])) {
          continue;
        }

        $pathParts = explode('/', trim($URLParsed['path'], '/'));

        if (isset($pathParts[0]) && $pathParts[0] === 'page' && isset($pathParts[1])) {
          $pageName = $pathParts[1];

          if (isset($pagesByName[$pageName])) {
            $page = $pagesByName[$pageName];
            $pageId = $page->getId();

            if (isset($pagesResult[$pageId])) {
              $pagesResult[$pageId]->setViewsCount(
                $pagesResult[$pageId]->getViewsCount() + $count
              );
            } else {
              $page->setViewsCount($count);
              $pagesResult[$pageId] = $page;
            }
          }
        }
      }
    }

    // Сортируем по убыванию просмотров
    usort($pagesResult, function($a, $b) {
      return $b->getViewsCount() - $a->getViewsCount();
    });

    return array_values($pagesResult);
  }

  /**
   * Получить общую статистику за период
   *
   * @param int $timestampStart
   * @param int $timestampEnd
   * @return array
   */
  public function getStatsByTimestampRange(int $timestampStart, int $timestampEnd): array
  {
    $sessions = $this->getSessionsByTimestampRange($timestampStart, $timestampEnd);

    $stats = [
      'totalViews' => 0,
      'uniqueVisitors' => [],
      'newVisitors' => [],
      'days' => count($sessions),
      'tokens' => []
    ];

    foreach ($sessions as $session) {
      $session->initData(['data']);
      $data = $session->getData();

      if (!isset($data['metrics']['views'])) {
        continue;
      }

      foreach ($data['metrics']['views'] as $token => $viewData) {
        // Считаем просмотры
        foreach ($viewData['urls'] as $url => $count) {
          $stats['totalViews'] += $count;
        }

        // Уникальные посетители
        if (!in_array($token, $stats['uniqueVisitors'])) {
          $stats['uniqueVisitors'][] = $token;
        }

        // Новые посетители
        if (isset($viewData['URLTransfers'])) {
          foreach ($viewData['URLTransfers'] as $transfer) {
            foreach ($transfer as $url => $data) {
              if (isset($data['isVisitedNew']) && $data['isVisitedNew']) {
                if (!in_array($token, $stats['newVisitors'])) {
                  $stats['newVisitors'][] = $token;
                }
              }
            }
          }
        }

        // Собираем информацию по токенам
        if (!isset($stats['tokens'][$token])) {
          $stats['tokens'][$token] = [
            'ip' => $viewData['ip'] ?? '0.0.0.0',
            'urls' => [],
            'transfers' => 0
          ];
        }

        foreach ($viewData['urls'] as $url => $count) {
          if (!isset($stats['tokens'][$token]['urls'][$url])) {
            $stats['tokens'][$token]['urls'][$url] = 0;
          }
          $stats['tokens'][$token]['urls'][$url] += $count;
        }

        $stats['tokens'][$token]['transfers'] += count($viewData['URLTransfers'] ?? []);
      }
    }

    $stats['uniqueVisitorsCount'] = count($stats['uniqueVisitors']);
    $stats['newVisitorsCount'] = count($stats['newVisitors']);

    return $stats;
  }

  /**
   * Получить детальную статистику по дню
   *
   * @param int $timestamp
   * @return array
   */
  public function getDetailedStatsByDay(int $timestamp): array
  {
    $session = $this->getSessionByTimestamp($timestamp);

    if ($session === null) {
      return [
        'totalViews' => 0,
        'uniqueVisitors' => 0,
        'newVisitors' => 0,
        'views' => [],
        'tokens' => []
      ];
    }

    $session->initData(['data']);
    $data = $session->getData();

    $stats = [
      'totalViews' => 0,
      'uniqueVisitors' => 0,
      'newVisitors' => 0,
      'views' => [],
      'tokens' => []
    ];

    if (!isset($data['metrics']['views'])) {
      return $stats;
    }

    foreach ($data['metrics']['views'] as $token => $viewData) {
      $stats['tokens'][$token] = [
        'ip' => $viewData['ip'] ?? '0.0.0.0',
        'urls' => [],
        'transfers' => count($viewData['URLTransfers'] ?? []),
        'isNew' => false
      ];

      foreach ($viewData['urls'] as $url => $count) {
        $stats['totalViews'] += $count;
        $stats['tokens'][$token]['urls'][$url] = $count;

        if (!isset($stats['views'][$url])) {
          $stats['views'][$url] = 0;
        }
        $stats['views'][$url] += $count;
      }

      // Проверяем, был ли новый визит
      if (isset($viewData['URLTransfers'])) {
        foreach ($viewData['URLTransfers'] as $transfer) {
          foreach ($transfer as $url => $data) {
            if (isset($data['isVisitedNew']) && $data['isVisitedNew']) {
              $stats['tokens'][$token]['isNew'] = true;
              $stats['newVisitors']++;
              break 2;
            }
          }
        }
      }
    }

    $stats['uniqueVisitors'] = count($stats['tokens']);

    // Сортируем просмотры по убыванию
    arsort($stats['views']);

    return $stats;
  }

  /**
   * Очистить данные за указанный период
   *
   * @param int $timestampStart
   * @param int $timestampEnd
   * @return bool
   */
  public function cleanDataByTimestampRange(int $timestampStart, int $timestampEnd): bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $timestampStart = strtotime(date('Y/m/d', $timestampStart));
    $timestampEnd = strtotime(date('Y/m/d', $timestampEnd));

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);

    // DELETE
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('metrics');

    // WHERE
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`date` >= :dateStart AND `date` <= :dateEnd',
      'postgresql' => '"date" >= :dateStart AND "date" <= :dateEnd'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();

    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':dateStart', $timestampStart, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':dateEnd', $timestampEnd, \PDO::PARAM_INT);
      return $databaseQuery->execute();
    } catch (PDOException $exception) {
      error_log('[Metrics] Clean error: ' . $exception->getMessage());
      return false;
    }
  }
}
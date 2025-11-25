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

use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Pages as Pages;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\Metrics\Session as MetricsSession;
use \PDOException as PDOException;

/**
 * Метрики CMS
 * 
 * @author Andrey Shestakov <drelagas.new@gmail.com>
 * @version 0.0.1
 */
#[\AllowDynamicProperties]
final class Metrics
{
  /** @var string Временная отметка */
  public int $timestamp = 0;

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore
  ) {}

  /**
   * Установить временную отметку
   * 
   * @param int $value
   * 
   * @return void
   */
  public function setTimestamp(int $value) : void
  {
    $this->timestamp = $value;
  }

  /**
   * Получить сессию метрики
   * 
   * @param int $timestamp
   * 
   * @return Session|null
   */
  public function getSessionByTimestamp(int $timestamp) : MetricsSession|null
  {
    $timestamp = strtotime(date('Y/m/d', $timestamp));

    if (MetricsSession::existsByTimestamp($this->CMSCore, $this, $timestamp)) {
      return MetricsSession::getByTimestamp($this->CMSCore, $this, $timestamp);
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
  public function getSessionsByTimestampRange(int $timestampStart, int $timestampEnd) : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $timestampStart = strtotime(date('Y/m/d', $timestampStart));
    $timestampEnd = strtotime(date('Y/m/d', $timestampEnd));

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('metrics');
    $queryBuilder->statement->clauseFrom->assembly();
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
  public function getEntriesViewsByTimestamp(int $timestamp) : array
  {
    $entries = (new Entries($this->CMSCore, true))->getAll([], true);
    $entriesResult = [];

    foreach ($entries as $index => $object) {
      $object->initData(['name']);
    }

    if (!empty($entries)) {
      $metricsSession = $this->getSessionByTimestamp($timestamp);

      if ($metricsSession !== null) {
        $metricsSession->initData(['data']);

        $metricsViews = $metricsSession->getDataMetricsViews();

        if ($metricsViews !== null) {
          foreach ($metricsViews as $viewsToken => $viewsData) {
            $viewsURLs = $viewsData['urls'] ?? [];

            foreach ($viewsURLs as $url => $count) {
              $URLParsed = parse_url($url);
              $pathParts = explode('/', $URLParsed['path']);

              if ($pathParts[1] === 'entry') {
                foreach ($entries as $index => $object) {
                  if ($object->getName() === $pathParts[2]) {
                    if (in_array($object, $entriesResult)) {
                      $currentViews = $object->getViewsCount();
                      $object->setViewsCount($currentViews + $count);
                    } else {
                      $object->setViewsCount($count);
                      $entriesResult[] = $object;
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
  public function getPagesViewsByTimestamp(int $timestamp) : array
  {
    $pages = (new Pages($this->CMSCore, true))->getAll([], true);
    $pagesResult = [];

    foreach ($pages as $index => $object) {
      $object->initData(['name']);
    }

    if (!empty($pages)) {
      $metricsSession = $this->getSessionByTimestamp($timestamp);

      if ($metricsSession !== null) {
        $metricsSession->initData(['data']);

        $metricsViews = $metricsSession->getDataMetricsViews();

        if ($metricsViews !== null) {
          foreach ($metricsViews as $viewsToken => $viewsData) {
            $viewsURLs = $viewsData['urls'] ?? [];

            foreach ($viewsURLs as $url => $count) {
              $URLParsed = parse_url($url);
              $pathParts = explode('/', $URLParsed['path']);

              if ($pathParts[1] === 'page') {
                foreach ($pages as $index => $object) {
                  if ($object->getName() === $pathParts[2]) {
                    if (in_array($object, $pagesResult)) {
                      $currentViews = $object->getViewsCount();
                      $object->setViewsCount($currentViews + $count);
                    } else {
                      $object->setViewsCount($count);
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

    return $pagesResult;
  }
}
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

namespace core\PHPLibrary\SystemCore\Reports;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \PDOException as PDOException;

class Rotator
{
  /**
   * Выполнить ротацию отчётов
   *
   * Перемещает отчёты старше N дней в таблицу reports_archive.
   *
   * @param CoreInterface $CMSCore
   * @param int $days Срок хранения в днях
   * @param int $batchSize Размер батча (по умолчанию 1000)
   * @return array ['rotated' => int, 'threshold' => int, 'days' => int]
   */
  public static function rotate(
    CoreInterface $CMSCore,
    int $days = 365,
    int $batchSize = 1000
  ) : array {
    if ($days <= 0) {
      return ['rotated' => 0, 'threshold' => 0, 'days' => $days];
    }

    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    $databaseConnection = $CMSCore->databaseConnector->database->connection;

    $threshold = time() - ($days * 86400);
    $totalRotated = 0;

    // Защита от бесконечного цикла
    $maxIterations = 1000;
    $iteration = 0;

    while ($iteration < $maxIterations) {
      $iteration++;

      // ============================================================
      // 1. Получить батч ID для архивации
      // ============================================================
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('reports');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => '`createdUnixTimestamp` < :threshold',
        'postgresql' => '"createdUnixTimestamp" < :threshold'
      ]);
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->setClauseOrderBy();
      $queryBuilder->statement->clauseOrderBy->setColumn('id');
      $queryBuilder->statement->clauseOrderBy->setSortType('ASC');
      $queryBuilder->statement->setClauseLimit($batchSize);
      $queryBuilder->statement->assembly();

      try {
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':threshold', $threshold, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        error_log('Rotator: select failed: ' . $exception->getMessage());
        break;
      }

      $rows = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

      if (empty($rows)) {
        break; // больше нечего архивировать
      }

      $ids = array_map(fn($row) => (int)$row['id'], $rows);
      $idsPlaceholders = [];
      $idsBindings = [];

      foreach ($ids as $index => $id) {
        $placeholder = ':id_' . $index;
        $idsPlaceholders[] = $placeholder;
        $idsBindings[$placeholder] = $id;
      }

      $idsImploded = implode(', ', $idsPlaceholders);
      $currentTimestamp = time();

      // ============================================================
      // 2. Транзакция: INSERT в архив + DELETE из reports
      // ============================================================
      $ownTransaction = !$databaseConnection->inTransaction();
      if ($ownTransaction) {
        $databaseConnection->beginTransaction();
      }

      try {
        // INSERT INTO reports_archive
        $insertSql = match ($CMSConfigDatabase['dms']) {
          CMSDMS::MySQL => sprintf(
            'INSERT INTO `reports_archive` (`id`, `variables`, `metadata`, `createdUnixTimestamp`, `archivedUnixTimestamp`)
             SELECT `id`, `variables`, `metadata`, `createdUnixTimestamp`, :archivedUnixTimestamp
             FROM `reports` WHERE `id` IN (%s)',
            $idsImploded
          ),
          CMSDMS::PostgreSQL => sprintf(
            'INSERT INTO "reports_archive" ("id", "variables", "metadata", "createdUnixTimestamp", "archivedUnixTimestamp")
             SELECT "id", "variables", "metadata", "createdUnixTimestamp", :archivedUnixTimestamp
             FROM "reports" WHERE "id" IN (%s)',
            $idsImploded
          )
        };

        $insertQuery = $databaseConnection->prepare($insertSql);
        $insertQuery->bindValue(':archivedUnixTimestamp', $currentTimestamp, \PDO::PARAM_INT);
        foreach ($idsBindings as $placeholder => $id) {
          $insertQuery->bindValue($placeholder, $id, \PDO::PARAM_INT);
        }
        $insertQuery->execute();

        // DELETE FROM reports
        $deleteSql = match ($CMSConfigDatabase['dms']) {
          CMSDMS::MySQL => sprintf('DELETE FROM `reports` WHERE `id` IN (%s)', $idsImploded),
          CMSDMS::PostgreSQL => sprintf('DELETE FROM "reports" WHERE "id" IN (%s)', $idsImploded)
        };

        $deleteQuery = $databaseConnection->prepare($deleteSql);
        foreach ($idsBindings as $placeholder => $id) {
          $deleteQuery->bindValue($placeholder, $id, \PDO::PARAM_INT);
        }
        $deleteQuery->execute();

        if ($ownTransaction) {
          $databaseConnection->commit();
        }

        $totalRotated += count($ids);

        // Если батч меньше batchSize — данных больше нет
        if (count($ids) < $batchSize) {
          break;
        }
      } catch (PDOException $exception) {
        if ($ownTransaction) {
          $databaseConnection->rollBack();
        }

        error_log('Rotator: transaction failed: ' . $exception->getMessage());
        break;
      }
    }

    return [
      'rotated' => $totalRotated,
      'threshold' => $threshold,
      'days' => $days,
    ];
  }
}
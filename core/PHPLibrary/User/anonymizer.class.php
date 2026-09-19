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

namespace core\PHPLibrary\User;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \PDOException as PDOException;

class Anonymizer
{
  /**
   * Обезличить пользователя
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @param string $reason
   * @param int $anonymizedByID
   * @return bool
   */
  public static function anonymize(
    CMSCore $CMSCore,
    int $userID,
    string $reason = 'retention_expired',
    int $anonymizedByID = 0
  ) : bool {
    if ($userID <= 0 || !User::existsByID($CMSCore, $userID)) {
      return false;
    }

    $user = new User($CMSCore, $userID);
    $user->initData(['login', 'email', 'metadata']);

    if ($user->isAnonymized()) {
      return false;
    }

    // ============================================================
    // 1. Обезличиваем users
    // ============================================================
    $anonymizedLogin = 'anon_' . $userID;
    $anonymizedEmail = '';

    $anonymizedMetadata = [
      'name' => '',
      'surname' => '',
      'patronymic' => '',
      'groupID' => $user->getGroupID(),  // сохраняем группу для аудита
      'registrationIP' => '0.0.0.0',
      'birthdateUnixTimestamp' => 0,
      'additionalFields' => [],
      'isBlocked' => $user->isBlocked(),
      'passwordResetToken' => '',
      'passwordResetTokenCreatedUnixTimestamp' => 0,
      'anonymizedAt' => time(),
      'anonymizedByID' => $anonymizedByID,
      'anonymizedReason' => $reason,
    ];

    // Случайный хеш — чтобы нельзя было восстановить пароль
    $randomHash = bin2hex(random_bytes(32));

    $updated = $user->update([
      'login' => $anonymizedLogin,
      'email' => $anonymizedEmail,
      'passwordHash' => password_hash($randomHash, PASSWORD_DEFAULT),
      'securityHash' => md5($randomHash),
      'metadata' => $anonymizedMetadata,
    ]);

    if (!$updated) {
      return false;
    }

    // ============================================================
    // 2. Обезличиваем users_consents
    // ============================================================
    self::anonymizeConsents($CMSCore, $userID);

    // ============================================================
    // 3. Обезличиваем reports
    // ============================================================
    self::anonymizeReports($CMSCore, $userID);

    return true;
  }

  /**
   * Обезличить согласия пользователя
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @return void
   */
  private static function anonymizeConsents(CMSCore $CMSCore, int $userID) : void
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('users_consents');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('ip');
    $queryBuilder->statement->clauseSet->addColumn('userAgent');
    $queryBuilder->statement->clauseSet->addColumn('revokeReason');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userID` = :userID',
      'postgresql' => '"userID" = :userID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindValue(':ip', '0.0.0.0', \PDO::PARAM_STR);
      $databaseQuery->bindValue(':userAgent', '', \PDO::PARAM_STR);
      $databaseQuery->bindValue(':revokeReason', '', \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      error_log('Anonymizer: consents anonymization failed for user ' . $userID . ': ' . $exception->getMessage());
    }
  }

  /**
   * Обезличить отчёты, связанные с пользователем
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @return void
   */
  private static function anonymizeReports(CMSCore $CMSCore, int $userID) : void
  {
    $reports = \core\PHPLibrary\SystemCore\Reports::getAllByUser($CMSCore, $userID, 100000, 0);

    foreach ($reports as $report) {
      $report->initData(['variables', 'metadata']);
      $variables = $report->getVariables();

      $forbiddenKeys = [
        'email', 'password', 'passwordHash', 'securityHash',
        'phone', 'phoneNumber', 'name', 'surname', 'patronymic',
        'birthdate', 'birthdateUnixTimestamp', 'passport',
        'snils', 'inn', 'address', 'registrationIP', 'token',
        'user_login', 'user_email', 'user_password', 'user_name',
        'user_surname', 'user_patronymic', 'user_birthdate',
        'userPassword', 'userPasswordRepeat', 'userPasswordOld',
        'userGroupID', 'userID', 'userIsBlock',
        'userLogin',
        'targetUserLogin',
        'viewedByLogin',
        'createdByLogin',
        'updatedByLogin',
        'deletedByLogin',
        'revokedByLogin',
        'exportedByLogin',
        'subjectUserLogin',
        'documentTitles',
      ];

      $sanitized = self::sanitizeReportVariables($variables, $forbiddenKeys);

      self::updateReportVariables($CMSCore, $report->getID(), $sanitized);
    }
  }

  /**
   * Рекурсивно затирает ПДн-ключи в variables
   *
   * @param array $variables
   * @param array $forbiddenKeys
   * @return array
   */
  private static function sanitizeReportVariables(array $variables, array $forbiddenKeys) : array
  {
    foreach ($variables as $key => $value) {
      $keyLower = strtolower($key);

      if (in_array($keyLower, $forbiddenKeys, true)) {
        $variables[$key] = '';
        continue;
      }

      if (is_array($value)) {
        $variables[$key] = self::sanitizeReportVariables($value, $forbiddenKeys);
      }
    }

    return $variables;
  }

  /**
   * Обновить variables в отчёте
   *
   * @param CMSCore $CMSCore
   * @param int $reportID
   * @param array $variables
   * @return void
   */
  private static function updateReportVariables(CMSCore $CMSCore, int $reportID, array $variables) : void
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('reports');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('variables');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindValue(':variables', json_encode($variables, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), \PDO::PARAM_STR);
      $databaseQuery->bindParam(':id', $reportID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      error_log('Anonymizer: report anonymization failed for report ' . $reportID . ': ' . $exception->getMessage());
    }
  }
}
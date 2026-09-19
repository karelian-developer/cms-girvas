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

use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Consent
{
  private bool $isDataFullyInitialized = false;
  private array $initializedColumns = [];

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $id
  ) {}

  /**
   * Инициализация данных из БД
   *
   * @param array $columns
   * @return void
   */
  public function initData(array $columns = ['*']) : void
  {
    if ($this->isDataFullyInitialized) {
      return;
    }

    if ($columns !== ['*'] && empty(array_diff($columns, $this->initializedColumns))) {
      return;
    }

    $columnsToLoad = $this->isDataFullyInitialized
      ? array_diff($columns, $this->initializedColumns)
      : $columns;

    $columnsData = $this->getDatabaseColumnsData($columnsToLoad);

    if ($columnsData !== null) {
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }

      if ($columns === ['*']) {
        $this->isDataFullyInitialized = true;
      } else {
        $this->initializedColumns = array_merge($this->initializedColumns, $columns);
      }
    }
  }

  /**
   * Получить ID согласия
   *
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить ID пользователя
   *
   * @return int
   */
  public function getUserID() : int
  {
    return $this->userID ?? 0;
  }

  /**
   * Получить ID формы
   *
   * @return int
   */
  public function getFormID() : int
  {
    return $this->formID ?? 0;
  }

  /**
   * Получить ID события в reports
   *
   * @return int
   */
  public function getFormReportID() : int
  {
    return $this->formReportID ?? 0;
  }

  /**
   * Получить ID статической страницы (документа)
   *
   * @return int
   */
  public function getPageStaticID() : int
  {
    return $this->pageStaticID ?? 0;
  }

  /**
   * Получить версию документа
   *
   * @return string
   */
  public function getDocumentVersion() : string
  {
    return $this->documentVersion ?? '';
  }

  /**
   * Получить локаль
   *
   * @return string
   */
  public function getLocale() : string
  {
    return $this->locale ?? '';
  }

  /**
   * Получить IP-адрес
   *
   * @return string
   */
  public function getIP() : string
  {
    return $this->ip ?? '';
  }

  /**
   * Получить User-Agent
   *
   * @return string
   */
  public function getUserAgent() : string
  {
    return $this->userAgent ?? '';
  }

  /**
   * Получить источник согласия
   *
   * @return string
   */
  public function getSource() : string
  {
    return $this->source ?? '';
  }

  /**
   * Получить время согласия
   *
   * @return int
   */
  public function getConsentedAt() : int
  {
    return $this->consentedAt ?? 0;
  }

  /**
   * Получить время отзыва
   *
   * @return int
   */
  public function getRevokedAt() : int
  {
    return $this->revokedAt ?? 0;
  }

  /**
   * Получить причину отзыва
   *
   * @return string
   */
  public function getRevokeReason() : string
  {
    return $this->revokeReason ?? '';
  }

  /**
   * Отозвано ли согласие
   *
   * @return bool
   */
  public function isRevoked() : bool
  {
    return !empty($this->revokedAt);
  }

  /**
   * Получить ID отзыва
   * 
   * @return int
   */
  public function getRevokedByID() : int
  {
    return $this->revokedByID ?? 0;
  }

  /**
   * Получить данные колонок согласия из БД
   *
   * @param array $columns
   * @return array|null
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result : null;
  }

  /**
   * Получить одно согласие по условиям
   *
   * @param CMSCore $CMSCore
   * @param array $conditions
   * @return ?Consent
   */
  private static function getOneBy(CMSCore $CMSCore, array $conditions) : ?Consent
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    $conditionPartsMysql = [];
    $conditionPartsPostgres = [];

    foreach (array_keys($conditions) as $key) {
      $conditionPartsMysql[] = '`' . $key . '` = :' . $key;
      $conditionPartsPostgres[] = '"' . $key . '" = :' . $key;
    }

    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => implode(' AND ', $conditionPartsMysql),
      'postgresql' => implode(' AND ', $conditionPartsPostgres)
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      foreach ($conditions as $key => $value) {
        $type = is_int($value) ? \PDO::PARAM_INT : (is_bool($value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR);
        $databaseQuery->bindValue(':' . $key, $value, $type);
      }
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
      $consent = new Consent($CMSCore, (int)$result['id']);
      $consent->initData();
      return $consent;
    }

    return null;
  }

  /**
   * Зафиксировать одно согласие
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @param int $formID
   * @param int $formReportID
   * @param int $pageStaticID
   * @param string $documentVersion
   * @param string $locale
   * @param string $ip
   * @param string $userAgent
   * @param string $source
   * @return ?Consent
   */
  public static function give(
    CMSCore $CMSCore,
    int $userID,
    int $formID,
    int $formReportID,
    int $pageStaticID,
    string $documentVersion,
    string $locale,
    string $ip,
    string $userAgent = '',
    string $source = 'form'
  ) : ?Consent {
    $userAgent = mb_substr($userAgent, 0, 512);
    
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('users_consents');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('formID');
    $queryBuilder->statement->addColumn('formReportID');
    $queryBuilder->statement->addColumn('pageStaticID');
    $queryBuilder->statement->addColumn('documentVersion');
    $queryBuilder->statement->addColumn('locale');
    $queryBuilder->statement->addColumn('ip');
    $queryBuilder->statement->addColumn('userAgent');
    $queryBuilder->statement->addColumn('source');
    $queryBuilder->statement->addColumn('consentedAt');
    if ($CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      $queryBuilder->statement->setClauseReturning();
      $queryBuilder->statement->clauseReturning->addColumn('id');
    }
    $queryBuilder->statement->assembly();

    $consentedAt = time();
    $userAgent = mb_substr($userAgent, 0, 512);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':formID', $formID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':formReportID', $formReportID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':pageStaticID', $pageStaticID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':documentVersion', $documentVersion, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':locale', $locale, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':ip', $ip, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userAgent', $userAgent, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':source', $source, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':consentedAt', $consentedAt, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($execute) {
      if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
        $lastID = (int)$databaseConnection->lastInsertId();
        return new Consent($CMSCore, $lastID);
      }

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new Consent($CMSCore, (int)$result['id']) : null;
    }

    return null;
  }

  /**
   * Проверить наличие свежего согласия по условиям
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @param string $ip
   * @param string $userAgent
   * @param int $pageStaticID
   * @param string $documentVersion
   * @param string $source
   * @param int $withinSeconds — временное окно (по умолчанию 300 = 5 минут)
   * @return ?Consent
   */
  public static function findRecent(
    CMSCore $CMSCore,
    int $userID,
    string $ip,
    string $userAgent,
    int $pageStaticID,
    string $documentVersion,
    string $source = 'cookie_banner',
    int $withinSeconds = 300
  ) : ?Consent {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    $threshold = time() - $withinSeconds;

    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userID` = :userID
                  AND `ip` = :ip
                  AND `userAgent` = :userAgent
                  AND `pageStaticID` = :pageStaticID
                  AND `documentVersion` = :documentVersion
                  AND `source` = :source
                  AND `consentedAt` >= :threshold
                  AND `revokedAt` IS NULL',
      'postgresql' => '"userID" = :userID
                       AND "ip" = :ip
                       AND "userAgent" = :userAgent
                       AND "pageStaticID" = :pageStaticID
                       AND "documentVersion" = :documentVersion
                       AND "source" = :source
                       AND "consentedAt" >= :threshold
                       AND "revokedAt" IS NULL'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('id');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':ip', $ip, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userAgent', $userAgent, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':pageStaticID', $pageStaticID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':documentVersion', $documentVersion, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':source', $source, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':threshold', $threshold, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
      $consent = new Consent($CMSCore, (int)$result['id']);
      $consent->initData();
      return $consent;
    }

    return null;
  }

  /**
   * Зафиксировать несколько согласий одним batch-запросом
   *
   * @param CMSCore $CMSCore
   * @param array $consents Массив вида:
   *   [
   *     ['pageStaticID' => 4, 'documentVersion' => '1.0'],
   *     ['pageStaticID' => 5, 'documentVersion' => '2.0'],
   *   ]
   * @param int $userID
   * @param int $formID
   * @param int $formReportID
   * @param string $locale
   * @param string $ip
   * @param string $userAgent
   * @param string $source
   * @return array Массив ['pageStaticID' => Consent, ...]
   */
  public static function giveBatch(
    CMSCore $CMSCore,
    array $consents,
    int $userID = 0,
    int $formID = 0,
    int $formReportID = 0,
    string $locale = '',
    string $ip = '',
    string $userAgent = '',
    string $source = 'form'
  ) : array {
    if (empty($consents)) {
      return [];
    }

    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    $databaseConnection = $CMSCore->databaseConnector->database->connection;

    $consentedAt = time();
    $userAgent = mb_substr($userAgent, 0, 512);

    $columns = [
      'userID', 'formID', 'formReportID', 'pageStaticID',
      'documentVersion', 'locale', 'ip', 'userAgent', 'source', 'consentedAt'
    ];

    $quotedColumns = [];
    foreach ($columns as $col) {
      $quotedColumns[] = match ($CMSConfigDatabase['dms']) {
        CMSDMS::MySQL => '`' . $col . '`',
        CMSDMS::PostgreSQL => '"' . $col . '"'
      };
    }

    $tableName = match ($CMSConfigDatabase['dms']) {
      CMSDMS::MySQL => '`users_consents`',
      CMSDMS::PostgreSQL => '"users_consents"'
    };

    $result = [];

    if ($CMSConfigDatabase['dms'] === CMSDMS::PostgreSQL) {
      // ============================================================
      // PostgreSQL: bulk INSERT + RETURNING (точные ID)
      // ============================================================
      $valuePlaceholders = [];
      $bindings = [];

      foreach (array_values($consents) as $index => $consent) {
        $rowPlaceholders = [
          ':userID_' . $index,
          ':formID_' . $index,
          ':formReportID_' . $index,
          ':pageStaticID_' . $index,
          ':documentVersion_' . $index,
          ':locale_' . $index,
          ':ip_' . $index,
          ':userAgent_' . $index,
          ':source_' . $index,
          ':consentedAt_' . $index
        ];

        $valuePlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';

        $bindings[':userID_' . $index] = [$userID, \PDO::PARAM_INT];
        $bindings[':formID_' . $index] = [$formID, \PDO::PARAM_INT];
        $bindings[':formReportID_' . $index] = [$formReportID, \PDO::PARAM_INT];
        $bindings[':pageStaticID_' . $index] = [(int)$consent['pageStaticID'], \PDO::PARAM_INT];
        $bindings[':documentVersion_' . $index] = [$consent['documentVersion'], \PDO::PARAM_STR];
        $bindings[':locale_' . $index] = [$locale, \PDO::PARAM_STR];
        $bindings[':ip_' . $index] = [$ip, \PDO::PARAM_STR];
        $bindings[':userAgent_' . $index] = [$userAgent, \PDO::PARAM_STR];
        $bindings[':source_' . $index] = [$source, \PDO::PARAM_STR];
        $bindings[':consentedAt_' . $index] = [$consentedAt, \PDO::PARAM_INT];
      }

      $sql = sprintf(
        'INSERT INTO %s (%s) VALUES %s RETURNING "id", "pageStaticID"',
        $tableName,
        implode(', ', $quotedColumns),
        implode(', ', $valuePlaceholders)
      );

      try {
        $databaseQuery = $databaseConnection->prepare($sql);
        foreach ($bindings as $placeholder => [$value, $type]) {
          $databaseQuery->bindValue($placeholder, $value, $type);
        }
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $rows = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
      foreach ($rows as $row) {
        $result[(int)$row['pageStaticID']] = new Consent($CMSCore, (int)$row['id']);
      }
    } else {
      // ============================================================
      // MySQL: транзакция + поштучный INSERT + lastInsertId()
      // (не полагаемся на последовательность AUTO_INCREMENT)
      // ============================================================
      $singleInsertSql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $tableName,
        implode(', ', $quotedColumns),
        implode(', ', [
          ':userID', ':formID', ':formReportID', ':pageStaticID',
          ':documentVersion', ':locale', ':ip', ':userAgent',
          ':source', ':consentedAt'
        ])
      );

      // Безопасная вложенность: если транзакция уже открыта — не открываем новую
      $ownTransaction = !$databaseConnection->inTransaction();
      if ($ownTransaction) {
        $databaseConnection->beginTransaction();
      }

      try {
        $singleQuery = $databaseConnection->prepare($singleInsertSql);

        foreach ($consents as $consent) {
          $singleQuery->bindValue(':userID', $userID, \PDO::PARAM_INT);
          $singleQuery->bindValue(':formID', $formID, \PDO::PARAM_INT);
          $singleQuery->bindValue(':formReportID', $formReportID, \PDO::PARAM_INT);
          $singleQuery->bindValue(':pageStaticID', (int)$consent['pageStaticID'], \PDO::PARAM_INT);
          $singleQuery->bindValue(':documentVersion', $consent['documentVersion'], \PDO::PARAM_STR);
          $singleQuery->bindValue(':locale', $locale, \PDO::PARAM_STR);
          $singleQuery->bindValue(':ip', $ip, \PDO::PARAM_STR);
          $singleQuery->bindValue(':userAgent', $userAgent, \PDO::PARAM_STR);
          $singleQuery->bindValue(':source', $source, \PDO::PARAM_STR);
          $singleQuery->bindValue(':consentedAt', $consentedAt, \PDO::PARAM_INT);

          $singleQuery->execute();

          $insertedID = (int)$databaseConnection->lastInsertId();
          $result[(int)$consent['pageStaticID']] = new Consent($CMSCore, $insertedID);
        }

        if ($ownTransaction) {
          $databaseConnection->commit();
        }
      } catch (PDOException $exception) {
        if ($ownTransaction) {
          $databaseConnection->rollBack();
        }

        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    return $result;
  }

  /**
   * Отозвать согласие
   *
   * @param CMSCore $CMSCore
   * @param int $consentID
   * @param string $reason
   * @return bool
   */
  public static function revoke(CMSCore $CMSCore, int $consentID, string $reason = '', int $revokedByID = 0) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('users_consents');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('revokedAt');
    $queryBuilder->statement->clauseSet->addColumn('revokeReason');
    $queryBuilder->statement->clauseSet->addColumn('revokedByID');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $revokedAt = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':revokedAt', $revokedAt, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':revokeReason', $reason, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':revokedByID', $revokedByID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':id', $consentID, \PDO::PARAM_INT);
      return $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
  }

  /**
   * Получить активные согласия пользователя
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @return array
   */
  public static function getActiveByUser(CMSCore $CMSCore, int $userID) : array
  {
    return self::getAllByUser($CMSCore, $userID, true);
  }

    /**
   * Получить все согласия с пагинацией
   *
   * @param CMSCore $CMSCore
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public static function getAll(CMSCore $CMSCore, int $limit = 20, int $offset = 0) : array
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('consentedAt');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->setClauseLimit($limit, $offset);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $consents = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($results as $row) {
      $consents[] = new Consent($CMSCore, (int) $row['id']);
    }

    return $consents;
  }

  /**
   * Получить общее количество согласий
   *
   * @param CMSCore $CMSCore
   * @return int
   */
  public static function countAll(CMSCore $CMSCore) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['COUNT(*)']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return (int) $databaseQuery->fetchColumn();
  }

  /**
   * Получить все согласия пользователя
   *
   * @param CMSCore $CMSCore
   * @param int $userID
   * @param bool $onlyActive
   * @return array
   */
  public static function getAllByUser(CMSCore $CMSCore, int $userID, bool $onlyActive = false) : array
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_consents');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    $conditionMysql = '`userID` = :userID';
    $conditionPostgres = '"userID" = :userID';

    if ($onlyActive) {
      $conditionMysql .= ' AND `revokedAt` IS NULL';
      $conditionPostgres .= ' AND "revokedAt" IS NULL';
    }

    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => $conditionMysql,
      'postgresql' => $conditionPostgres
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseOrderBy();
    $queryBuilder->statement->clauseOrderBy->setColumn('consentedAt');
    $queryBuilder->statement->clauseOrderBy->setSortType('DESC');
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $consents = [];
    $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) {
        $consents[] = new Consent($CMSCore, (int)$row['id']);
      }
    }

    return $consents;
  }
}
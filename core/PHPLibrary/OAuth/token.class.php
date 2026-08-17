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

namespace core\PHPLibrary\OAuth;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Token
{
  private CoreInterface $CMSCore;
  private int $id;
  
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return void
   */
  public function __construct(CoreInterface $CMSCore, int $id)
  {
    $this->CMSCore = $CMSCore;
    $this->id = $id;
  }

  /**
   * Инициализировать данные
   * 
   * @param array $columns
   * 
   * @return void
   */
  public function initData(array $columns = ['*']) : void
  {
    $columnsData = $this->getDatabaseColumnsData($columns);
    if ($columnsData !== null) {
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
    }
  }

  /**
   * Получить ID
   *
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить accessToken
   *
   * @return string
   */
  public function getAccessToken() : string
  {
    return $this->accessToken ?? '';
  }

  /**
   * Получить refreshToken
   *
   * @return string|null
   */
  public function getRefreshToken() : string|null
  {
    return $this->refreshToken ?? null;
  }

  /**
   * Получить ID клиента
   *
   * @return int
   */
  public function getClientID() : int
  {
    return $this->clientID ?? 0;
  }

  /**
   * Получить объект клиента
   *
   * @return Client|null
   */
  public function getClient() : Client|null
  {
    if (!property_exists($this, 'clientID')) {
      $this->initData(['clientID']);
    }

    return Client::existsByID($this->CMSCore, $this->clientID)
      ? new Client($this->CMSCore, $this->clientID)
      : null;
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
   * Получить объект пользователя
   *
   * @return User|null
   */
  public function getUser() : User|null
  {
    if (!property_exists($this, 'userID')) {
      $this->initData(['userID']);
    }

    return User::existsByID($this->CMSCore, $this->userID)
      ? new User($this->CMSCore, $this->userID)
      : null;
  }

  /**
   * Получить scopes
   *
   * @return string
   */
  public function getScopes() : string
  {
    return $this->scopes ?? '';
  }

  /**
   * Получить массив scopes
   *
   * @return array
   */
  public function getScopesArray() : array
  {
    $scopes = $this->getScopes();
    return !empty($scopes) ? explode(' ', $scopes) : [];
  }

  /**
   * Получить временную отметку истечения срока действия
   *
   * @return int
   */
  public function getExpiresAt() : int
  {
    return $this->expiresAt ?? 0;
  }

  /**
   * Проверить, отозван ли токен
   *
   * @return bool
   */
  public function isRevoked() : bool
  {
    return (bool)($this->isRevoked ?? false);
  }

  /**
   * Получить временную отметку отзыва
   *
   * @return int
   */
  public function getRevokedAt() : int
  {
    return $this->revokedAt ?? 0;
  }

  /**
   * Получить временную отметку создания
   *
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Проверить, истёк ли срок действия токена
   *
   * @return bool
   */
  public function isExpired() : bool
  {
    return $this->getExpiresAt() < time();
  }

  /**
   * Проверить, действителен ли токен
   *
   * @return bool
   */
  public function isValid() : bool
  {
    return !$this->isRevoked() && !$this->isExpired();
  }

  /**
   * Проверить наличие указанного scope
   *
   * @param string $scope
   * 
   * @return bool
   */
  public function hasScope(string $scope) : bool
  {
    return in_array($scope, $this->getScopesArray(), true);
  }

  /**
   * Проверить наличие всех указанных scopes
   *
   * @param string $scopes
   * 
   * @return bool
   */
  public function hasScopes(string $scopes) : bool
  {
    $tokenScopes = $this->getScopesArray();
    $requestedScopes = explode(' ', $scopes);
    
    foreach ($requestedScopes as $scope) {
      if (!in_array($scope, $tokenScopes, true)) {
        return false;
      }
    }
    
    return true;
  }

  /**
   * Сгенерировать accessToken
   *
   * @return string
   */
  public static function generateAccessToken() : string
  {
    return 'gra_' . bin2hex(random_bytes(48));
  }

  /**
   * Сгенерировать refreshToken
   *
   * @return string
   */
  public static function generateRefreshToken() : string
  {
    return 'grr_' . bin2hex(random_bytes(64));
  }

  /**
   * Получить данные колонок из БД
   *
   * @param array $columns
   * 
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
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $tokenID = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $tokenID, \PDO::PARAM_INT);
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
   * Получить токен по accessToken
   *
   * @param CoreInterface $CMSCore
   * @param string $accessToken
   * 
   * @return Token|null
   */
  public static function getByAccessToken(CoreInterface $CMSCore, string $accessToken) : Token|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`accessToken` = :accessToken',
      'postgresql' => '"accessToken" = :accessToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Token($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Получить токен по refreshToken
   *
   * @param CoreInterface $CMSCore
   * @param string $refreshToken
   * 
   * @return Token|null
   */
  public static function getByRefreshToken(CoreInterface $CMSCore, string $refreshToken) : Token|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`refreshToken` = :refreshToken',
      'postgresql' => '"refreshToken" = :refreshToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Token($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Проверить существование токена по accessToken
   *
   * @param CoreInterface $CMSCore
   * @param string $accessToken
   * 
   * @return bool
   */
  public static function existsByAccessToken(CoreInterface $CMSCore, string $accessToken) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`accessToken` = :accessToken',
      'postgresql' => '"accessToken" = :accessToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Получить количество активных токенов клиента
   *
   * @param CoreInterface $CMSCore
   * @param int $clientID
   * 
   * @return int
   */
  public static function getActiveCountByClientID(CoreInterface $CMSCore, int $clientID) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['COUNT(*)']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`clientID` = :clientID AND `isRevoked` = :isRevoked AND `expiresAt` > :now',
      'postgresql' => '"clientID" = :clientID AND "isRevoked" = :isRevoked AND "expiresAt" > :now'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $isRevoked = false;
    $now = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':clientID', $clientID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $isRevoked, \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':now', $now, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return (int)$databaseQuery->fetchColumn();
  }

  /**
   * Создать токен
   *
   * @param CoreInterface $CMSCore
   * @param array $data
   * 
   * @return Token|null
   */
  public static function create(CoreInterface $CMSCore, array $data) : Token|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('oauth_access_tokens');
    $queryBuilder->statement->addColumn('accessToken');
    $queryBuilder->statement->addColumn('refreshToken');
    $queryBuilder->statement->addColumn('clientID');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('scopes');
    $queryBuilder->statement->addColumn('expiresAt');
    $queryBuilder->statement->addColumn('isRevoked');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $accessToken = self::generateAccessToken();
    $refreshToken = self::generateRefreshToken();
    $createdUnixTimestamp = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':clientID', $data['clientID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':userID', $data['userID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':scopes', $data['scopes'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':expiresAt', $data['expiresAt'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $data['isRevoked'], \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($CMSConfigDatabase['dms'] === \core\PHPLibrary\Database\DatabaseManagementSystem::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
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
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new Token($CMSCore, (int)$result['id']) : null;
    }

    return null;
  }

  /**
   * Отозвать токен
   *
   * @return bool
   */
  public function revoke() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('oauth_access_tokens');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('isRevoked');
    $queryBuilder->statement->clauseSet->addColumn('revokedAt');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $isRevoked = true;
    $revokedAt = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $isRevoked, \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':revokedAt', $revokedAt, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }

  /**
   * Отозвать все токены пользователя
   *
   * @param CoreInterface $CMSCore
   * @param int $userID
   * 
   * @return int Количество отозванных токенов
   */
  public static function revokeAllByUserID(CoreInterface $CMSCore, int $userID) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('oauth_access_tokens');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('isRevoked');
    $queryBuilder->statement->clauseSet->addColumn('revokedAt');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userID` = :userID AND `isRevoked` = :isRevoked',
      'postgresql' => '"userID" = :userID AND "isRevoked" = :isRevoked'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $isRevoked = false;
    $setRevoked = true;
    $revokedAt = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $setRevoked, \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':revokedAt', $revokedAt, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->rowCount();
  }

  /**
   * Отозвать все токены клиента
   *
   * @param CoreInterface $CMSCore
   * @param int $clientID
   * 
   * @return int Количество отозванных токенов
   */
  public static function revokeAllByClientID(CoreInterface $CMSCore, int $clientID) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('oauth_access_tokens');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('isRevoked');
    $queryBuilder->statement->clauseSet->addColumn('revokedAt');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`clientID` = :clientID AND `isRevoked` = :isRevoked',
      'postgresql' => '"clientID" = :clientID AND "isRevoked" = :isRevoked'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $isRevoked = true;
    $revokedAt = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':clientID', $clientID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $isRevoked, \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':revokedAt', $revokedAt, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->rowCount();
  }

  /**
   * Удалить просроченные и отозванные токены
   *
   * @param CoreInterface $CMSCore
   * 
   * @return int Количество удалённых записей
   */
  public static function deleteExpired(CoreInterface $CMSCore) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_access_tokens');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '(`expiresAt` < :now AND `isRevoked` = :isRevokedTrue) OR `isRevoked` = :isRevokedFalse',
      'postgresql' => '("expiresAt" < :now AND "isRevoked" = :isRevokedTrue) OR "isRevoked" = :isRevokedFalse'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $now = time();
    $isRevokedTrue = true;
    $isRevokedFalse = false;

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':now', $now, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevokedTrue', $isRevokedTrue, \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':isRevokedFalse', $isRevokedFalse, \PDO::PARAM_BOOL);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->rowCount();
  }
}
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
class AuthCode
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
   * Получить код авторизации
   *
   * @return string
   */
  public function getCode() : string
  {
    return $this->code ?? '';
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
   * Получить redirectURI
   *
   * @return string
   */
  public function getRedirectURI() : string
  {
    return $this->redirectURI ?? '';
  }

  /**
   * Получить codeChallenge (для PKCE)
   *
   * @return string|null
   */
  public function getCodeChallenge() : string|null
  {
    return $this->codeChallenge ?? null;
  }

  /**
   * Получить codeChallengeMethod
   *
   * @return string
   */
  public function getCodeChallengeMethod() : string
  {
    return $this->codeChallengeMethod ?? 'S256';
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
   * Проверить, отозван ли код
   *
   * @return bool
   */
  public function isRevoked() : bool
  {
    return (bool)($this->isRevoked ?? false);
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
   * Проверить, истёк ли срок действия кода
   *
   * @return bool
   */
  public function isExpired() : bool
  {
    return $this->getExpiresAt() < time();
  }

  /**
   * Проверить, действителен ли код
   *
   * @return bool
   */
  public function isValid() : bool
  {
    return !$this->isRevoked() && !$this->isExpired();
  }

  /**
   * Проверить codeVerifier по PKCE
   *
   * @param string $codeVerifier
   * 
   * @return bool
   */
  public function verifyCodeChallenge(string $codeVerifier) : bool
  {
    $codeChallenge = $this->getCodeChallenge();
    
    // Если codeChallenge отсутствует — PKCE не использовался, проверка не требуется
    if ($codeChallenge === null) {
      return true;
    }

    $method = $this->getCodeChallengeMethod();

    return match ($method) {
      'S256' => hash_equals(
        $codeChallenge,
        self::generateCodeChallenge($codeVerifier)
      ),
      'plain' => hash_equals($codeChallenge, $codeVerifier),
      default => false,
    };
  }

  /**
   * Сгенерировать код авторизации
   *
   * @return string
   */
  public static function generateCode() : string
  {
    return 'grc_' . bin2hex(random_bytes(32));
  }

  /**
   * Сгенерировать codeChallenge из codeVerifier (S256)
   *
   * @param string $codeVerifier
   * 
   * @return string
   */
  public static function generateCodeChallenge(string $codeVerifier) : string
  {
    return strtr(rtrim(
      base64_encode(hash('sha256', $codeVerifier, true)),
      '='
    ), '+/', '-_');
  }

  /**
   * Сгенерировать codeVerifier для PKCE
   *
   * @return string
   */
  public static function generateCodeVerifier() : string
  {
    return bin2hex(random_bytes(32));
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
    $queryBuilder->statement->clauseFrom->addTable('oauth_auth_codes');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $authCodeID = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $authCodeID, \PDO::PARAM_INT);
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
   * Получить код авторизации по значению code
   *
   * @param CoreInterface $CMSCore
   * @param string $code
   * 
   * @return AuthCode|null
   */
  public static function getByCode(CoreInterface $CMSCore, string $code) : AuthCode|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_auth_codes');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`code` = :code',
      'postgresql' => '"code" = :code'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':code', $code, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new AuthCode($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Проверить существование кода по значению
   *
   * @param CoreInterface $CMSCore
   * @param string $code
   * 
   * @return bool
   */
  public static function existsByCode(CoreInterface $CMSCore, string $code) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_auth_codes');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`code` = :code',
      'postgresql' => '"code" = :code'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':code', $code, \PDO::PARAM_STR);
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
   * Создать код авторизации
   *
   * @param CoreInterface $CMSCore
   * @param array $data
   * 
   * @return AuthCode|null
   */
  public static function create(CoreInterface $CMSCore, array $data) : AuthCode|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('oauth_auth_codes');
    $queryBuilder->statement->addColumn('code');
    $queryBuilder->statement->addColumn('clientID');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('scopes');
    $queryBuilder->statement->addColumn('redirectURI');
    $queryBuilder->statement->addColumn('codeChallenge');
    $queryBuilder->statement->addColumn('codeChallengeMethod');
    $queryBuilder->statement->addColumn('expiresAt');
    $queryBuilder->statement->addColumn('isRevoked');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $code = self::generateCode();
    $createdUnixTimestamp = time();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':code', $code, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':clientID', $data['clientID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':userID', $data['userID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':scopes', $data['scopes'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':redirectURI', $data['redirectURI'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':codeChallenge', $data['codeChallenge'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':codeChallengeMethod', $data['codeChallengeMethod'], \PDO::PARAM_STR);
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
      $queryBuilder->statement->clauseFrom->addTable('oauth_auth_codes');
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
      return $result ? new AuthCode($CMSCore, (int)$result['id']) : null;
    }

    return null;
  }

  /**
   * Отозвать код авторизации
   *
   * @return bool
   */
  public function revoke() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('oauth_auth_codes');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('isRevoked');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $isRevoked = true;

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $isRevoked, \PDO::PARAM_BOOL);
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
   * Удалить просроченные коды авторизации
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
    $queryBuilder->statement->clauseFrom->addTable('oauth_auth_codes');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`expiresAt` < :now OR `isRevoked` = :isRevoked',
      'postgresql' => '"expiresAt" < :now OR "isRevoked" = :isRevoked'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $now = time();
    $isRevoked = true;

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':now', $now, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isRevoked', $isRevoked, \PDO::PARAM_BOOL);
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
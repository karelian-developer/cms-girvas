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
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class Client
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
   * Получить clientID (публичный идентификатор)
   *
   * @return string
   */
  public function getClientID() : string
  {
    return $this->clientID ?? '';
  }

  /**
   * Получить clientSecret (хешированный)
   *
   * @return string
   */
  public function getClientSecret() : string
  {
    return $this->clientSecret ?? '';
  }

  /**
   * Получить название приложения
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }

  /**
   * Получить описание приложения
   *
   * @return string
   */
  public function getDescription() : string
  {
    return $this->description ?? '';
  }

  /**
   * Получить разрешённые redirectURI
   *
   * @return string
   */
  public function getRedirectURI() : string
  {
    return $this->redirectURI ?? '';
  }

  /**
   * Получить массив разрешённых redirectURI
   *
   * @return array
   */
  public function getRedirectURIArray() : array
  {
    $redirectURI = $this->getRedirectURI();
    return !empty($redirectURI) ? explode(' ', $redirectURI) : [];
  }

  /**
   * Получить разрешённые grantTypes
   *
   * @return string
   */
  public function getGrantTypes() : string
  {
    return $this->grantTypes ?? '';
  }

  /**
   * Получить массив разрешённых grantTypes
   *
   * @return array
   */
  public function getGrantTypesArray() : array
  {
    $grantTypes = $this->getGrantTypes();
    return !empty($grantTypes) ? explode(' ', $grantTypes) : [];
  }

  /**
   * Получить разрешённые scopes
   *
   * @return string
   */
  public function getScopes() : string
  {
    return $this->scopes ?? '';
  }

  /**
   * Получить массив разрешённых scopes
   *
   * @return array
   */
  public function getScopesArray() : array
  {
    $scopes = $this->getScopes();
    return !empty($scopes) ? explode(' ', $scopes) : [];
  }

  /**
   * Получить ID владельца приложения
   *
   * @return int
   */
  public function getUserID() : int
  {
    return $this->userID ?? 0;
  }

  /**
   * Получить объект владельца приложения
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
   * Проверить активность клиента
   *
   * @return bool
   */
  public function isActive() : bool
  {
    return (bool)($this->isActive ?? false);
  }

  /**
   * Проверить верификацию клиента
   *
   * @return bool
   */
  public function isVerified() : bool
  {
    return (bool)($this->isVerified ?? false);
  }

  /**
   * Получить временную отметку верификации
   *
   * @return int
   */
  public function getVerifiedAt() : int
  {
    return $this->verifiedAt ?? 0;
  }

  /**
   * Получить ID администратора, верифицировавшего клиента
   *
   * @return int
   */
  public function getVerifiedBy() : int
  {
    return $this->verifiedBy ?? 0;
  }

  /**
   * Получить email владельца приложения
   *
   * @return string
   */
  public function getOwnerEmail() : string
  {
    return $this->ownerEmail ?? '';
  }

  /**
   * Получить максимальное количество токенов
   *
   * @return int
   */
  public function getMaxTokens() : int
  {
    return $this->maxTokens ?? 100;
  }

  /**
   * Получить время жизни токена (в секундах)
   *
   * @return int
   */
  public function getTokenTTL() : int
  {
    return $this->tokenTTL ?? 3600;
  }

  /**
   * Получить белый список IP клиента
   *
   * @return string|null
   */
  public function getAllowedIPs() : string|null
  {
    return $this->allowedIPs ?? null;
  }

  /**
   * Получить массив белого списка IP клиента
   *
   * @return array
   */
  public function getAllowedIPsArray() : array
  {
    $allowedIPs = $this->getAllowedIPs();
    return !empty($allowedIPs) ? explode(' ', $allowedIPs) : [];
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
   * Получить временную отметку обновления
   *
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Проверить, поддерживает ли клиент указанный grantType
   *
   * @param string $grantType
   * 
   * @return bool
   */
  public function supportsGrantType(string $grantType) : bool
  {
    return in_array($grantType, $this->getGrantTypesArray(), true);
  }

  /**
   * Проверить, разрешён ли указанный redirectURI для клиента
   *
   * @param string $redirectURI
   * 
   * @return bool
   */
  public function isRedirectURIAllowed(string $redirectURI) : bool
  {
    $allowedURIs = $this->getRedirectURIArray();
    
    foreach ($allowedURIs as $allowed) {
      if (hash_equals($allowed, $redirectURI)) {
        $parsed = parse_url($redirectURI);
        
        // Запрещаем не-HTTP схемы
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
          return false;
        }
        
        return true;
      }
    }
    
    return false;
  }

  /**
   * Проверить, разрешены ли запрашиваемые scopes для клиента
   *
   * @param string $requestedScopes
   * 
   * @return bool
   */
  public function areScopesAllowed(string $requestedScopes) : bool
  {
    $clientScopes = $this->getScopesArray();
    $requestedScopesArray = explode(' ', $requestedScopes);
    
    foreach ($requestedScopesArray as $scope) {
      if (!in_array($scope, $clientScopes, true)) {
        return false;
      }
    }
    
    return true;
  }

  /**
   * Проверить IP клиента по белому списку
   *
   * @param string $ip
   * 
   * @return bool
   */
  public function isIPAllowed(string $ip) : bool
  {
    $allowedIPs = $this->getAllowedIPsArray();
    
    // Если белый список пуст — разрешены все IP
    if (empty($allowedIPs)) {
      return true;
    }
    
    return in_array($ip, $allowedIPs, true);
  }

  /**
   * Проверить clientSecret
   *
   * @param string $secret
   * 
   * @return bool
   */
  public function verifySecret(string $secret) : bool
  {
    return password_verify($secret, $this->getClientSecret());
  }

  /**
   * Хешировать clientSecret
   *
   * @param string $secret
   * 
   * @return string
   */
  public static function hashSecret(string $secret) : string
  {
    return password_hash($secret, PASSWORD_BCRYPT, ['cost' => 12]);
  }

  /**
   * Сгенерировать clientID
   *
   * @return string
   */
  public static function generateClientID() : string
  {
    return 'grv_' . bin2hex(random_bytes(16));
  }

  /**
   * Сгенерировать clientSecret (открытый текст для показа один раз)
   *
   * @return string
   */
  public static function generateClientSecret() : string
  {
    return 'grs_' . bin2hex(random_bytes(32));
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
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $clientID = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $clientID, \PDO::PARAM_INT);
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
   * Получить клиента по clientID
   *
   * @param CoreInterface $CMSCore
   * @param string $clientID
   * 
   * @return Client|null
   */
  public static function getByClientID(CoreInterface $CMSCore, string $clientID) : Client|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`clientID` = :clientID',
      'postgresql' => '"clientID" = :clientID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':clientID', $clientID, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Client($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Получить клиента по ID владельца и названию
   *
   * @param CoreInterface $CMSCore
   * @param int $userID
   * @param string $name
   * 
   * @return Client|null
   */
  public static function getByUserIDAndName(CoreInterface $CMSCore, int $userID, string $name) : Client|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userID` = :userID AND `name` = :name',
      'postgresql' => '"userID" = :userID AND "name" = :name'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Client($CMSCore, (int)$result['id']) : null;
  }

  /**
   * Проверить существование клиента по clientID
   *
   * @param CoreInterface $CMSCore
   * @param string $clientID
   * 
   * @return bool
   */
  public static function existsByClientID(CoreInterface $CMSCore, string $clientID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`clientID` = :clientID',
      'postgresql' => '"clientID" = :clientID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':clientID', $clientID, \PDO::PARAM_STR);
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
   * Проверить существование клиента по ID
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return bool
   */
  public static function existsByID(CoreInterface $CMSCore, int $id) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $id, \PDO::PARAM_INT);
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
   * Создать OAuth-клиента
   *
   * @param CoreInterface $CMSCore
   * @param array $data
   * 
   * @return Client|null
   */
  public static function create(CoreInterface $CMSCore, array $data) : Client|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('oauth_clients');
    $queryBuilder->statement->addColumn('clientID');
    $queryBuilder->statement->addColumn('clientSecret');
    $queryBuilder->statement->addColumn('name');
    $queryBuilder->statement->addColumn('description');
    $queryBuilder->statement->addColumn('redirectURI');
    $queryBuilder->statement->addColumn('grantTypes');
    $queryBuilder->statement->addColumn('scopes');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('isActive');
    $queryBuilder->statement->addColumn('isVerified');
    $queryBuilder->statement->addColumn('ownerEmail');
    $queryBuilder->statement->addColumn('maxTokens');
    $queryBuilder->statement->addColumn('tokenTTL');
    $queryBuilder->statement->addColumn('allowedIPs');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $clientID = self::generateClientID();
    $clientSecret = self::generateClientSecret();
    $clientSecretHash = self::hashSecret($clientSecret);
    $createdUnixTimestamp = time();
    $updatedUnixTimestamp = $createdUnixTimestamp;

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':clientID', $clientID, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':clientSecret', $clientSecretHash, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':name', $data['name'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':description', $data['description'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':redirectURI', $data['redirectURI'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':grantTypes', $data['grantTypes'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':scopes', $data['scopes'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userID', $data['userID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':isActive', $data['isActive'], \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':isVerified', $data['isVerified'], \PDO::PARAM_BOOL);
      $databaseQuery->bindParam(':ownerEmail', $data['ownerEmail'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':maxTokens', $data['maxTokens'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':tokenTTL', $data['tokenTTL'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':allowedIPs', $data['allowedIPs'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
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
      $client = $result ? new Client($CMSCore, (int)$result['id']) : null;
      
      // Возвращаем открытый clientSecret только при создании
      if ($client !== null) {
        $client->plainSecret = $clientSecret;
      }
      
      return $client;
    }

    return null;
  }

  /**
   * Обновить OAuth-клиента
   *
   * @param array $data
   * 
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('oauth_clients');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'clientID', 'clientSecret', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    $queryBuilder->statement->clauseSet->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $updatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'clientID', 'clientSecret', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
          $valueTypeName = gettype($value);
          $valueType = match ($valueTypeName) {
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'null' => \PDO::PARAM_NULL,
          };
          
          $databaseQuery->bindParam(':' . $name, $data[$name], $valueType);
        }
      }
      
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
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
   * Удалить OAuth-клиента
   *
   * @return bool
   */
  public function delete() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('oauth_clients');
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
   * Верифицировать клиента
   *
   * @param int $verifiedBy
   * 
   * @return bool
   */
  public function verify(int $verifiedBy) : bool
  {
    return $this->update([
      'isVerified' => true,
      'verifiedAt' => time(),
      'verifiedBy' => $verifiedBy
    ]);
  }

  /**
   * Отозвать верификацию клиента
   *
   * @return bool
   */
  public function unverify() : bool
  {
    return $this->update([
      'isVerified' => false,
      'verifiedAt' => null,
      'verifiedBy' => null
    ]);
  }

  /**
   * Обновить clientSecret
   *
   * @return string|null Новый открытый secret (только в момент создания)
   */
  public function regenerateSecret() : string|null
  {
    $newSecret = self::generateClientSecret();
    $newSecretHash = self::hashSecret($newSecret);
    
    $result = $this->update(['clientSecret' => $newSecretHash]);
    
    return $result ? $newSecret : null;
  }
}
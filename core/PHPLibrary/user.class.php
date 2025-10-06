<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\SystemCore as CMSCore;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
/**
 * Class User
 * @package core\PHPLibrary
 * 
 * @property-read CMSCore $CMSCore Класс системного ядра CMS
 * @property int $id ID пользователя
 */
class User
{
  // ID администратора системы (суперпользователя)
  public const ADMIN_ID = 1;

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
   * Инициализировать данные
   * 
   * @param array $columns
   * 
   * @return void
   */
  public function initData(array $columns = ['*']) : void
  {
    $columns = $this->getDatabaseColumnsData($columns);
    foreach ($columns as $name => $data) {
      $this->{$name} = $data;
    }
  }
  
  /**
   * Получить ID
   *
   * @param  mixed $value
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить логин
   * 
   * @return string
   */
  public function getLogin() : string
  {
    return $this->login ?? '';
  }

  /**
   * Получить E-Mail
   * 
   * @return string
   */
  public function getEmail() : string
  {
    return $this->email ?? '';
  }

  /**
   * Получить хеш пароля
   * 
   * @return string
   */
  public function getPasswordHash() : string
  {
    return $this->passwordHash ?? '';
  }

  /**
   * Получить хеш-ключ
   * 
   * @return string
   */
  public function getSecurityHash() : string
  {
    return $this->securityHash ?? '';
  }

  /**
   * Получить временную отментку создания данных в UNIX-формате
   * 
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить временную отментку обновления данных в UNIX-формате
   * 
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Получить URL дефолтного аватара пользователя
   * 
   * @param CMSCore $CMSCore
   * @param int $size
   * 
   * @return string
   */
  public static function getAvatarDefaultURL(CMSCore $CMSCore, int $size) : string
  {
    return '/' . $CMSCore->theme->getURL() . '/images/avatar_default_' . $size . '.webp';
  }
  
  /**
   * Получить имя пользователя
   *
   * @return string
   */
  public function getName() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['name'])) {
        return $metadata['name'];
      }
    }

    return '';
  }
  
  /**
   * Получить фамилию пользователя
   *
   * @return string
   */
  public function getSurname() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['surname'])) {
        return $metadata['surname'];
      }
    }

    return '';
  }
  
  /**
   * Получить отчество пользователя
   *
   * @return string
   */
  public function getPatronymic() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['patronymic'])) {
        return $metadata['patronymic'];
      }
    }

    return '';
  }
  
  /**
   * Получить ID группы пользователя
   *
   * @return int
   */
  public function getGroupID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['groupID'])) {
        return $metadata['groupID'];
      }
    }

    return 0;
  }
  
  /**
   * Получить статус блокировки пользователя
   *
   * @return bool
   */
  public function isBlocked() : bool
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['isBlocked'])) {
        return (bool) $metadata['isBlocked'];
      }
    }

    return false;
  }
  
  /**
   * Получить объект группы пользователя
   *
   * @return ?UserGroup
   */
  public function getGroup() : ?UserGroup
  {
    $groupID = $this->getGroupID();
    
    if (UserGroup::existsByID($this->CMSCore, $groupID)) {
      return new UserGroup($this->CMSCore, $groupID);
    }

    return null;
  }

  /**
   * Получить временную метку создания токена для сброса пароля
   *
   * @return string
   */
  public function getPasswordResetCreatedUnixTimestamp() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['passwordResetTokenCreatedUnixTimestamp'])) {
        return $metadata['passwordResetTokenCreatedUnixTimestamp'];
      }
    }

    return 0;
  }

  /**
   * Получить токен сброса пароля
   *
   * @return string
   */
  public function getPasswordResetToken() : string
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['passwordResetToken'])) {
        return $metadata['passwordResetToken'];
      }
    }

    return '';
  }
  
  /**
   * Получить отчество пользователя
   *
   * @return int
   */
  public function getBirthdateUnixTimestamp() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      
      if (isset($metadata['birthdateUnixTimestamp'])) {
        return $metadata['birthdateUnixTimestamp'];
      }
    }

    return 0;
  }

  /**
   * Получить данные по дополнительному полю
   * 
   * @param string $fieldName
   * 
   * @return string|null
   */
  public function getAdditionalFieldData(string $fieldName) : string|null
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['additionalFields'])) {
        return $metadata['additionalFields'][$fieldName] ?? null;
      }
    }

    return null;
  }
  
  /**
   * Получить URL до аватарки пользователя
   *
   * @param  mixed $size
   * 
   * @return string
   */
  public function getAvatarURL(int $size) : string
  {
    $filePath = CMS_ROOT_DIRECTORY . '/uploads/avatars/' . $this->id . '/' . $size . '.webp';
    $fileURL = '/uploads/avatars/' . $this->id . '/' . $size . '.webp';
    
    if (file_exists($filePath)) {
      return $fileURL;
    }

    return self::getAvatarDefaultURL($this->CMSCore, $size);
  }

  /**
   * Хешировать строку
   * 
   * @param string $string
   * 
   * @return string
   */
  public function hashing(string $string) : string
  {
    $userID = $this->getID();
    $securityHash = $this->getSecurityHash();
    $CMSSalt = $this->CMSCore->configurator->get('salt');
    $hashSource = sprintf('{GIRVAS:%s:%d+%s=>%s}', $securityHash, $userID, $CMSSalt, $string);
    return md5($hashSource);
  }

  /**
   * Хешировать пароль
   * 
   * @param CMSCore $CMSCore
   * @param string $userSecurityHash
   * @param string $password
   * 
   * @return string
   */
  public static function passwordHash(CMSCore $CMSCore, string $userSecurityHash, string $password) : string
  {
    $CMSSalt = $CMSCore->configurator->get('salt');
    $passwordHashingAlgorithm = $CMSCore->configurator->get('passwordHashingAlgorithm');
    $cryptSource = sprintf('{GIRVAS:%s+%s=>%s}', $userSecurityHash, $CMSSalt, $password);
    return password_hash($cryptSource, $passwordHashingAlgorithm);
  }

  /**
   * Проверить пароль
   * 
   * @param string $password
   * 
   * @return bool
   */
  public function passwordVerify(string $password) : bool
  {
    $CMSSalt = $this->CMSCore->configurator->get('salt');
    $cryptSource = sprintf('{GIRVAS:%s+%s=>%s}', $this->getSecurityHash(), $CMSSalt, $password);
    return password_verify($cryptSource, $this->getPasswordHash());
  }

  /**
   * Сгенерировать хеш-ключ
   * 
   * @param CMSCore $CMSCore
   * 
   * @return string
   */
  public static function generateSecurityHash(CMSCore $CMSCore) : string
  {
    $CMSSalt = $CMSCore->configurator->get('salt');
    return md5(sprintf('{GIRVAS:%s+%d}', $CMSSalt, time()));
  }

  /**
   * Проверка пользователя на суперпользователя
   * 
   * @return bool
   */
  public function isSuperAdmin() : bool
  {
    return $this->id === self::ADMIN_ID;
  }

  /**
   * Получить данные с колонок в БД
   * 
   * @param array $columns
   * 
   * @return array
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $userID Идентификационный номер записи */
    $userID = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $userID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result : null;
  }
  
  /**
   * Получить объекта пользователя по логину
   *
   * @param  CMSCore $CMSCore
   * @param  string $userLogin
   * 
   * @return User|null
   */
  public static function getByLogin(CMSCore $CMSCore, string $userLogin) : User|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'LOWER(`login`) = :login',
      'postgresql' => 'LOWER("login") = :login'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $userLogin = strtolower($userLogin);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':login', $userLogin, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    
    return $result ? new User($CMSCore, (int)$result['id']) : null;
  }
  
  /**
   * Получить объекта пользователя по адресу электронной почты
   *
   * @param  CMSCore $CMSCore
   * @param  string $userEmail
   * 
   * @return User|null
   */
  public static function getByEmail(CMSCore $CMSCore, string $userEmail) : User|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'LOWER(`email`) = :email',
      'postgresql' => 'LOWER("email") = :email'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $userEmail = strtolower($userEmail);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':email', $userEmail, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    
    return $result ? new User($CMSCore, (int)$result['id']) : null;
  }
  
  /**
   * Проверить существование пользователя по логину
   *
   * @param CMSCore $CMSCore
   * @param string $userLogin
   * @param bool $registerIsAccounting
   * 
   * @return bool
   */
  public static function existsByLogin(CMSCore $CMSCore, string $userLogin, bool $registerIsAccounting = false) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();

    if (!$registerIsAccounting) {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => 'LOWER(`login`) = :login',
        'postgresql' => 'LOWER("login") = :login'
      ]);

      $userLogin = strtolower($userLogin);
    } else {
      $queryBuilder->statement->clauseWhere->addConditionAdaptive([
        'mysql' => '`login` = :login',
        'postgresql' => '"login" = :login'
      ]);
    }

    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':login', $userLogin, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    
    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Проверить существование пользователя по E-Mail
   *
   * @param  CMSCore $CMSCore
   * @param  string $userLogin
   * 
   * @return bool
   */
  public static function existsByEmail(CMSCore $CMSCore, string $userEmail) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'LOWER(`email`) = :email',
      'postgresql' => 'LOWER("email") = :email'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $userEmail = strtolower($userEmail);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':email', $userEmail, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    
    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Проверить существование пользователя по ID
   *
   * @param  CMSCore $CMSCore
   * @param  int $userID
   * 
   * @return bool
   */
  public static function existsByID(CMSCore $CMSCore, int $userID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
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
      $databaseQuery->bindParam(':id', $userID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Удаление существующего пользователя
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
    $queryBuilder->statement->clauseFrom->addTable('users');
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
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }

  /**
   * Создать пользователя
   * 
   * @param CMSCore $CMSCore
   * @param string $userLogin
   * @param string $userEmail
   * @param string $userPassword
   * 
   * @return User|null
   */
  public static function create(CMSCore $CMSCore, string $userLogin, string $userEmail, string $userPassword) : User|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('users');
    $queryBuilder->statement->addColumn('login');
    $queryBuilder->statement->addColumn('email');
    $queryBuilder->statement->addColumn('passwordHash');
    $queryBuilder->statement->addColumn('securityHash');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->addColumn('emailIsSubmitted');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $userSecurityHash = self::generateSecurityHash($CMSCore);
    $userPasswordHash = self::passwordHash($CMSCore, $userSecurityHash, $userPassword);
    $userCreatedUnixTimestamp = time();
    $userUpdatedUnixTimestamp = $userCreatedUnixTimestamp;

    $userMetadata = [
      'name' => '',
      'surname' => '',
      'patronymic' => '',
      'groupID' => 4,
      'passwordResetToken' => '',
      'passwordResetTokenCreatedUnixTimestamp' => '',
    ];

    $userMetadataJSON = json_encode($userMetadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $userEmailIsSubmitted = false;

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':login', $userLogin, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':email', $userEmail, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':passwordHash', $userPasswordHash, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':securityHash', $userSecurityHash, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $userCreatedUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $userUpdatedUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':metadata', $userMetadataJSON, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':emailIsSubmitted', $userEmailIsSubmitted, \PDO::PARAM_BOOL);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      error_log('SQL: ' . $queryBuilder->statement->assembled);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new User($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновить пользователя
   *
   * @param  array $data Массив данных
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('users');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }
    
    foreach (['metadata'] as $columnName) {
      $fieldsJSON = [];
      
      if (!isset($data[$columnName])) {
        continue;
      }

      foreach ($data[$columnName] as $name => $value) {
        $valueJSON = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fieldsJSON[] = match ($queryBuilder->DMS) {
          CMSDMS::MySQL => sprintf('"%s": %s', $name, $valueJSON),
          CMSDMS::PostgreSQL => sprintf('\'{"%s": %s}\'::jsonb', $name, $valueJSON)
        };
      }

      if (!empty($data[$columnName])) {
        $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
          'mysql' => 'JSON_MERGE_PATCH(COALESCE(' . $columnName . ', \'{}\'), CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON))',
          'postgresql' => $columnName . '::jsonb || ' . implode(' || ', $fieldsJSON)
        ]);
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

    /** @var int $userUpdatedUnixTimestamp Текущее время в UNIX-формате */
    $userUpdatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
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
      $databaseQuery->bindParam(':updatedUnixTimestamp', $userUpdatedUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }

  /**
   * Создать заявку на подтверждение регистрации
   * 
   * @return array
   */
  public function createRegistrationSubmit() : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('users_registration_submits');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('submitToken');
    $queryBuilder->statement->addColumn('refusalToken');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $requestTime = time();
    $submitToken = md5(sprintf('[%d]%d => submit', $this->id, $requestTime));
    $refusalToken = md5(sprintf('[%d]%d => refusal', $this->id, $requestTime));
    $createdUnixTimestamp = time();
    
    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $this->id, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':submitToken', $submitToken, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':refusalToken', $refusalToken, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    if ($execute) {
      return [
        'submitToken' => $submitToken,
        'refusalToken' => $refusalToken
      ];
    }

    return null;
  }

  /**
   * Получить ID пользователя-инициатора заявки подтверждения регистрации
   * по подтвержающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return int
   */
  public static function getUserIDByRegistrationSubmitToken(CMSCore $CMSCore, string $token) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['userID']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`submitToken` = :submitToken',
      'postgresql' => '"submitToken" = :submitToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':submitToken', $token, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    
    return $result ? (int) $result['userID'] : null;
  }

  /**
   * Получить ID пользователя-инициатора заявки подтверждения регистрации
   * по сбрасывающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return int
   */
  public static function getUserIDByRegistrationRefusalToken(CMSCore $CMSCore, string $token) : int
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['userID']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`refusalToken` = :refusalToken',
      'postgresql' => '"refusalToken" = :refusalToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':refusalToken', $token, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    
    return $result ? (int) $result['userID'] : null;
  }

  /**
   * Проверить наличие заявки на подтверждение регистрации по подтверждающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return bool
   */
  public static function existsByRegistrationSubmitToken(CMSCore $CMSCore, string $token) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`submitToken` = :submitToken',
      'postgresql' => '"submitToken" = :submitToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':submitToken', $token, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Проверить наличие заявки на подтверждение регистрации по сбрасывающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return bool
   */
  public static function existsByRegistrationRefusalToken(CMSCore $CMSCore, string $token) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`refusalToken` = :refusalToken',
      'postgresql' => '"refusalToken" = :refusalToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':refusalToken', $token, \PDO::PARAM_STR);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Удалить заявку на подтверждение регистрации по сбрасывающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return bool
   */
  public static function deleteRegistrationSubmitByRefusalToken(CMSCore $CMSCore, string $token) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`refusalToken` = :refusalToken',
      'postgresql' => '"refusalToken" = :refusalToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':refusalToken', $token, \PDO::PARAM_STR);
    $execute = $databaseQuery->execute();

    return $execute ? true : false;
  }

  /**
   * Удалить заявку на подтверждение регистрации по подтверждающему токену
   * 
   * @param CMSCore $CMSCore
   * @param string $token
   * 
   * @return bool
   */
  public static function deleteRegistrationSubmitBySubmitToken(CMSCore $CMSCore, string $token) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_registration_submits');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`submitToken` = :submitToken',
      'postgresql' => '"submitToken" = :submitToken'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':submitToken', $token, \PDO::PARAM_STR);
      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $execute ? true : false;
  }

  /**
   * Проверить на валидацию E-Mail
   * 
   * @param CMSCore $CMSCore
   * @param string $email
   * 
   * @return bool
   */
  public static function emailIsValid(CMSCore $CMSCore, string $email) : bool
  {
    return preg_match('/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i', $email);
  }

  /**
   * Проверить на валидацию логин
   * 
   * @param CMSCore $CMSCore
   * @param string $login
   * 
   * @return bool
   */
  public static function loginIsValid(CMSCore $CMSCore, string $login) : bool
  {
    return preg_match('/^[\w\-\.\_]{1,36}$/i', $login);
  }

  /**
   * Проверить на валидацию пароль
   * 
   * @param CMSCore $CMSCore
   * @param string $password
   * 
   * @return bool
   * 
   */
  public static function passwordIsValid(CMSCore $CMSCore, string $password) : bool
  {
    return preg_match('/^[a-zA-Z0-9\@\#\$\%\&\(\)\?\!]{1,36}$/', $password);
  }
}
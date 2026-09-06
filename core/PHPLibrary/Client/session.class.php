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

namespace core\PHPLibrary\Client;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\SystemCore\Report as Report;

#[\AllowDynamicProperties]
class Session
{
  private readonly CoreInterface $CMSCore;
  private int $id;

  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * @param  mixed $id
   * @return void
   */
  public function __construct(CoreInterface $CMSCore, int $id)
  {
    $this->CMSCore = $CMSCore;
    $this->setID($id);
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
    foreach ($columnsData as $name => $data) {
      $this->{$name} = $data;
    }
  }

  /**
   * Назначить идентификатор записи
   *
   * @param  mixed $value
   * 
   * @return void
   */
  private function setID(int $value) : void
  {
    $this->id = $value;
  }

  /**
   * Получить идентификатор записи
   *
   * @param  mixed $value
   * 
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить идентификатор пользователя, к которому привязана сессия
   *
   * @param  mixed $value
   * 
   * @return int
   */
  public function getUserID() : int
  {
    return $this->userID;
  }

  /**
   * Получить объект пользователя, к которому привязана сессия
   *
   * @return User|null
   */
  public function getUser() : User|null {
    if (!property_exists($this, 'createdUnixTimestamp'))
    {
      $this->initData(['userID']);
    }

    return User::existsByID($this->CMSCore, $this->userID) ? new User($this->CMSCore, $this->userID) : null;
  }

  /**
   * Получить временную отметку создания в UNIX-формате
   * 
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить временную отметку обновления в UNIX-формате
   * 
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Получить токен
   * 
   * @return string
   */
  public function getToken() : string
  {
    return $this->token ?? '';
  }

  /**
   * Проверить жизнеспособность
   * 
   * @param int $expire
   * 
   * @return bool
   */
  public function isAlive(int $expire) : bool
  {
    if (property_exists($this, 'updatedUnixTimestamp')) {
      return $this->getUpdatedUnixTimestamp() + $expire > time();
    }

    return false;
  }

  /**
   * Сбросить жизнеспособность
   * 
   * @return bool
   */
  public function resetExpire() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('users_sessions');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int $sessionID Идентификационный номер записи */
    $sessionID = $this->getID();
    $updatedUnixTimestamp = time();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':id', $sessionID, \PDO::PARAM_INT);
    $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
    $execute = $databaseQuery->execute();

    return $execute ? true : false;
  }

  /**
   * Получить данные колонок записи в базе данных
   *
   * @param  array $columns
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
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int $sessionID Идентификационный номер записи */
    $sessionID = $this->getID();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':id', $sessionID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result : null;
  }

  /**
   * Сгенерировать и получить токен
   * 
   * @return string
   */
  public static function generateToken(int $bytes = 64) : string
  {
    return bin2hex(random_bytes($bytes));
  }

  /**
   * Получить при помощи IP-адреса
   * 
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param int $typeID
   * 
   * @return Session|null
   */
  public static function getByIP(CoreInterface $CMSCore, string $userIP, int $typeID) : Session|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "typeID" = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Session($CMSCore, $result['id']) : null;
  }

  /**
   * Получить при помощи IP-адреса и ID пользователя
   * 
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param int $userID
   * @param int $typeID
   * 
   * @return Session|null
   */
  public static function getByIPAndUserID(CoreInterface $CMSCore, string $userIP, int $userID, int $typeID) : Session|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `userID` = :userID AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "userID" = :userID AND "typeID" = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Session($CMSCore, $result['id']) : null;
  }

  /**
   * Получить при помощи IP-адреса и токена
   * 
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param string $token
   * @param int $typeID
   * 
   * @return Session|null
   */
  public static function getByIPAndToken(CoreInterface $CMSCore, string $userIP, string $token, int $typeID) : Session|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `token` = :token AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "token" = :token AND "typeID" = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':token', $token, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? new Session($CMSCore, $result['id']) : null;
  }

  /**
   * Проверить существование сессии по IP-адресу и ID пользователя
   *
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param int $userID
   * @param int $typeID
   * 
   * @return bool
   */
  public static function existsByIPAndUserID(CoreInterface $CMSCore, string $userIP, int $userID, int $typeID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `userID` = :userID AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "userID" = :userID AND "typeID" = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':userID', $userID, \PDO::PARAM_INT);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Проверить существование сессии по IP-адресу и токену
   *
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param string $token
   * @param int $typeID
   * 
   * @return bool
   */
  public static function existsByIPAndToken(CoreInterface $CMSCore, string $userIP, string $token, int $typeID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();

    $queryBuilderStatement = $queryBuilder->statement;
    $queryBuilderStatement->addSelections(['1']);
    $queryBuilderStatement->setClauseFrom();

    $queryBuilderStatementClauseFrom = $queryBuilderStatement->clauseFrom;
    $queryBuilderStatementClauseFrom->addTable('users_sessions');
    $queryBuilderStatementClauseFrom->assembly();
    $queryBuilderStatement->setClauseWhere();

    $queryBuilderStatementClauseWhere = $queryBuilderStatement->clauseWhere;
    $queryBuilderStatementClauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `token` = :token AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "token" = :token AND "typeID" = :typeID'
    ]);
    $queryBuilderStatementClauseWhere->assembly();
    $queryBuilderStatement->setClauseLimit(1);
    $queryBuilderStatement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilderStatement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':token', $token, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Проверить существование сессии по IP-адресу
   *
   * @param CoreInterface $CMSCore
   * @param string $userIP
   * @param int $typeID
   * 
   * @return bool
   */
  public static function existsByIP(CoreInterface $CMSCore, string $userIP, int $typeID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`userIP` = :userIP AND `typeID` = :typeID',
      'postgresql' => '"userIP" = :userIP AND "typeID" = :typeID'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    $databaseConnection = $CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
    $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
    $databaseQuery->execute();

    return $databaseQuery->fetchColumn() ? true : false;
  }

  /**
   * Обновить
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
    $queryBuilder->statement->setTable('users_sessions');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
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

    /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
    $updatedUnixTimestamp = time();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
        $valueTypeName = gettype($value);

        $valueType = match ($valueTypeName) {
          'boolean' => \PDO::PARAM_INT,
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

    return $execute ? true : false;
  }

  /**
   * Создать
   *
   * @param  CoreInterface $CMSCore
   * @param  array $data (userID, token, userIP, typeID)
   * 
   * @return Session
   */
  public static function create(CoreInterface $CMSCore, array $data = []) : Session|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('users_sessions');
    $queryBuilder->statement->addColumn('userID');
    $queryBuilder->statement->addColumn('token');
    $queryBuilder->statement->addColumn('userIP');
    $queryBuilder->statement->addColumn('typeID');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    $createdUnixTimestamp = time();
    $updatedUnixTimestamp = $createdUnixTimestamp;

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $data['userID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':token', $data['token'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userIP', $data['userIP'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':typeID', $data['typeID'], \PDO::PARAM_INT);
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
      $queryBuilder->statement->clauseFrom->addTable('users_sessions');
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
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);

      if ($result) {
        $user = new User($CMSCore, (int) $data['userID']);
        $user->initData(['login']);

        $reportType = $data['typeID'] === 2
          ? Report::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
          : Report::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS;

        Report::create(
          $CMSCore,
          $reportType,
          [
            'userID' => $user->getID(),
            'userLogin' => $user->getLogin(),
            'ip' => $data['userIP'],
            'typeID' => $data['typeID']
          ]
        );
      }

      return $result ? new Session($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Удалить
   * 
   * @return bool
   */
  public function delete() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $user = $this->getUser();
    $userLogin = $user !== null ? $user->getLogin() : 'unknown';
    $userID = $user !== null ? $user->getID() : 0;
    $typeID = $this->typeID ?? 1;

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_sessions');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
    $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
    $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
    $execute = $databaseQuery->execute();

    if ($execute && $user !== null) {
      $reportType = $typeID === 2
        ? Report::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL
        : Report::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL;

      Report::create(
        $this->CMSCore,
        $reportType,
        [
          'userID' => $userID,
          'userLogin' => $userLogin,
          'ip' => $this->userIP ?? '0.0.0.0',
          'typeID' => $typeID,
          'action' => 'logout'
        ]
      );
    }

    return $execute ? true : false;
  }
}
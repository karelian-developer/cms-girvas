<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Client {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;

  #[\AllowDynamicProperties]
  class Session {
    private readonly SystemCore $CMSCore;
    private int $id;
    
    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @param  mixed $id
     * @return void
     */
    public function __construct(SystemCore $CMSCore, int $id) {
      $this->CMSCore = $CMSCore;
      $this->set_id($id);
    }

    public function init_data(array $columns = ['*']) {
      $columnsData = $this->get_database_columns_data($columns);
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
    }

    /**
     * Назначить идентификатор записи
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить идентификатор записи
     *
     * @param  mixed $value
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }
    
    /**
     * Получить идентификатор пользователя, к которому привязана сессия
     *
     * @param  mixed $value
     * @return int
     */
    public function get_userID() : int {
      return $this->userID;
    }
    
    /**
     * Получить объект пользователя, к которому привязана сессия
     *
     * @return User|null
     */
    public function get_user() : User|null {
      if (!property_exists($this, 'createdUnixTimestamp')) {
        $this->init_data(['userID']);
      }

      return User::exists_by_id($this->CMSCore, $this->userID) ? new User($this->CMSCore, $this->userID) : null;
    }

    public function get_created_unix_timestamp() : int {
      return property_exists($this, 'createdUnixTimestamp') ? $this->createdUnixTimestamp : 0;
    }

    public function get_updated_unix_timestamp() : int {
      return property_exists($this, 'updatedUnixTimestamp') ? $this->updatedUnixTimestamp : 0;
    }

    public function get_token() : string {
      return property_exists($this, 'token') ? $this->token : '';
    }

    public function is_alive(int $expire) : bool {
      if (property_exists($this, 'updatedUnixTimestamp')) {
        return $this->get_updated_unix_timestamp() + $expire > time() ? true : false;
      }

      return false;
    }

    public function reset_expire() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('users_sessions');
      $queryBuilder->statement->set_clause_set();
      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('id = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $sessionID Идентификационный номер записи */
      $sessionID = $this->get_id();
      $updatedUnixTimestamp = time();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $sessionID, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
			$execute = $databaseQuery->execute();

      return ($execute) ? true : false;
    }

    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('id = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $sessionID Идентификационный номер записи */
      $sessionID = $this->get_id();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $sessionID, \PDO::PARAM_INT);
			$databaseQuery->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? $result : null;
    }

    public static function generate_token(int $bytes = 64) : string {
      return bin2hex(random_bytes($bytes));
    }

    public static function get_by_ip(SystemCore $CMSCore, string $userIP, int $typeID) : Session|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
			$databaseQuery->execute();

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new Session($CMSCore, $result['id']) : null;
    }

    public static function get_by_ip_and_userID(SystemCore $CMSCore, string $userIP, int $userID, int $typeID) : Session|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND userID = :userID AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

    public static function get_by_ip_and_token(SystemCore $CMSCore, string $userIP, string $token, int $typeID) : Session|null{
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND token = :token AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
     * Проверка существования сессии по IP-адресу и ID пользователя
     *
     * @param  mixed $userIP
     * @return void
     */
    public static function exists_by_ip_and_userID(SystemCore $CMSCore, string $userIP, int $userID, int $typeID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND userID = :userID AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
     * Проверка существования сессии по IP-адресу и токену
     *
     * @param  mixed $userIP
     * @return void
     */
    public static function exists_by_ip_and_token(SystemCore $CMSCore, string $userIP, string $token, int $typeID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND token = :token AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':token', $token, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
			$databaseQuery->execute();

      return $databaseQuery->fetchColumn() ? true : false;
    }
    
    /**
     * Проверка существования сессии по IP-адресу
     *
     * @param  mixed $userIP
     * @return void
     */
    public static function exists_by_ip(SystemCore $CMSCore, string $userIP, int $typeID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('userIP = :userIP AND typeID = :typeID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userIP', $userIP, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':typeID', $typeID, \PDO::PARAM_INT);
			$databaseQuery->execute();

      return $databaseQuery->fetchColumn() ? true : false;
    }

    public function update(array $data) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('users_sessions');
      $queryBuilder->statement->set_clause_set();

      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('id = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
      $updatedUnixTimestamp = time();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp'])) {
          $valueTypeName = gettype($value);

          $valueType = match () {
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
     * Создание записи нового пользователя в базе данных
     *
     * @param  SystemCore $CMSCore
     * @param  array $data (userID, token, userIP, typeID)
     * @return Session
     */
    public static function create(SystemCore $CMSCore, array $data = []) : Session|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('users_sessions');
      $queryBuilder->statement->add_column('userID');
      $queryBuilder->statement->add_column('token');
      $queryBuilder->statement->add_column('userIP');
      $queryBuilder->statement->add_column('typeID');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clause_returning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;
      
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':userID', $data['userID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':token', $data['token'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':userIP', $data['userIP'], \PDO::PARAM_STR);
      $databaseQuery->bindParam(':typeID', $data['typeID'], \PDO::PARAM_INT);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
      $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
			$execute = $databaseQuery->execute();

      if ($execute) {
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return $result ? new Session($CMSCore, $result['id']) : null;
      }

      return null;
    }
    
    /**
     * Удаление сессии
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_sessions');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('id = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
			$execute = $databaseQuery->execute();

      return $execute ? true : false;
    }
  }
}

?>
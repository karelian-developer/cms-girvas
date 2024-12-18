<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Client {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;

  #[\AllowDynamicProperties]
  class Session {
    private readonly SystemCore $system_core;
    private int $id;
    
    /**
     * __construct
     *
     * @param  mixed $system_core
     * @param  mixed $id
     * @return void
     */
    public function __construct(SystemCore $system_core, int $id) {
      $this->system_core = $system_core;
      $this->set_id($id);
    }

    public function init_data(array $columns = ['*']) {
      $columns_data = $this->get_database_columns_data($columns);
      foreach ($columns_data as $column_name => $column_data) {
        $this->{$column_name} = $column_data;
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
    public function get_user_id() : int {
      return $this->user_id;
    }
    
    /**
     * Получить объект пользователя, к которому привязана сессия
     *
     * @return User|null
     */
    public function get_user() : User|null {
      if (!property_exists($this, 'created_unix_timestamp')) {
        $this->init_data(['user_id']);
      }

      return (User::exists_by_id($this->system_core, $this->user_id)) ? new User($this->system_core, $this->user_id) : null;
    }

    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : 0;
    }

    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : 0;
    }

    public function get_token() : string {
      return (property_exists($this, 'token')) ? $this->token : '';
    }

    public function is_alive(int $expire) : bool {
      if (property_exists($this, 'updated_unix_timestamp')) {
        return ($this->get_updated_unix_timestamp() + $expire > time()) ? true : false;
      }

      return false;
    }

    public function reset_expire() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('users_sessions');
      $query_builder->statement->set_clause_set();
      $query_builder->statement->clause_set->add_column('updated_unix_timestamp');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      /** @var int $session_id Идентификационный номер записи */
      $session_id = $this->get_id();
      $session_updated_unix_timestamp = time();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $session_id, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $session_updated_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }

    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $session_id Идентификационный номер записи */
      $session_id = $this->get_id();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $session_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }

    public static function generate_token(int $bytes = 64) {
      return bin2hex(random_bytes($bytes));
    }

    public static function get_by_ip(SystemCore $system_core, string $user_ip, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? new Session($system_core, $result['id']) : null;
    }

    public static function get_by_ip_and_user_id(SystemCore $system_core, string $user_ip, int $user_id, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND user_id = :user_id AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? new Session($system_core, $result['id']) : null;
    }

    public static function get_by_ip_and_token(SystemCore $system_core, string $user_ip, string $token, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND token = :token AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':token', $token, \PDO::PARAM_STR);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? new Session($system_core, $result['id']) : null;
    }

    /**
     * Проверка существования сессии по IP-адресу и ID пользователя
     *
     * @param  mixed $user_ip
     * @return void
     */
    public static function exists_by_ip_and_user_id(SystemCore $system_core, string $user_ip, int $user_id, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND user_id = :user_id AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Проверка существования сессии по IP-адресу и токену
     *
     * @param  mixed $user_ip
     * @return void
     */
    public static function exists_by_ip_and_token(SystemCore $system_core, string $user_ip, string $token, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND token = :token AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':token', $token, \PDO::PARAM_STR);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }
    
    /**
     * Проверка существования сессии по IP-адресу
     *
     * @param  mixed $user_ip
     * @return void
     */
    public static function exists_by_ip(SystemCore $system_core, string $user_ip, int $type_id) {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('user_ip = :user_ip AND type_id = :type_id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_ip', $user_ip, \PDO::PARAM_STR);
      $database_query->bindParam(':type_id', $type_id, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('users_sessions');
      $query_builder->statement->set_clause_set();

      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp'])) {
          $query_builder->statement->clause_set->add_column($data_name);
        }
      }

      $query_builder->statement->clause_set->add_column('updated_unix_timestamp');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      /** @var int $user_updated_unix_timestamp Текущее время в UNIX-формате */
      $user_updated_unix_timestamp = time();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      
      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp'])) {
          switch (gettype($data_value)) {
            case 'boolean': $data_value_type = \PDO::PARAM_INT; break;
            case 'integer': $data_value_type = \PDO::PARAM_INT; break;
            case 'string': $data_value_type = \PDO::PARAM_STR; break;
            case 'null': $data_value_type = \PDO::PARAM_NULL; break;
          }
          
          $database_query->bindParam(':' . $data_name, $data[$data_name], $data_value_type);
        }
      }
      
      $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $user_updated_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }
    
    /**
     * Создание записи нового пользователя в базе данных
     *
     * @param  SystemCore $system_core
     * @param  array $session_data (user_id, token, user_ip, type_id)
     * @return Session
     */
    public static function create(SystemCore $system_core, array $session_data = []) : Session|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('users_sessions');
      $query_builder->statement->add_column('user_id');
      $query_builder->statement->add_column('token');
      $query_builder->statement->add_column('user_ip');
      $query_builder->statement->add_column('type_id');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $session_created_unix_timestamp = time();
      $session_updated_unix_timestamp = $session_created_unix_timestamp;
      
      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_id', $session_data['user_id'], \PDO::PARAM_INT);
      $database_query->bindParam(':token', $session_data['token'], \PDO::PARAM_STR);
      $database_query->bindParam(':user_ip', $session_data['user_ip'], \PDO::PARAM_STR);
      $database_query->bindParam(':type_id', $session_data['type_id'], \PDO::PARAM_INT);
      $database_query->bindParam(':created_unix_timestamp', $session_created_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $session_updated_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      if ($execute) {
        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new Session($system_core, $result['id']) : null;
      }

      return null;
    }
    
    /**
     * Удаление сессии
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_sessions');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }
  }
}

?>
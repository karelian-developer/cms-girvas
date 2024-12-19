<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;

  #[\AllowDynamicProperties]
  /**
   * Class User
   * @package core\PHPLibrary
   * 
   * @property-read SystemCore $system_core Класс системного ядра CMS
   * @property int $id ID пользователя
   */
  class User {
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

    /**
     * Инициализировать данные
     * 
     * @param array $columns
     * 
     * @return void
     */
    public function init_data(array $columns = ['*']) : void {
      $columns_data = $this->get_database_columns_data($columns);
      foreach ($columns_data as $column_name => $column_data) {
        $this->{$column_name} = $column_data;
      }
    }

    /**
     * Назначить ID
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить ID
     *
     * @param  mixed $value
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }

    /**
     * Получить логин
     * 
     * @return string
     */
    public function get_login() : string {
      return (property_exists($this, 'login')) ? $this->login : '';
    }

    /**
     * Получить E-Mail
     * 
     * @return string
     */
    public function get_email() : string {
      return (property_exists($this, 'email')) ? $this->email : '';
    }

    /**
     * Получить хеш пароля
     * 
     * @return string
     */
    public function get_password_hash() : string {
      return (property_exists($this, 'password_hash')) ? $this->password_hash : '';
    }

    /**
     * Получить хеш-ключ
     * 
     * @return string
     */
    public function get_security_hash() : string {
      return (property_exists($this, 'security_hash')) ? $this->security_hash : '';
    }

    /**
     * Получить временную отментку создания данных в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : 0;
    }

    /**
     * Получить временную отментку обновления данных в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : 0;
    }

    /**
     * Получить URL дефолтного аватара пользователя
     * 
     * @param SystemCore $system_core
     * @param int $size
     * 
     * @return string
     */
    public static function get_avatar_default_url(SystemCore $system_core, int $size) : string {
      return sprintf('/%s/images/avatar_default_%d.png', $system_core->template->get_url(), $size);
    }
    
    /**
     * Получить имя пользователя
     *
     * @return string
     */
    public function get_name() : string {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['name'])) {
          return $metadata_array['name'];
        }
      }

      return '';
    }
    
    /**
     * Получить фамилию пользователя
     *
     * @return string
     */
    public function get_surname() : string {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['surname'])) {
          return $metadata_array['surname'];
        }
      }

      return '';
    }
    
    /**
     * Получить отчество пользователя
     *
     * @return string
     */
    public function get_patronymic() : string {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['patronymic'])) {
          return $metadata_array['patronymic'];
        }
      }

      return '';
    }
    
    /**
     * Получить ID группы пользователя
     *
     * @return int
     */
    public function get_group_id() : int {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['group_id'])) {
          return $metadata_array['group_id'];
        }
      }

      return 0;
    }
    
    /**
     * Получить статус блокировки пользователя
     *
     * @return bool
     */
    public function is_blocked() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['isBlocked'])) {
          return (bool)$metadata_array['isBlocked'];
        }
      }

      return false;
    }
    
    /**
     * Получить объект группы пользователя
     *
     * @return UserGroup|null
     */
    public function get_group() : UserGroup|null {
      $group_id = $this->get_group_id();
      
      if (UserGroup::exists_by_id($this->system_core, $group_id)) {
        return new UserGroup($this->system_core, $group_id);
      }

      return null;
    }

    /**
     * Получить временную метку создания токена для сброса пароля
     *
     * @return string
     */
    public function get_password_reset_created_unix_timestamp() : int {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['passwordResetTokenCreatedUnixTimestamp'])) {
          return $metadata_array['passwordResetTokenCreatedUnixTimestamp'];
        }
      }

      return 0;
    }

    /**
     * Получить токен сброса пароля
     *
     * @return string
     */
    public function get_password_reset_token() : string {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['passwordResetToken'])) {
          return $metadata_array['passwordResetToken'];
        }
      }

      return '';
    }
    
    /**
     * Получить отчество пользователя
     *
     * @return int
     */
    public function get_birthdate_unix_timestamp() : int {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['birthdateUnixTimestamp'])) {
          return $metadata_array['birthdateUnixTimestamp'];
        }
      }

      return 0;
    }

    /**
     * Получить данные по дополнительному полю
     * 
     * @param string $field_name
     * 
     * @return mixed
     */
    public function get_additional_field_data(string $field_name) : mixed {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['additionalFields'])) {
          return (isset($metadata_array['additionalFields'][$field_name])) ? $metadata_array['additionalFields'][$field_name] : null;
        }
      }

      return null;
    }
    
    /**
     * Получить URL до аватарки пользователя
     *
     * @param  mixed $size
     * @return string
     */
    public function get_avatar_url(int $size) : string {
      $file_path = sprintf('%s/uploads/avatars/%d/%d.webp', CMS_ROOT_DIRECTORY, $this->id, $size);
      $file_url = sprintf('/uploads/avatars/%d/%d.webp', $this->id, $size);
      
      if (file_exists($file_path)) {
        return $file_url;
      }

      return self::get_avatar_default_url($this->system_core, $size);
    }

    /**
     * Хешировать строку
     * 
     * @param string $string
     * 
     * @return string
     */
    public function hashing(string $string) : string {
      $user_id = $this->get_id();
      $security_hash = $this->get_security_hash();
      $system_salt = $this->system_core->configurator->get('system_salt');
      $hash_source = sprintf('{GIRVAS:%s:%d+%s=>%s}', $security_hash, $user_id, $system_salt, $string);
      return md5($hash_source);
    }

    /**
     * Хешировать пароль
     * 
     * @param SystemCore $system_core
     * @param string $user_security_hash
     * @param string $password
     * 
     * @return string
     */
    public static function password_hash(SystemCore $system_core, string $user_security_hash, string $password) : string {
      $system_salt = $system_core->configurator->get('system_salt');
      $password_hashing_algorithm = $system_core->configurator->get('password_hashing_algorithm');
      $crypt_source = sprintf('{GIRVAS:%s+%s=>%s}', $user_security_hash, $system_salt, $password);
      return password_hash($crypt_source, $password_hashing_algorithm);
    }

    /**
     * Проверить пароль
     * 
     * @param string $password
     * 
     * @return bool
     */
    public function password_verify(string $password) : bool {
      $system_salt = $this->system_core->configurator->get('system_salt');
      $crypt_source = sprintf('{GIRVAS:%s+%s=>%s}', $this->get_security_hash(), $system_salt, $password);
      return password_verify($crypt_source, $this->get_password_hash());
    }

    /**
     * Сгенерировать хеш-ключ
     * 
     * @param SystemCore $system_core
     * 
     * @return string
     */
    public static function generate_security_hash(SystemCore $system_core) : string {
      $system_salt = $system_core->configurator->get('system_salt');
      return md5(sprintf('{GIRVAS:%s+%d}', $system_salt, time()));
    }

    /**
     * Получить данные с колонок в БД
     * 
     * @param array $columns
     * 
     * @return array
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $user_id Идентификационный номер записи */
      $user_id = $this->get_id();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $user_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }
    
    /**
     * Получить объекта пользователя по логину
     *
     * @param  mixed $system_core
     * @param  mixed $user_login
     * @return User
     */
    public static function get_by_login(SystemCore $system_core, string $user_login) : User|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('LOWER(login) = :login');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $user_login = strtolower($user_login);

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':login', $user_login, \PDO::PARAM_STR);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      
      return ($result) ? new User($system_core, (int)$result['id']) : null;
    }
    
    /**
     * Получить объекта пользователя по адресу электронной почты
     *
     * @param  mixed $system_core
     * @param  mixed $user_email
     * @return User
     */
    public static function get_by_email(SystemCore $system_core, string $user_email) : User|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('LOWER(email) = :email');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $user_email = strtolower($user_email);

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':email', $user_email, \PDO::PARAM_STR);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      
      return ($result) ? new User($system_core, (int)$result['id']) : null;
    }
    
    /**
     * Проверить существование пользователя по логину
     *
     * @param  mixed $system_core
     * @param  string $user_login
     * @return void
     */
    public static function exists_by_login(SystemCore $system_core, string $user_login) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('LOWER(login) = :login');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $user_login = strtolower($user_login);

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':login', $user_login, \PDO::PARAM_STR);
			$database_query->execute();
      
      return ($database_query->fetchColumn()) ? true : false;
    }
    
    /**
     * Проверить существование пользователя по E-Mail
     *
     * @param  mixed $system_core
     * @param  string $user_login
     * @return void
     */
    public static function exists_by_email(SystemCore $system_core, string $user_email) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('LOWER(email) = :email');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $user_email = strtolower($user_email);

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':email', $user_email, \PDO::PARAM_STR);
			$database_query->execute();
      
      return ($database_query->fetchColumn()) ? true : false;
    }
    
    /**
     * Проверить существование пользователя по ID
     *
     * @param  mixed $system_core
     * @param  int $user_id
     * @return void
     */
    public static function exists_by_id(SystemCore $system_core, int $user_id) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $user_id, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Удаление существующего пользователя
     *
     * @return bool
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users');
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

    /**
     * Создать пользователя
     * 
     * @param SystemCore $system_core
     * @param string $user_login
     * @param string $user_email
     * @param string $user_password
     * 
     * @return User
     */
    public static function create(SystemCore $system_core, string $user_login, string $user_email, string $user_password) : User|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('users');
      $query_builder->statement->add_column('login');
      $query_builder->statement->add_column('email');
      $query_builder->statement->add_column('password_hash');
      $query_builder->statement->add_column('security_hash');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->add_column('email_is_submitted');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $user_security_hash = self::generate_security_hash($system_core);
      $user_password_hash = self::password_hash($system_core, $user_security_hash, $user_password);
      $user_created_unix_timestamp = time();
      $user_updated_unix_timestamp = $user_created_unix_timestamp;

      $user_default_metadata = [
        'name' => '',
        'surname' => '',
        'patronymic' => '',
        'group_id' => 4,
        'passwordResetToken' => '',
        'passwordResetTokenCreatedUnixTimestamp' => '',
      ];

      $user_metadata_json = json_encode($user_default_metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $email_is_submitted = false;

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':login', $user_login, \PDO::PARAM_STR);
      $database_query->bindParam(':email', $user_email, \PDO::PARAM_STR);
      $database_query->bindParam(':password_hash', $user_password_hash, \PDO::PARAM_STR);
      $database_query->bindParam(':security_hash', $user_security_hash, \PDO::PARAM_STR);
      $database_query->bindParam(':created_unix_timestamp', $user_created_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $user_updated_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':metadata', $user_metadata_json, \PDO::PARAM_STR);
      $database_query->bindParam(':email_is_submitted', $email_is_submitted, \PDO::PARAM_BOOL);
			$execute = $database_query->execute();

      if ($execute) {
        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new User($system_core, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновить пользователя
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('users');
      $query_builder->statement->set_clause_set();

      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata'])) {
          $query_builder->statement->clause_set->add_column($data_name);
        }
      }
      
      if (array_key_exists('metadata', $data)) {
        $json_fields = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $query_builder->statement->clause_set->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $json_fields));
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
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata'])) {
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
     * Создать заявку на подтверждение регистрации
     * 
     * @return array
     */
    public function create_registration_submit() : array|null {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('users_registration_submits');
      $query_builder->statement->add_column('user_id');
      $query_builder->statement->add_column('submit_token');
      $query_builder->statement->add_column('refusal_token');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $request_time = time();
      $submit_token = md5(sprintf('[%d]%d => submit', $this->id, $request_time));
      $refusal_token = md5(sprintf('[%d]%d => refusal', $this->id, $request_time));
      $registration_submit_created_unix_timestamp = time();
      
      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':user_id', $this->id, \PDO::PARAM_INT);
      $database_query->bindParam(':submit_token', $submit_token, \PDO::PARAM_STR);
      $database_query->bindParam(':refusal_token', $refusal_token, \PDO::PARAM_STR);
      $database_query->bindParam(':created_unix_timestamp', $registration_submit_created_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      if ($execute) {
        return [
          'submit_token' => $submit_token,
          'refusal_token' => $refusal_token
        ];
      }

      return null;
    }

    /**
     * Получить ID пользователя-инициатора заявки подтверждения регистрации
     * по подтвержающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return int
     */
    public static function get_user_id_by_registration_submit_token(SystemCore $system_core, string $token) : int {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['user_id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('submit_token = :submit_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':submit_token', $token, \PDO::PARAM_STR);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      
      return ($result) ? (int)$result['user_id'] : null;
    }

    /**
     * Получить ID пользователя-инициатора заявки подтверждения регистрации
     * по сбрасывающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return int
     */
    public static function get_user_id_by_registration_refusal_token(SystemCore $system_core, string $token) : int {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['user_id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('refusal_token = :refusal_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':refusal_token', $token, \PDO::PARAM_STR);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      
      return ($result) ? (int)$result['user_id'] : null;
    }

    /**
     * Проверить наличие заявки на подтверждение регистрации по подтверждающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return bool
     */
    public static function exists_by_registration_submit_token(SystemCore $system_core, string $token) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('submit_token = :submit_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':submit_token', $token, \PDO::PARAM_STR);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Проверить наличие заявки на подтверждение регистрации по сбрасывающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return bool
     */
    public static function exists_by_registration_refusal_token(SystemCore $system_core, string $token) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('refusal_token = :refusal_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':refusal_token', $token, \PDO::PARAM_STR);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }

    /**
     * Удалить заявку на подтверждение регистрации по сбрасывающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return bool
     */
    public static function delete_registration_submit_by_refusal_token(SystemCore $system_core, string $token) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('refusal_token = :refusal_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':refusal_token', $token, \PDO::PARAM_STR);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }

    /**
     * Удалить заявку на подтверждение регистрации по подтверждающему токену
     * 
     * @param SystemCore $system_core
     * @param string $token
     * 
     * @return bool
     */
    public static function delete_registration_submit_by_submit_token(SystemCore $system_core, string $token) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('users_registration_submits');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('submit_token = :submit_token');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':submit_token', $token, \PDO::PARAM_STR);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }

    /**
     * Проверить на валидацию E-Mail
     * 
     * @param SystemCore $system_core
     * @param string $email
     * 
     * @return bool
     */
    public static function email_is_valid(SystemCore $system_core, string $email) : bool {
      return preg_match('/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i', $email);
    }

    /**
     * Проверить на валидацию логин
     * 
     * @param SystemCore $system_core
     * @param string $login
     * 
     * @return bool
     */
    public static function login_is_valid(SystemCore $system_core, string $login) : bool {
      return preg_match('/^[\w\-\.\_]{1,36}$/i', $login);
    }

    /**
     * Проверить на валидацию пароль
     * 
     * @param SystemCore $system_core
     * @param string $login
     * 
     * @return bool
     */
    public static function password_is_valid(SystemCore $system_core, string $password) : bool {
      return preg_match('/^[a-zA-Z0-9\@\#\$\%\&\(\)\?\!]{1,36}$/', $password);
    }
  }
}

?>
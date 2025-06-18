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
  use \PDOException as PDOException;

  #[\AllowDynamicProperties]
  /**
   * Class User
   * @package core\PHPLibrary
   * 
   * @property-read SystemCore $CMSCore Класс системного ядра CMS
   * @property int $id ID пользователя
   */
  class User {
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

    /**
     * Инициализировать данные
     * 
     * @param array $columns
     * 
     * @return void
     */
    public function init_data(array $columns = ['*']) : void {
      $columns = $this->get_database_columns_data($columns);
      foreach ($columns as $name => $data) {
        $this->{$name} = $data;
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
      return (property_exists($this, 'passwordHash')) ? $this->passwordHash : '';
    }

    /**
     * Получить хеш-ключ
     * 
     * @return string
     */
    public function get_security_hash() : string {
      return (property_exists($this, 'securityHash')) ? $this->securityHash : '';
    }

    /**
     * Получить временную отментку создания данных в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : 0;
    }

    /**
     * Получить временную отментку обновления данных в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : 0;
    }

    /**
     * Получить URL дефолтного аватара пользователя
     * 
     * @param SystemCore $CMSCore
     * @param int $size
     * 
     * @return string
     */
    public static function get_avatar_default_url(SystemCore $CMSCore, int $size) : string {
      return sprintf('/%s/images/avatar_default_%d.png', $CMSCore->theme->get_url(), $size);
    }
    
    /**
     * Получить имя пользователя
     *
     * @return string
     */
    public function get_name() : string {
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
    public function get_surname() : string {
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
    public function get_patronymic() : string {
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
    public function get_group_id() : int {
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
    public function is_blocked() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['isBlocked'])) {
          return (bool)$metadata['isBlocked'];
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
      $groupID = $this->get_group_id();
      
      if (UserGroup::exists_by_id($this->CMSCore, $groupID)) {
        return new UserGroup($this->CMSCore, $groupID);
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
    public function get_password_reset_token() : string {
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
    public function get_birthdate_unix_timestamp() : int {
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
     * @return mixed
     */
    public function get_additional_field_data(string $fieldName) : mixed {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['additionalFields'])) {
          return (isset($metadata['additionalFields'][$fieldName])) ? $metadata['additionalFields'][$fieldName] : null;
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
      $filePath = sprintf('%s/uploads/avatars/%d/%d.webp', CMS_ROOT_DIRECTORY, $this->id, $size);
      $fileURL = sprintf('/uploads/avatars/%d/%d.webp', $this->id, $size);
      
      if (file_exists($filePath)) {
        return $fileURL;
      }

      return self::get_avatar_default_url($this->CMSCore, $size);
    }

    /**
     * Хешировать строку
     * 
     * @param string $string
     * 
     * @return string
     */
    public function hashing(string $string) : string {
      $userID = $this->get_id();
      $securityHash = $this->get_security_hash();
      $CMSSalt = $this->CMSCore->configurator->get('salt');
      $hashSource = sprintf('{GIRVAS:%s:%d+%s=>%s}', $securityHash, $userID, $CMSSalt, $string);
      return md5($hashSource);
    }

    /**
     * Хешировать пароль
     * 
     * @param SystemCore $CMSCore
     * @param string $userSecurityHash
     * @param string $password
     * 
     * @return string
     */
    public static function password_hash(SystemCore $CMSCore, string $userSecurityHash, string $password) : string {
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
    public function password_verify(string $password) : bool {
      $CMSSalt = $this->CMSCore->configurator->get('salt');
      $cryptSource = sprintf('{GIRVAS:%s+%s=>%s}', $this->get_security_hash(), $CMSSalt, $password);
      return password_verify($cryptSource, $this->get_password_hash());
    }

    /**
     * Сгенерировать хеш-ключ
     * 
     * @param SystemCore $CMSCore
     * 
     * @return string
     */
    public static function generate_security_hash(SystemCore $CMSCore) : string {
      $CMSSalt = $CMSCore->configurator->get('salt');
      return md5(sprintf('{GIRVAS:%s+%d}', $CMSSalt, time()));
    }

    /**
     * Получить данные с колонок в БД
     * 
     * @param array $columns
     * 
     * @return array
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $userID Идентификационный номер записи */
      $userID = $this->get_id();

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
      return ($result) ? $result : null;
    }
    
    /**
     * Получить объекта пользователя по логину
     *
     * @param  mixed $CMSCore
     * @param  mixed $userLogin
     * @return User
     */
    public static function get_by_login(SystemCore $CMSCore, string $userLogin) : User|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('LOWER("login") = :login');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($result) ? new User($CMSCore, (int)$result['id']) : null;
    }
    
    /**
     * Получить объекта пользователя по адресу электронной почты
     *
     * @param  mixed $CMSCore
     * @param  mixed $userEmail
     * @return User
     */
    public static function get_by_email(SystemCore $CMSCore, string $userEmail) : User|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('LOWER("email") = :email');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($result) ? new User($CMSCore, (int)$result['id']) : null;
    }
    
    /**
     * Проверить существование пользователя по логину
     *
     * @param mixed $CMSCore
     * @param string $userLogin
     * @param bool $registerIsAccounting
     * 
     * @return void
     */
    public static function exists_by_login(SystemCore $CMSCore, string $userLogin, bool $registerIsAccounting = false) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();

      if (!$registerIsAccounting) {
        $queryBuilder->statement->clauseWhere->add_condition('LOWER("login") = :login');
        $userLogin = strtolower($userLogin);
      } else {
        $queryBuilder->statement->clauseWhere->add_condition('"login" = :login');
      }

      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($databaseQuery->fetchColumn()) ? true : false;
    }
    
    /**
     * Проверить существование пользователя по E-Mail
     *
     * @param  mixed $CMSCore
     * @param  string $userLogin
     * @return void
     */
    public static function exists_by_email(SystemCore $CMSCore, string $userEmail) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('LOWER("email") = :email');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($databaseQuery->fetchColumn()) ? true : false;
    }
    
    /**
     * Проверить существование пользователя по ID
     *
     * @param  mixed $CMSCore
     * @param  int $userID
     * @return void
     */
    public static function exists_by_id(SystemCore $CMSCore, int $userID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Удаление существующего пользователя
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
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

      return ($execute) ? true : false;
    }

    /**
     * Создать пользователя
     * 
     * @param SystemCore $CMSCore
     * @param string $userLogin
     * @param string $userEmail
     * @param string $userPassword
     * 
     * @return User
     */
    public static function create(SystemCore $CMSCore, string $userLogin, string $userEmail, string $userPassword) : User|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('users');
      $queryBuilder->statement->add_column('login');
      $queryBuilder->statement->add_column('email');
      $queryBuilder->statement->add_column('password_hash');
      $queryBuilder->statement->add_column('security_hash');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('email_is_submitted');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $userSecurityHash = self::generate_security_hash($CMSCore);
      $userPasswordHash = self::password_hash($CMSCore, $userSecurityHash, $userPassword);
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
        $databaseQuery->bindParam(':password_hash', $userPasswordHash, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':security_hash', $userSecurityHash, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':createdUnixTimestamp', $userCreatedUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updated_unix_timestamp', $userUpdatedUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':metadata', $userMetadataJSON, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':email_is_submitted', $userEmailIsSubmitted, \PDO::PARAM_BOOL);
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
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new User($CMSCore, $result['id']) : null;
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
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('users');
      $queryBuilder->statement->set_clause_set();

      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }
      
      if (array_key_exists('metadata', $data)) {
        $fieldsJSON = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($fieldsJSON, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $queryBuilder->statement->clauseSet->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $fieldsJSON));
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $userUpdatedUnixTimestamp Текущее время в UNIX-формате */
      $userUpdatedUnixTimestamp = time();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        
        foreach ($data as $name => $value) {
          if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
            switch (gettype($value)) {
              case 'boolean': $valueType = \PDO::PARAM_INT; break;
              case 'integer': $valueType = \PDO::PARAM_INT; break;
              case 'string': $valueType = \PDO::PARAM_STR; break;
              case 'null': $valueType = \PDO::PARAM_NULL; break;
            }
            
            $databaseQuery->bindParam(':' . $name, $data[$name], $valueType);
          }
        }
        
        $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updated_unix_timestamp', $userUpdatedUnixTimestamp, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($execute) ? true : false;
    }

    /**
     * Создать заявку на подтверждение регистрации
     * 
     * @return array
     */
    public function create_registration_submit() : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('users_registration_submits');
      $queryBuilder->statement->add_column('userID');
      $queryBuilder->statement->add_column('submitToken');
      $queryBuilder->statement->add_column('refusalToken');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
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
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return int
     */
    public static function get_user_id_by_registration_submit_token(SystemCore $CMSCore, string $token) : int {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['userID']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"submitToken" = :submitToken');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($result) ? (int)$result['userID'] : null;
    }

    /**
     * Получить ID пользователя-инициатора заявки подтверждения регистрации
     * по сбрасывающему токену
     * 
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return int
     */
    public static function get_user_id_by_registration_refusal_token(SystemCore $CMSCore, string $token) : int {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['userID']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"refusalToken" = :refusalToken');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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
      
      return ($result) ? (int)$result['userID'] : null;
    }

    /**
     * Проверить наличие заявки на подтверждение регистрации по подтверждающему токену
     * 
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return bool
     */
    public static function exists_by_registration_submit_token(SystemCore $CMSCore, string $token) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"submitToken" = :submitToken');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Проверить наличие заявки на подтверждение регистрации по сбрасывающему токену
     * 
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return bool
     */
    public static function exists_by_registration_refusal_token(SystemCore $CMSCore, string $token) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"refusalToken" = :refusalToken');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
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

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Удалить заявку на подтверждение регистрации по сбрасывающему токену
     * 
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return bool
     */
    public static function delete_registration_submit_by_refusal_token(SystemCore $CMSCore, string $token) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"refusalToken" = :refusalToken');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':refusalToken', $token, \PDO::PARAM_STR);
			$execute = $databaseQuery->execute();

      return ($execute) ? true : false;
    }

    /**
     * Удалить заявку на подтверждение регистрации по подтверждающему токену
     * 
     * @param SystemCore $CMSCore
     * @param string $token
     * 
     * @return bool
     */
    public static function delete_registration_submit_by_submit_token(SystemCore $CMSCore, string $token) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_registration_submits');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"submitToken" = :submitToken');
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

      return ($execute) ? true : false;
    }

    /**
     * Проверить на валидацию E-Mail
     * 
     * @param SystemCore $CMSCore
     * @param string $email
     * 
     * @return bool
     */
    public static function email_is_valid(SystemCore $CMSCore, string $email) : bool {
      return preg_match('/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i', $email);
    }

    /**
     * Проверить на валидацию логин
     * 
     * @param SystemCore $CMSCore
     * @param string $login
     * 
     * @return bool
     */
    public static function login_is_valid(SystemCore $CMSCore, string $login) : bool {
      return preg_match('/^[\w\-\.\_]{1,36}$/i', $login);
    }

    /**
     * Проверить на валидацию пароль
     * 
     * @param SystemCore $CMSCore
     * @param string $password
     * 
     * @return bool
     * 
     */
    public static function password_is_valid(SystemCore $CMSCore, string $password) : bool {
      return preg_match('/^[a-zA-Z0-9\@\#\$\%\&\(\)\?\!]{1,36}$/', $password);
    }
  }
}

?>
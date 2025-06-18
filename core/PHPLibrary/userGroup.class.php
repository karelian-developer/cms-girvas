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
  class UserGroup {
    // Административные права
    public const PERMISSION_ADMIN_PANEL_AUTH                    = 1 << 0;
    public const PERMISSION_ADMIN_USERS_MANAGEMENT              = 1 << 1;
    public const PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT       = 1 << 2;
    public const PERMISSION_ADMIN_MODULES_MANAGEMENT            = 1 << 3;
    public const PERMISSION_ADMIN_TEMPLATES_MANAGEMENT          = 1 << 4;
    public const PERMISSION_ADMIN_SETTINGS_MANAGEMENT           = 1 << 5;
    public const PERMISSION_ADMIN_VIEWING_LOGS                  = 1 << 6;
    public const PERMISSION_ADMIN_FEEDS_MANAGEMENT              = 1 << 17;
    public const PERMISSION_ADMIN_SUPERUSER                     = 1 << 18;
    // Права модерации
    public const PERMISSION_MODER_USERS_BAN                     = 1 << 7;
    public const PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT   = 1 << 8;
    public const PERMISSION_MODER_USERS_WARNS                   = 1 << 9;
    // Права редакции
    public const PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT       = 1 << 10;
    public const PERMISSION_EDITOR_ENTRIES_EDIT                 = 1 << 11;
    public const PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT      = 1 << 12;
    public const PERMISSION_EDITOR_PAGES_STATIC_EDIT            = 1 << 13;

    public const PERMISSION_BASE_ENTRY_COMMENT_CREATE           = 1 << 14;
    public const PERMISSION_BASE_ENTRY_COMMENT_CHANGE           = 1 << 15;
    public const PERMISSION_BASE_ENTRY_COMMENT_RATE             = 1 << 16;

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
      $this->CMSCore= $CMSCore;
      $this->set_id($id);
    }

    public function init_data(array $columns = ['*']) : void {
      $columnsData = $this->get_database_columns_data($columns);
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
    }

    /**
     * Назначить идентификатор
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить идентификатор
     * 
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }

    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }

    public function get_permissions() : int {
      return (property_exists($this, 'permissions')) ? $this->permissions : 0;
    }

    public function get_users() : array {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('(metadata->>\'groupID\')::int = :groupID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':groupID', $this->id, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $users = [];

      if ($execute) {
        $result = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $userData) {
          array_push($users, new User($this->CMSCore, $userData['id']));
        }
      }

      return $users;
    }

    public function get_users_count() : int {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['count(*)']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('(metadata->>\'groupID\')::int = :groupID');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':groupID', $this->id, \PDO::PARAM_INT);
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
        return $result['count'];
      }

      return 0;
    }

    public function get_created_unix_timestamp() : int|string {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : '{ERROR:USER_GROUP_DATA_IS_NOT_EXISTS=createdUnixTimestamp}';
    }

    public function get_updated_unix_timestamp() : int|string {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : '{ERROR:USER_GROUP_IS_NOT_EXISTS=updatedUnixTimestamp}';
    }

    public function permission_check(int $permission) : bool {
      $permissions = $this->get_permissions();
      
      if (($permissions & $permission) == $permission) {
        return true;
      }

      return false;
    }
    
    /**
     * Получить заголовок
     *
     * @return string
     */
    public function get_title($localeName = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts_array = json_decode($this->texts, true);
        if (isset($texts_array[$localeName]['title'])) {
          return $texts_array[$localeName]['title'];
        }
      }

      return '';
    }

    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_groups');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $userGroupID Идентификационный номер записи */
      $userGroupID = $this->get_id();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $userGroupID, \PDO::PARAM_INT);
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
     * Получить объект группы пользователя по наименованию
     *
     * @param  mixed $CMSCore
     * @param  mixed $groupName
     * @return UserGroup
     */
    public static function get_by_name(SystemCore $CMSCore, string $groupName) : UserGroup|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_groups');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('LOWER("name") = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $groupName = strtolower($groupName);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $groupName, \PDO::PARAM_STR);
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
      
      return ($result) ? new UserGroup($CMSCore, (int)$result['id']) : null;
    }
    
    /**
     * Проверить существование группы пользователей по наименованию
     *
     * @param  mixed $CMSCore
     * @param  string $groupName
     * @return void
     */
    public static function exists_by_name(\core\PHPLibrary\SystemCore $CMSCore, string $groupName) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_groups');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('LOWER("name") = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      $groupName = strtolower($groupName);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $groupName, \PDO::PARAM_STR);
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
     * Проверить существование группы пользователей по ID
     *
     * @param  mixed $CMSCore
     * @param  int $group_id
     * @return void
     */
    public static function exists_by_id(\core\PHPLibrary\SystemCore $CMSCore, int $group_id) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_groups');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $group_id, \PDO::PARAM_INT);
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
     * Удаление существующей группы пользователей
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('users_groups');
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

    public static function create(SystemCore $CMSCore, string $groupName, array $texts = [], int $permissions = 0x0000000000000000) : UserGroup|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('users_groups');
      $queryBuilder->statement->add_column('name');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->add_column('permissions');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('texts');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;
      $metadata = json_encode([], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $texts = json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $groupName, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':permissions', $permissions, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':texts', $texts, \PDO::PARAM_STR);
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
        return ($result) ? new UserGroup($CMSCore, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующего пользователя
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('users_groups');
      $queryBuilder->statement->set_clause_set();

      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      if (array_key_exists('texts', $data)) {
        $jsonFields = [];

        foreach ($data['texts'] as $name => $value) {
          array_push($jsonFields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['texts'])) {
          $queryBuilder->statement->clauseSet->add_column('texts', 'texts::jsonb || ' . implode(' || ', $jsonFields));
        }
      }

      if (array_key_exists('metadata', $data)) {
        $jsonFields = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($jsonFields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $queryBuilder->statement->clauseSet->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $jsonFields));
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $user_updated_unix_timestamp Текущее время в UNIX-формате */
      $userGroupUpdatedUnixTimestamp = time();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        
        foreach ($data as $name => $value) {
          if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
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
        $databaseQuery->bindParam(':updatedUnixTimestamp', $userGroupUpdatedUnixTimestamp, \PDO::PARAM_INT);
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
  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\DatabaseManagementSystem as EnumDatabaseManagementSystem;
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \PDO as PDO;
  use \PDOException as PDOException;

  /**
   * База данных
   */
  final class Database {
    public const SQL_LIBRARY_PATH = 'core/SQLLibrary';

    private string $database_name;
    private string $database_user;
    private string $database_password;
    private string $database_host;
    private EnumDatabaseManagementSystem $database_management_system;
    private DatabaseQueryBuilder $query_builder;
    public PDO|null $connection = null;


    /**
     * __construct
     *
     * @return void
     */
    public function __construct(EnumDatabaseManagementSystem $database_management_system) {
      $this->set_database_management_system($database_management_system);
      //$this->query_builder = new DatabaseQueryBuilder();
    }
    
    /**
     * Назначение наименования расширения SQL для работы с базой данных
     *
     * @param  mixed $system
     * @return void
     */
    private function set_database_management_system(EnumDatabaseManagementSystem $system) {
      $this->database_management_system = $system;
    }
    
    /**
     * Получение наименования расширения SQL для работы с базой данных
     *
     * @return EnumDatabaseManagementSystem
     */
    private function get_database_management_system() : EnumDatabaseManagementSystem {
      return $this->database_management_system;
    }
    
    /**
     * Назначить имя базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_name(string $value) : void {
      $this->database_name = $value;
    }
    
    /**
     * Назначить имя пользователя базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_user(string $value) : void {
      $this->database_user = $value;
    }
    
    /**
     * Назначить пароль доступа к базе данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_password(string $value) : void {
      $this->database_password = $value;
    }
    
    /**
     * Назначить хост базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_host(string $value) : void {
      $this->database_host = $value;
    }
    
    /**
     * Получить имя базы данных
     *
     * @return string
     */
    private function get_database_name() : string {
      return $this->database_name;
    }
    
    /**
     * Получить имя пользователя базы данных
     *
     * @return string
     */
    private function get_database_user() : string {
      return $this->database_user;
    }
    
    /**
     * Получить пароль базы данных
     *
     * @return string
     */
    private function get_database_password() : string {
      return $this->database_password;
    }
    
    /**
     * Получить хост базы данных
     *
     * @return string
     */
    private function get_database_host() : string {
      return $this->database_host;
    }

    /**
     * Подключиться к базе данных
     * 
     * @param bool $error_is_json
     * 
     * @return [type]
     */
    public function connect(bool $error_is_json = false) {
      /** @var string $database_name Наименование базы данных */
      $database_name = $this->get_database_name();
      /** @var string $database_user Пользователь базы данных */
      $database_user = $this->get_database_user();
      /** @var string $database_password Пароль базы данных */
      $database_password = $this->get_database_password();
      /** @var string $database_host Хост базы данных */
      $database_host = $this->get_database_host();
      
      /** @var EnumDatabaseManagementSystem $database_management_system */
      $database_management_system = $this->get_database_management_system();
      switch ($database_management_system->value) {
        case 'mysql': $database_connection_query = 'mysql:host=%s;dbname=%s'; break;
        case 'pgsql': $database_connection_query = 'pgsql:host=%s;dbname=%s'; break;
      }

      $database_connection_query_modified = sprintf($database_connection_query, $database_host, $database_name);

      try {
        $this->connection = new PDO($database_connection_query_modified, $database_user, $database_password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $exception) {
        if (!$error_is_json) {
          die($exception->getMessage());
        } else {
          die(json_encode([
            'message' => $exception->getMessage(),
            'statusCode' => 0,
            'outputData' => [
              'html' => $exception->getMessage()
            ]
          // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
          ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
      }
    }

    /**
     * Подключиться к базе данных в тестовом режиме
     * 
     * @return bool
     */
    public function connect_test() : bool {
      /** @var string $database_name Наименование базы данных */
      $database_name = $this->get_database_name();
      /** @var string $database_user Пользователь базы данных */
      $database_user = $this->get_database_user();
      /** @var string $database_password Пароль базы данных */
      $database_password = $this->get_database_password();
      /** @var string $database_host Хост базы данных */
      $database_host = $this->get_database_host();

      /** @var EnumDatabaseManagementSystem $database_management_system */
      $database_management_system = $this->get_database_management_system();
      switch ($database_management_system->value) {
        case 'mysql': $database_connection_query = 'mysql:host=%s;dbname=%s'; break;
        case 'pgsql': $database_connection_query = 'pgsql:host=%s;dbname=%s'; break;
      }

      $database_connection_query_modified = sprintf($database_connection_query, $database_host, $database_name);

      try {
        $this->connection = new PDO($database_connection_query_modified, $database_user, $database_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
        return true;
      } catch (PDOException $exception) {
        return false;
      }
    }

    /**
     * Получить содержимое файла формата SQL
     * 
     * @param string $file_path
     * 
     * @return string
     */
    public function get_file_sql(string $file_path) : string {
      $file_path_full = sprintf('%s/%s/%s', CMS_ROOT_DIRECTORY, self::SQL_LIBRARY_PATH, $file_path);
      if (file_exists($file_path_full)) {
        return file_get_contents($file_path_full);
      }

      return '';
    }
  }

}

?>
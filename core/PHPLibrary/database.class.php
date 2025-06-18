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

    private string $name;
    private string $user;
    private string $password;
    private string $host;
    private EnumDatabaseManagementSystem $managementSystem;
    private DatabaseQueryBuilder $queryBuilder;
    public PDO|null $connection = null;


    /**
     * __construct
     *
     * @return void
     */
    public function __construct(EnumDatabaseManagementSystem $managementSystem) {
      $this->set_database_management_system($managementSystem);
    }
    
    /**
     * Назначение наименования расширения SQL для работы с базой данных
     *
     * @param  mixed $system
     * @return void
     */
    private function set_database_management_system(EnumDatabaseManagementSystem $system) {
      $this->managementSystem = $system;
    }
    
    /**
     * Получение наименования расширения SQL для работы с базой данных
     *
     * @return EnumDatabaseManagementSystem
     */
    private function get_database_management_system() : EnumDatabaseManagementSystem {
      return $this->managementSystem;
    }
    
    /**
     * Назначить имя базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_name(string $value) : void {
      $this->name = $value;
    }
    
    /**
     * Назначить имя пользователя базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_user(string $value) : void {
      $this->user = $value;
    }
    
    /**
     * Назначить пароль доступа к базе данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_password(string $value) : void {
      $this->password = $value;
    }
    
    /**
     * Назначить хост базы данных
     *
     * @param  mixed $value
     * @return void
     */
    public function set_database_host(string $value) : void {
      $this->host = $value;
    }
    
    /**
     * Получить имя базы данных
     *
     * @return string
     */
    private function get_database_name() : string {
      return $this->name;
    }
    
    /**
     * Получить имя пользователя базы данных
     *
     * @return string
     */
    private function get_database_user() : string {
      return $this->user;
    }
    
    /**
     * Получить пароль базы данных
     *
     * @return string
     */
    private function get_database_password() : string {
      return $this->password;
    }
    
    /**
     * Получить хост базы данных
     *
     * @return string
     */
    private function get_database_host() : string {
      return $this->host;
    }

    /**
     * Подключиться к базе данных
     * 
     * @param bool $errorIsJSON
     * 
     * @return [type]
     */
    public function connect(bool $errorIsJSON = false) {
      /** @var string $name Наименование базы данных */
      $name = $this->get_database_name();
      /** @var string $user Пользователь базы данных */
      $user = $this->get_database_user();
      /** @var string $password Пароль базы данных */
      $password = $this->get_database_password();
      /** @var string $host Хост базы данных */
      $host = $this->get_database_host();
      
      /** @var EnumDatabaseManagementSystem $managementSystem */
      $managementSystem = $this->get_database_management_system();
      switch ($managementSystem->value) {
        case 'mysql': $connectionQuery = 'mysql:host=%s;dbname=%s'; break;
        case 'pgsql': $connectionQuery = 'pgsql:host=%s;dbname=%s'; break;
      }

      $connectionQueryModified = sprintf($connectionQuery, $host, $name);

      try {
        $this->connection = new PDO($connectionQueryModified, $user, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $exception) {
        if (!$errorIsJSON) {
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
      $name = $this->get_database_name();
      $user = $this->get_database_user();
      $password = $this->get_database_password();
      $host = $this->get_database_host();

      /** @var EnumDatabaseManagementSystem $managementSystem */
      $managementSystem = $this->get_database_management_system();
      switch ($managementSystem->value) {
        case 'mysql': $connectionQuery = 'mysql:host=%s;dbname=%s'; break;
        case 'pgsql': $connectionQuery = 'pgsql:host=%s;dbname=%s'; break;
      }

      $connectionQueryModified = sprintf($connectionQuery, $host, $name);

      try {
        $this->connection = new PDO($connectionQueryModified, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
        return true;
      } catch (PDOException $exception) {
        return false;
      }
    }

    /**
     * Получить содержимое файла формата SQL
     * 
     * @param string $filePath
     * 
     * @return string
     */
    public function get_file_sql(string $filePath) : string {
      $filePathFull = CMS_ROOT_DIRECTORY . '/' . self::SQL_LIBRARY_PATH . '/' . $filePath;
      return file_exists($filePathFull) ? file_get_contents($filePathFull) : '';
    }
  }

}

?>
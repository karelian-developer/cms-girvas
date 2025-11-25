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

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\DatabaseManagementSystem as EnumDatabaseManagementSystem;
use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \PDO as PDO;
use \PDOException as PDOException;

/**
 * База данных
 */
final class Database
{
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
  public function __construct(EnumDatabaseManagementSystem $managementSystem)
  {
    $this->setDatabaseManagementSystem($managementSystem);
  }
  
  /**
   * Назначение наименования расширения SQL для работы с базой данных
   *
   * @param  mixed $system
   * @return void
   */
  private function setDatabaseManagementSystem(EnumDatabaseManagementSystem $system)
  {
    $this->managementSystem = $system;
  }
  
  /**
   * Получение наименования расширения SQL для работы с базой данных
   *
   * @return EnumDatabaseManagementSystem
   */
  private function getDatabaseManagementSystem() : EnumDatabaseManagementSystem
  {
    return $this->managementSystem;
  }
  
  /**
   * Назначить имя базы данных
   *
   * @param  mixed $value
   * @return void
   */
  public function setDatabaseName(string $value) : void
  {
    $this->name = $value;
  }
  
  /**
   * Назначить имя пользователя базы данных
   *
   * @param  mixed $value
   * @return void
   */
  public function setDatabaseUser(string $value) : void
  {
    $this->user = $value;
  }
  
  /**
   * Назначить пароль доступа к базе данных
   *
   * @param  mixed $value
   * @return void
   */
  public function setDatabasePassword(string $value) : void
  {
    $this->password = $value;
  }
  
  /**
   * Назначить хост базы данных
   *
   * @param  mixed $value
   * @return void
   */
  public function setDatabaseHost(string $value) : void
  {
    $this->host = $value;
  }
  
  /**
   * Получить имя базы данных
   *
   * @return string
   */
  private function getDatabaseName() : string
  {
    return $this->name;
  }
  
  /**
   * Получить имя пользователя базы данных
   *
   * @return string
   */
  private function getDatabaseUser() : string
  {
    return $this->user;
  }
  
  /**
   * Получить пароль базы данных
   *
   * @return string
   */
  private function getDatabasePassword() : string
  {
    return $this->password;
  }
  
  /**
   * Получить хост базы данных
   *
   * @return string
   */
  private function getDatabaseHost() : string
  {
    return $this->host;
  }

  /**
   * Подключиться к базе данных
   * 
   * @param bool $errorIsJSON
   * 
   * @return [type]
   */
  public function connect(bool $errorIsJSON = false)
  {
    /** @var string $name Наименование базы данных */
    $name = $this->getDatabaseName();
    /** @var string $user Пользователь базы данных */
    $user = $this->getDatabaseUser();
    /** @var string $password Пароль базы данных */
    $password = $this->getDatabasePassword();
    /** @var string $host Хост базы данных */
    $host = $this->getDatabaseHost();
    
    /** @var EnumDatabaseManagementSystem $managementSystem */
    $managementSystem = $this->getDatabaseManagementSystem();
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
  public function connectTest() : bool
  {
    $name = $this->getDatabaseName();
    $user = $this->getDatabaseUser();
    $password = $this->getDatabasePassword();
    $host = $this->getDatabaseHost();

    /** @var EnumDatabaseManagementSystem $managementSystem */
    $managementSystem = $this->getDatabaseManagementSystem();
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
  public function getFileSQL(string $filePath) : string
  {
    $filePathFull = CMS_ROOT_DIRECTORY . '/' . self::SQL_LIBRARY_PATH . '/' . $filePath;
    return file_exists($filePathFull) ? file_get_contents($filePathFull) : '';
  }
}
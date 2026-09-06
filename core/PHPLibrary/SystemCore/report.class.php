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

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
final class Report
{
  public const REPORT_TYPE_ID_AP_ENTRY_CREATED = 11000000;
  public const REPORT_TYPE_ID_AP_ENTRY_EDITED = 11000001;
  public const REPORT_TYPE_ID_AP_ENTRY_DELETED = 11000002;

  public const REPORT_TYPE_ID_AP_PAGE_CREATED = 11000003;
  public const REPORT_TYPE_ID_AP_PAGE_EDITED = 11000004;
  public const REPORT_TYPE_ID_AP_PAGE_DELETED = 11000005;

  public const REPORT_TYPE_ID_AP_MEDIA_UPLOADED = 11000006;
  public const REPORT_TYPE_ID_AP_MEDIA_DELETED = 11000007;

  public const REPORT_TYPE_ID_AP_USER_CREATED = 11000008;
  public const REPORT_TYPE_ID_AP_USER_EDITED = 11000009;
  public const REPORT_TYPE_ID_AP_USER_DELETED = 11000010;

  public const REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_CREATED = 11000011;
  public const REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_EDITED = 11000012;
  public const REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED = 11000013;

  public const REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_CREATED = 11000014;
  public const REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_EDITED = 11000015;
  public const REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED = 11000016;

  public const REPORT_TYPE_ID_AP_FORM_CREATED = 11000017;
  public const REPORT_TYPE_ID_AP_FORM_EDITED = 11000018;
  public const REPORT_TYPE_ID_AP_FORM_DELETED = 11000019;

  public const REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED = 11000020;
  public const REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED = 11000021;
  public const REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED = 11000022;

  public const REPORT_TYPE_ID_AP_ENTRIES_COMMENT_CREATED = 11000023;
  public const REPORT_TYPE_ID_AP_ENTRIES_COMMENT_EDITED = 11000024;
  public const REPORT_TYPE_ID_AP_ENTRIES_COMMENT_DELETED = 11000025;

  public const REPORT_TYPE_ID_AP_VIEWING_LOGS = 11000026;

  public const REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL = 10000001;
  public const REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS = 10000002;
  public const REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL = 20000001;
  public const REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS = 20000002;

  public const REPORT_TYPE_ID_BASE_USER_CREATED = 11100001;
  public const REPORT_TYPE_ID_BASE_USER_EDITED = 11100002;
  public const REPORT_TYPE_ID_BASE_USER_DELETED = 11100003;
  public const REPORT_TYPE_ID_BASE_USER_BANNED = 11100004;
  public const REPORT_TYPE_ID_BASE_USER_UNBANNED = 11100005;
  public const REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED = 11100006;

  /** @var string Ключ шифрования для ПДн в логах */
  private static ?string $encryptionKey = null;

  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore,
    private int $id
  ) {}

  /**
   * Инициализация данных из БД
   *
   * @param  mixed $columns
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
   * Назначить идентификатор
   *
   * @param  mixed $value
   * @return void
   */
  private function setID(int $value) : void
  {
    $this->id = $value;
  }

  /**
   * Получить идентификатор
   * 
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить идентификационный номер типа отчета
   *
   * @return int
   */
  public function getTypeID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['typeID'])) {
        return is_numeric($metadata['typeID'])
          ? (int)$metadata['typeID']
          : 0;
      }
    }

    return 0;
  }

  /**
   * Получить идентификационный номер категории отчета
   *
   * @return int
   */
  public function getCategoryID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);
      if (isset($metadata['typeID'])) {
        if (in_array($metadata['typeID'], [
          self::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
          self::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
        ])) {
          return 2;
        }
      }
    }

    return 0;
  }

  /**
   * Получить метадату отчета
   *
   * @return array
   */
  public function getMetadata() : array
  {
    if (property_exists($this, 'metadata')) {
      return json_decode($this->metadata, true);
    }

    return [];
  }

  /**
   * Получить переменные отчета
   *
   * @param User|null $viewer Пользователь, просматривающий логи (для расшифровки ПДн)
   * @return array
   */
  public function getVariables(?User $viewer = null) : array
  {
    if (property_exists($this, 'variables')) {
      $variables = json_decode($this->variables, true);
      
      // Если есть просматривающий — расшифровываем ПДн
      if ($viewer !== null) {
        return $this->decryptVariables($variables, $viewer);
      }
      
      return $variables;
    }

    return [];
  }

  /**
   * Получить содержимое отчета
   *
   * @return string
   */
  public function getContent() : string
  {
    $reflectionClass = new \ReflectionClass('\core\PHPLibrary\SystemCore\Report');
    $reflectionClassConstants = $reflectionClass->getConstants();

    foreach ($reflectionClassConstants as $name => $value) {
      if ($value === $this->getTypeID()) {
        return sprintf('{LANG:%s}', $name);
      }
    }

    return '';
  }

  /**
   * Получить время создания в UNIX-формате
   *
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить время обновления в UNIX-формате
   *
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Добавить переменную и ее значение
   *
   * @param  string $name
   * @param  mixed $value
   * @return void
   */
  public function addVariable(string $name, mixed $value) : void
  {
    $this->variables[$name] = $value;
  }

  /**
   * Создание записи в базе данных
   *
   * @param  mixed $CoreInterface
   * @param  int $typeID
   * @param  array $variables
   * 
   * @return Report
   */
  public static function create(CoreInterface $CMSCore, int $typeID, array $variables = []) : Report|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    // Очищаем переменные от ПДн (с шифрованием)
    $variables = self::sanitizeVariables($CMSCore, $variables);

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('reports');
    $queryBuilder->statement->addColumn('variables');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
    $queryBuilder->statement->assembly();

    /** @var int Время создания записи в БД в UNIX-формате */
    $createdUnixTimestamp = time();

    $metadata = ['typeID' => $typeID];
    $metadataJSON = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $variablesJSON = json_encode($variables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':variables', $variablesJSON, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':metadata', $metadataJSON, \PDO::PARAM_STR);
      $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
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
      $queryBuilder->statement->clauseFrom->addTable('reports');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition('`id` = LAST_INSERT_ID()');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

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
      return $result ? new Report($CMSCore, $result['id']) : null;
    }

    return null;
  }

  /**
   * Очистка переменных от ПДн перед сохранением (с шифрованием)
   * 
   * @param CoreInterface $CMSCore
   * @param array $variables
   * @return array
   */
  private static function sanitizeVariables(CoreInterface $CMSCore, array $variables): array
  {
    $forbiddenKeys = [
      'email', 'password', 'passwordHash', 'securityHash',
      'phone', 'phoneNumber', 'name', 'surname', 'patronymic',
      'birthdate', 'birthdateUnixTimestamp', 'passport',
      'snils', 'inn', 'address', 'registrationIP', 'token',
      'user_login', 'user_email', 'user_password', 'user_name',
      'user_surname', 'user_patronymic', 'user_birthdate',
      'user_password_repeat', 'user_password_old', 'user_group_id',
      'user_id', 'user_is_block'
    ];

    foreach ($variables as $key => $value) {
      $keyLower = strtolower($key);
      if (in_array($keyLower, $forbiddenKeys) && !empty($value) && !is_array($value)) {
        $variables[$key] = self::encryptValue($CMSCore, (string)$value);
      }
      if (is_array($value)) {
        $variables[$key] = self::sanitizeVariables($CMSCore, $value);
      }
    }

    return $variables;
  }

  /**
   * Шифрование значения для хранения в логах
   * 
   * @param CoreInterface $CMSCore
   * @param string $value
   * @return string
   */
  private static function encryptValue(CoreInterface $CMSCore, string $value): string
  {
    $salt = $CMSCore->configurator->get('salt');
    $method = 'AES-256-CBC';
    $ivLength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($value, $method, $salt, 0, $iv);
    return '[ENCRYPTED:' . base64_encode($iv . $encrypted) . ']';
  }

  /**
   * Проверка, зашифровано ли значение
   * 
   * @param mixed $value
   * @return bool
   */
  public static function isEncrypted($value): bool
  {
    if (!is_string($value)) {
      return false;
    }
    return strpos($value, '[ENCRYPTED:') === 0;
  }

  /**
   * Расшифровка переменных (только для уполномоченных пользователей)
   * 
   * @param array $variables
   * @param User $viewer
   * @return array
   */
  public function decryptVariables(array $variables, User $viewer): array
  {
    // Проверка прав доступа к ПДн в логах
    $viewerGroup = $viewer->getGroup();
    $hasAccess = $viewer->isSuperAdmin() || 
                 ($viewerGroup && $viewerGroup->hasPermissionAdminViewingLogs());

    if (!$hasAccess) {
      return $variables;
    }

    foreach ($variables as $key => $value) {
      if (is_array($value)) {
        $variables[$key] = $this->decryptVariables($value, $viewer);
      } elseif (self::isEncrypted($value)) {
        $variables[$key] = self::decryptValue($this->CMSCore, $value);
      }
    }

    return $variables;
  }

  /**
   * Расшифровка одного значения
   * 
   * @param CoreInterface $CMSCore
   * @param string $encrypted
   * @return string
   */
  private static function decryptValue(CoreInterface $CMSCore, string $encrypted): string
  {
    if (preg_match('/\[ENCRYPTED:([^\]]+)\]/', $encrypted, $matches)) {
      $salt = $CMSCore->configurator->get('salt');
      $method = 'AES-256-CBC';
      $data = base64_decode($matches[1]);
      $ivLength = openssl_cipher_iv_length($method);
      $iv = substr($data, 0, $ivLength);
      $encryptedData = substr($data, $ivLength);
      $decrypted = openssl_decrypt($encryptedData, $method, $salt, 0, $iv);
      return $decrypted ?: '[DECRYPTION_FAILED]';
    }

    return $encrypted;
  }

  /**
   * Получить данные колонок записи в базе данных
   *
   * @param  mixed $columns
   * @return void
   */
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('reports');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('"id" = :id');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int Идентификационный номер записи */
    $id = $this->getID();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $id, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result : null;
  }
}
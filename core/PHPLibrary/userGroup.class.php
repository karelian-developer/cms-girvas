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

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class UserGroup
{
  // ID первичных групп пользователей
  public const GROUP_SUPER_ID   = 1;
  public const GROUP_USER_ID    = 4;

  // Административные права
  public const PERMISSION_ADMIN_PANEL_AUTH                    = 1 << 0;
  public const PERMISSION_ADMIN_USERS_MANAGEMENT              = 1 << 1;
  public const PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT       = 1 << 2;
  public const PERMISSION_ADMIN_MODULES_MANAGEMENT            = 1 << 3;
  public const PERMISSION_ADMIN_TEMPLATES_MANAGEMENT          = 1 << 4;
  public const PERMISSION_ADMIN_SETTINGS_MANAGEMENT           = 1 << 5;
  public const PERMISSION_ADMIN_VIEWING_LOGS                  = 1 << 6;
  public const PERMISSION_ADMIN_FEEDS_MANAGEMENT              = 1 << 17;
  public const PERMISSION_ADMIN_FORMS_MANAGEMENT              = 1 << 19;
  public const PERMISSION_ADMIN_CONTENT_BLOCKS_MANAGEMENT     = 1 << 20;
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
  public const PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT          = 1 << 21;

  public const PERMISSION_BASE_ENTRY_COMMENT_CREATE           = 1 << 14;
  public const PERMISSION_BASE_ENTRY_COMMENT_CHANGE           = 1 << 15;
  public const PERMISSION_BASE_ENTRY_COMMENT_RATE             = 1 << 16;

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $id
  ) {}

  /**
   * Инициализация данных из БД
   *
   * @param  mixed $columns
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
   * Получить идентификатор
   * 
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }

  /**
   * Получить имя
   * 
   * @return string
   */
  public function getName() : string
  {
    return $this->name ?? '';
  }

  /**
   * Получить права в числовом формате
   * 
   * @return int
   */
  public function getPermissions() : int
  {
    return $this->permissions ?? 0;
  }

  /**
   * Получить массив объектов пользователей
   * 
   * @return array
   */
  public function getUsers() : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'JSON_EXTRACT(`metadata`, \'$.groupID\') = :groupID',
      'postgresql' => '(metadata::jsonb->>\'groupID\')::int = :groupID'
    ]);
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

  /**
   * Получить количество пользователей
   * 
   * @return int
   */
  public function getUsersCount() : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(*) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'JSON_EXTRACT(`metadata`, \'$.groupID\') = :groupID',
      'postgresql' => '(metadata::jsonb->>\'groupID\')::int = :groupID'
    ]);
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

  /**
   * Получить временную отметку создания записи в UNIX-формате
   * 
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ?? 0;
  }

  /**
   * Получить временную отметку обновления записи в UNIX-формате
   * 
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Проверить наличие права
   * 
   * @return bool
   */
  public function permissionCheck(int $permission) : bool
  {
    $permissions = $this->getPermissions();
    return ($permissions & $permission) === $permission;
  }
  
  /**
   * Получить заголовок
   *
   * @return string
   */
  public function getTitle($localeName = 'en_US') : string
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);
      
      if (isset($texts[$localeName]['title'])) {
        return $texts[$localeName]['title'];
      }
    }

    return '';
  }

  /**
   * Получить тексты
   * 
   * @return array
   */
  public function getTexts() : array
  {
    if (property_exists($this, 'texts')) {
      return json_decode($this->texts, true);
    }

    return [];
  }

  /**
   * Получить заполненные тексты
   * 
   * @return array
   */
  public function getCompletedTexts() : array
  {
    if (property_exists($this, 'texts')) {
      $texts = json_decode($this->texts, true);

      return array_filter($texts, function ($locale) {
        if (!is_array($locale) || empty($locale)) {
          return false;
        };

        foreach ($locale as $key => $value) {
          if (empty($value) && in_array($key, ['title'])) {
            return false;
          }
        }

        return true;
      });
    }

    return [];
  }

  /**
   * Получить данные по заполненным локализациям
   * 
   * @param CoreInterface $CMSCore
   * 
   * @return array
   */
  public function getCompletedLocalesData(CoreInterface $CMSCore) : array
  {
    if (property_exists($this, 'texts')) {
      $texts = $this->getCompletedTexts();
      $locales = [];

      foreach ($texts as $localeName => $data) {
        $CMSLocale = new CMSLocale($CMSCore, $localeName);
        $CMSLocale->initPathes();
        $locales[$localeName] = [
          'title' => $CMSLocale->getTitle(),
          'iconURL' => $CMSLocale->getIconURL()
        ];
      }

      return $locales;
    }

    return [];
  }

  /**
   * Проверка группы на супергруппу
   * 
   * @return bool
   */
  public function isSuperGroup() : bool
  {
    return $this->id === self::GROUP_SUPER_ID;
  }
  
  /**
   * Проверка группы на пользователя (первичная группа)
   * 
   * @return bool
   */
  public function isUserGroup() : bool
  {
    return $this->id === self::GROUP_USER_ID;
  }

  /**
   * Проверить наличие права авторизации в панели управления
   * 
   * @return bool
   */
  public function hasPermissionAdminPanelAuth() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_PANEL_AUTH);
  }

  /**
   * Проверить наличие права управления пользователями
   * 
   * @return bool
   */
  public function hasPermissionAdminUsersManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_USERS_MANAGEMENT);
  }

  /**
   * Проверить наличие права управления группами пользователей
   * 
   * @return bool
   */
  public function hasPermissionAdminUsersGroupsManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
  }

  /**
   * Проверить наличие права управления темами
   * 
   * @return bool
   */
  public function hasPermissionAdminThemesManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
  }

  /**
   * Проверить наличие права управления настройками системы
   * 
   * @return bool
   */
  public function hasPermissionAdminSettingsManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
  }

  /**
   * Проверить наличие права просмотра логов
   * 
   * @return bool
   */
  public function hasPermissionAdminViewingLogs() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_VIEWING_LOGS);
  }

  /**
   * Проверить наличие права управления фидами
   * 
   * @return bool
   */
  public function hasPermissionAdminFeedsManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_FEEDS_MANAGEMENT);
  }

  /**
   * Проверить наличие права управления формами
   * 
   * @return bool
   */
  public function hasPermissionAdminFormsManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_FORMS_MANAGEMENT);
  }

  /**
   * Проверить наличие права суперпользователя
   * 
   * @return bool
   */
  public function hasPermissionSuperUser() : bool
  {
    return $this->permissionCheck(self::PERMISSION_ADMIN_SUPERUSER);
  }

  /**
   * Проверить наличие права блокировки пользователей
   * 
   * @return bool
   */
  public function hasPermissionModerUsersBan() : bool
  {
    return $this->permissionCheck(self::PERMISSION_MODER_USERS_BAN);
  }

  /**
   * Проверить наличие права управления комментариями к записям
   * 
   * @return bool
   */
  public function hasPermissionModerEntriesCommentsManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
  }

  /**
   * Проверить наличие права выдачи предупреждений пользователям
   * 
   * @return bool
   */
  public function hasPermissionModerUsersWarns() : bool
  {
    return $this->permissionCheck(self::PERMISSION_MODER_USERS_WARNS);
  }

  /**
   * Проверить наличие права управления медиа-файлами
   * 
   * @return bool
   */
  public function hasPermissionEditorMediaFilesManagement() : bool
  {
    return $this->permissionCheck(self::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
  }

  /**
   * Проверить наличие права редактирования записей
   * 
   * @return bool
   */
  public function hasPermissionEditorEntriesEdit() : bool
  {
    return $this->permissionCheck(self::PERMISSION_EDITOR_ENTRIES_EDIT);
  }

  /**
   * Проверить наличие права редактирования категорий записей
   * 
   * @return bool
   */
  public function hasPermissionEditorEntriesCategoriesEdit() : bool
  {
    return $this->permissionCheck(self::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
  }

  /**
   * Проверить наличие права редактирования статических страниц
   * 
   * @return bool
   */
  public function hasPermissionEditorPagesStaticEdit() : bool
  {
    return $this->permissionCheck(self::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
  }

  /**
   * Проверить наличие права редактирования контент-блоков
   * 
   * @return bool
   */
  public function hasPermissionEditorContentBlocksEdit() : bool
  {
    return $this->permissionCheck(self::PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT);
  }

  /**
   * Проверить наличие права создания комментариев к записям
   * 
   * @return bool
   */
  public function hasPermissionBaseEntryCommentCreate() : bool
  {
    return $this->permissionCheck(self::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
  }

  /**
   * Проверить наличие права изменения комментариев к записям
   * 
   * @return bool
   */
  public function hasPermissionBaseEntryCommentChange() : bool
  {
    return $this->permissionCheck(self::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
  }

  /**
   * Проверить наличие права изменения рейтинга комментариев к записям
   * 
   * @return bool
   */
  public function hasPermissionBaseEntryCommentRate() : bool
  {
    return $this->permissionCheck(self::PERMISSION_BASE_ENTRY_COMMENT_RATE);
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
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $userGroupID Идентификационный номер записи */
    $userGroupID = $this->getID();

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
    return $result ? $result : null;
  }
  
  /**
   * Получить объект группы пользователя по наименованию
   *
   * @param  SystemCore $CMSCore
   * @param  string $groupName
   * @return UserGroup
   */
  public static function getByName(SystemCore $CMSCore, string $groupName) : UserGroup|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'LOWER(`name`) = :name',
      'postgresql' => 'LOWER("name") = :name'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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
    
    return $result ? new UserGroup($CMSCore, (int) $result['id']) : null;
  }
  
  /**
   * Проверить существование группы пользователей по наименованию
   *
   * @param  mixed $CMSCore
   * @param  string $groupName
   * @return void
   */
  public static function existsByName(SystemCore $CMSCore, string $groupName) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => 'LOWER(`name`) = :name',
      'postgresql' => 'LOWER("name") = :name'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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
    
    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Проверить существование группы пользователей по ID
   *
   * @param  mixed $CMSCore
   * @param  int $groupID
   * @return void
   */
  public static function existsByID(SystemCore $CMSCore, int $groupID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      $databaseQuery->bindParam(':id', $groupID, \PDO::PARAM_INT);
      $databaseQuery->execute();
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $databaseQuery->fetchColumn() ? true : false;
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

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('users_groups');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addConditionAdaptive([
      'mysql' => '`id` = :id',
      'postgresql' => '"id" = :id'
    ]);
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

    return $execute ? true : false;
  }

  /**
   * Создать
   * 
   * @param SystemCore $CMSCore
   * @param string $groupName
   * @param array $texts
   * @param int $permissions
   * 
   * @return UserGroup|null
   */
  public static function create(SystemCore $CMSCore, string $groupName, array $texts = [], int $permissions = 0x0000000000000000) : UserGroup|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('users_groups');
    $queryBuilder->statement->addColumn('name');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->addColumn('permissions');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->addColumn('texts');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
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

    if ($CMSConfigDatabase['dms'] === CMSDMS::MySQL) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('users_groups');
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
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new UserGroup($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующего пользователя
   *
   * @param  array $data
   * 
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('users_groups');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    foreach (['texts', 'metadata'] as $columnName) {
      $fieldsJSON = [];
      
      if (!isset($data[$columnName])) {
        continue;
      }

      foreach ($data[$columnName] as $name => $value) {
        $valueJSON = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fieldsJSON[] = match ($queryBuilder->DMS) {
          CMSDMS::MySQL => sprintf('"%s": %s', $name, $valueJSON),
          CMSDMS::PostgreSQL => sprintf('\'{"%s": %s}\'::jsonb', $name, $valueJSON)
        };
      }

      if (!empty($data[$columnName])) {
        $queryBuilder->statement->clauseSet->addColumnAdaptive($columnName, [
          'mysql' => 'JSON_MERGE_PATCH(COALESCE(' . $columnName . ', \'{}\'), CAST(\'{' . implode(', ', $fieldsJSON) . '}\' AS JSON))',
          'postgresql' => $columnName . '::jsonb || ' . implode(' || ', $fieldsJSON)
        ]);
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

    /** @var int $user_updated_unix_timestamp Текущее время в UNIX-формате */
    $userGroupUpdatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $valueTypeName = gettype($value);
          $valueType = match ($valueTypeName) {
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'null' => \PDO::PARAM_NULL,
          };
          
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

    return $execute ? true : false;
  }
}
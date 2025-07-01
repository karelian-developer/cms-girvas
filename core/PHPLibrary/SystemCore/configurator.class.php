<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;
use \core\PHPLibrary\SystemCore as CMSCore;
use \PDOException as PDOException;
use \PDO as PDO;

/**
 * Class Configurator
 */
final class Configurator
{
  const FILE_PATH = 'core/configuration.php';

  public string $metaTitle = '';
  public string $metaDescription = '';
  public array $metaKeywords = [];
  private array $data = [];
  private CMSCore $CMSCore;
  
  /**
   * __construct
   *
   * @param  mixed $CMSCore
   * @return void
   */
  public function __construct(CMSCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;
    $filePath = CMS_ROOT_DIRECTORY . '/' . self::FILE_PATH;

    if (file_exists($filePath)) {
      $this->merge($this->getFileData());
    }
  }

  /**
   * Назначить заголовок для веб-сайта
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setMetaTitle(string $value) : void
  {
    $this->metaTitle = $value;
  }

  /**
   * Назначить описание для веб-сайта
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setMetaDescription(string $value) : void
  {
    $this->metaDescription = $value;
  }

  /**
   * Назначить ключевые слова для веб-сайта
   * 
   * @param array $values
   * 
   * @return void
   */
  public function setMetaKeywords(array $values) : void
  {
    $this->metaKeywords = $values;
  }

  /**
   * Добавить ключевое слово
   * 
   * @param mixed $value
   * 
   * @return void
   */
  public function addMetaKeyword(mixed $value) : void
  {
    array_push($this->metaKeywords, $value);
  }

  /**
   * Получить заголовок для веб-сайта
   * 
   * @return string
   */
  public function getMetaTitle() : string
  {
    return $this->metaTitle;
  }

  /**
   * Получить описание для веб-сайта
   * 
   * @return string
   */
  public function getMetaDescription() : string
  {
    return $this->metaDescription;
  }

  /**
   * Получить ключевые слова для веб-сайта
   * 
   * @return array
   */
  public function getMetaKeywords() : array
  {
    return $this->metaKeywords;
  }

  /**
   * Получить ключевые слова для веб-сайта в формате строки
   * 
   * @return string
   */
  public function getMetaKeywordsImploded() : string
  {
    return implode(', ', $this->metaKeywords);
  }

  /**
   * Получить ключевые слова для веб-сайта в формате JSON
   * 
   * @return string
   */
  public function getMetaKeywordsJSON() : string
  {
    return json_encode($this->metaKeywords);
  }

  /**
   * Получить заголовок для веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSiteTitle() : string
  {
    return $this->existsDatabaseEntryValue('base_site_title') ? $this->getDatabaseEntryValue('base_site_title') : $this->CMSCore->getCMSTitle();
  }

  /**
   * Получить описание для веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSiteDescription() : string
  {
    return $this->existsDatabaseEntryValue('seo_site_description') ? $this->getDatabaseEntryValue('seo_site_description') : sprintf('%s %s developed by www.garbalo.com', $this->CMSCore->getCMSTitle(), $this->CMSCore->getCMSVersion());
  }

  /**
   * Получить ключевые слова для веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSiteKeywords() : string
  {
    return $this->existsDatabaseEntryValue('seo_site_keywords') ? implode(', ', json_decode($this->getDatabaseEntryValue('seo_site_keywords'), true)) : implode(', ', ['cms girvas', 'empty site', 'karelian developer']);
  }

  /**
   * Получить кодировку веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSiteCharset() : string
  {
    return $this->existsDatabaseEntryValue('base_site_charset') ? $this->getDatabaseEntryValue('base_site_charset') : 'UTF-8';
  }

  /**
   * Получить временную зону веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSiteTimezone() : string
  {
    return $this->existsDatabaseEntryValue('base_timezone') ? $this->getDatabaseEntryValue('base_timezone') : 'Europe/Moscow';
  }

  /**
   * Получить максимальный вес загружаемого файла
   * 
   * @return int
   */
  public function getUploadFileWeightMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_weight_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_weight_max') : 0;
  }

  /**
   * Получить максимальную ширину загружаемого изображения
   * 
   * @return int
   */
  public function getUploadFileImageWidthMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_image_width_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_image_width_max') : 0;
  }

  /**
   * Получить максимальную ширину загружаемого изображения
   * 
   * @return int
   */
  public function getUploadFileImageHeightMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_image_height_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_image_height_max') : 0;
  }

  /**
   * Получить максимальный вес загружаемого аватара
   * 
   * @return int
   */
  public function getUploadFileImageAvatarWeightMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_image_avatar_weight_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_image_avatar_weight_max') : 0;
  }

  /**
   * Получить максимальную ширину загружаемого аватара
   * 
   * @return int
   */
  public function getUploadFileImageAvatarWidthMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_image_avatar_width_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_image_avatar_width_max') : 0;
  }

  /**
   * Получить максимальную высоту загружаемого аватара
   * 
   * @return int
   */
  public function getUploadFileImageAvatarHeightMax() : int
  {
    return $this->existsDatabaseEntryValue('files_upload_file_image_avatar_height_max') ? (int) $this->getDatabaseEntryValue('files_upload_file_image_avatar_height_max') : 0;
  }

  /**
   * Получить статус состояния раздела "Записи"
   * 
   * @return string|bool
   */
  public function getSectionEntriesStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_entries_status')) {
        return $this->getDatabaseEntryValue('base_section_entries_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_entries_status') ? $this->getDatabaseEntryValue('base_section_entries_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Статические страницы"
   * 
   * @return string|bool
   */
  public function getSectionStaticPagesStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_static_pages_status')) {
        return $this->getDatabaseEntryValue('base_section_static_pages_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_static_pages_status') ? $this->getDatabaseEntryValue('base_section_static_pages_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Модули"
   * 
   * @return string|bool
   */
  public function getSectionModulesStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_modules_status')) {
        return $this->getDatabaseEntryValue('base_section_modules_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_modules_status') ? $this->getDatabaseEntryValue('base_section_modules_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Шаблоны"
   * 
   * @return string|bool
   */
  public function getSectionTemplatesStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_templates_status')) {
        return $this->getDatabaseEntryValue('base_section_templates_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_templates_status') ? $this->getDatabaseEntryValue('base_section_templates_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Пользователи"
   * 
   * @return string|bool
   */
  public function getSectionUsersStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_users_status')) {
        return $this->getDatabaseEntryValue('base_section_users_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_users_status') ? $this->getDatabaseEntryValue('base_section_users_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Медиа"
   * 
   * @return string|bool
   */
  public function getSectionMediaStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_media_status')) {
        return $this->getDatabaseEntryValue('base_section_media_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_media_status') ? $this->getDatabaseEntryValue('base_section_media_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Фиды"
   * 
   * @return string|bool
   */
  public function getSectionFeedsStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_feeds_status')) {
        return $this->getDatabaseEntryValue('base_section_feeds_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_feeds_status') ? $this->getDatabaseEntryValue('base_section_feeds_status') : 'off';
  }

  /**
   * Получить статус состояния раздела "Аналитика"
   * 
   * @return string|bool
   */
  public function getSectionAnalyticsStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_section_analytics_status')) {
        return $this->getDatabaseEntryValue('base_section_analytics_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_section_analytics_status') ? $this->getDatabaseEntryValue('base_section_analytics_status') : 'off';
  }

  /**
   * Получить статус состояния технических работ
   * 
   * @return string|bool
   */
  public function getEngineeringWorksStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('base_engineering_works_status')) {
        return $this->getDatabaseEntryValue('base_engineering_works_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('base_engineering_works_status') ? $this->getDatabaseEntryValue('base_engineering_works_status') : 'off';
  }

  /**
   * Получить причину закрытия сайта на технические работы
   * 
   * @return string
   */
  public function getEngineeringWorksText() : string
  {
    return $this->existsDatabaseEntryValue('base_engineering_works_text') ? (string) $this->getDatabaseEntryValue('base_engineering_works_text') : '';
  }

  /**
   * Получить статус настройки автоматической конвертации изображений
   * 
   * @param bool $isBoolean
   * 
   * @return string
   */
  public function getAutoConvertFileImageStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('files_auto_convert_file_image_status')) {
        return $this->getDatabaseEntryValue('files_auto_convert_file_image_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('files_auto_convert_file_image_status') ? $this->getDatabaseEntryValue('files_auto_convert_file_image_status') : 'off';
  }

  /**
   * Получить расширение для автоматический конвертации изображения
   * 
   * @return string
   */
  public function getAutoConvertFileImageExtension() : string
  {
    return $this->existsDatabaseEntryValue('files_auto_convert_file_image_extension') ? $this->getDatabaseEntryValue('files_auto_convert_file_image_extension') : '';
  }

  /**
   * Получить статус возможности загрузки аватаров пользователей
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersUploadAvatarStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_upload_avatar_status')) {
        return $this->getDatabaseEntryValue('users_upload_avatar_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_upload_avatar_status') ? $this->getDatabaseEntryValue('users_upload_avatar_status') : 'off';
  }

  /**
   * Получить минимальное количество символов для логина пользователя
   * 
   * @return int
   */
  public function getUsersLoginLengthMin() : int
  {
    return $this->existsDatabaseEntryValue('users_login_length_min') ? (int) $this->getDatabaseEntryValue('users_login_length_min') : 4;
  }

  /**
   * Получить максимальное количество символов для логина пользователя
   * 
   * @return int
   */
  public function getUsersLoginLengthMax() : int
  {
    return $this->existsDatabaseEntryValue('users_login_length_max') ? (int) $this->getDatabaseEntryValue('users_login_length_max') : 0;
  }

  /**
   * Получить статус возможности редактирования логинов пользователей
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersLoginEditStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_login_edit_status')) {
        return $this->getDatabaseEntryValue('users_login_edit_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_login_edit_status') ? $this->getDatabaseEntryValue('users_login_edit_status') : 'off';
  }

  /**
   * Получить статус возможности использования специальных символов в логине
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersLoginSpecialSymbolsStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_login_special_symbols_status')) {
        return $this->getDatabaseEntryValue('users_login_special_symbols_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_login_special_symbols_status') ? $this->getDatabaseEntryValue('users_login_special_symbols_status') : 'off';
  }

  /**
   * Получить статус учета регистра символов в логине пользователя
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersLoginRegisterAccountingStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_login_register_accounting_status')) {
        return $this->getDatabaseEntryValue('users_login_register_accounting_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_login_register_accounting_status') ? $this->getDatabaseEntryValue('users_login_register_accounting_status') : 'off';
  }

  /**
   * Получить минимальное количество символов для пароля пользователя
   * 
   * @return int
   */
  public function getUsersPasswordLengthMin() : int
  {
    return $this->existsDatabaseEntryValue('users_password_length_min') ? (int) $this->getDatabaseEntryValue('users_password_length_min') : 6;
  }

  /**
   * Получить максимальное количество символов для пароля пользователя
   * 
   * @return int
   */
  public function getUsersPasswordLengthMax() : int
  {
    return $this->existsDatabaseEntryValue('users_password_length_max') ? (int) $this->getDatabaseEntryValue('users_password_length_max') : 0;
  }

  /**
   * Получить статус возможности использования специальных символов в пароле
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersPasswordSpecialSymbolsStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_password_special_symbols_status')) {
        return $this->getDatabaseEntryValue('users_password_special_symbols_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_password_special_symbols_status') ? $this->getDatabaseEntryValue('users_password_special_symbols_status') : 'off';
  }

  /**
   * Получить статус использования фильтра для логинов
   * 
   * @param bool $isBoolean
   * 
   * @return string|bool
   */
  public function getUsersLoginsBlacklistStatus(bool $isBoolean = false) : string|bool
  {
    if ($isBoolean) {
      if ($this->existsDatabaseEntryValue('users_logins_blacklist_status')) {
        return $this->getDatabaseEntryValue('users_logins_blacklist_status') === 'on' ? true : false;
      }

      return false;
    }

    return $this->existsDatabaseEntryValue('users_logins_blacklist_status') ? $this->getDatabaseEntryValue('users_logins_blacklist_status') : 'off';
  }

  /**
   * Получить список заблокированных логинов для пользователей в виде строки
   * 
   * @return string|array
   */
  public function getUsersLoginsBlacklist(bool $isArray = false) : string|array
  {
    if (!$isArray) {
      return $this->existsDatabaseEntryValue('users_logins_blacklist') ? implode(', ', json_decode($this->getDatabaseEntryValue('users_logins_blacklist'), true)) : implode(', ', ['cms_girvas', 'garbalo', 'cms', 'girvas', 'admin', 'administrator', 'moder', 'moderator']);
    }
    
    return json_decode($this->getDatabaseEntryValue('users_logins_blacklist'), true);
  }

  /**
   * Получить правило SCP веб-сайта из базы данных
   * 
   * @return string
   */
  public function getSecurityCSP() : string {
    $domainAddress = sprintf('%s://%s', $this->get('SSLIsEnabled') ? 'https' : 'http', $this->get('domain'));
    $domainAliases = is_array($this->get('domainAliases')) ? implode(' ', $this->get('domainAliases')) : '';

    $CSP = $this->exists('SSLCSP') ? $this->get('SSLCSP') : '';
    if (is_array($CSP)) $CSP = implode('; ', $CSP);

    $CSP = str_replace('{SCRIPT_HASH}', $this->CMSCore->CSPScriptsHash, $CSP);
    $CSP = str_replace('{DOMAIN}', $domainAddress, $CSP);
    $CSP = str_replace('{DOMAIN_ALIASES}', $domainAliases, $CSP);
    return str_replace('&quot;', '\'', $CSP);
  }

  /**
   * Получить статус принудительной переадресации на поддомен WWW
   * 
   * @return string
   */
  public function getPermanentRedirectToWWWStatus() : bool {
    $value = $this->existsDatabaseEntryValue('seo_permanent_redirect_www_status') ? $this->getDatabaseEntryValue('seo_permanent_redirect_www_status') : 'off';
    return $value === 'on';
  }

  /**
   * Получить данные конфигурации CMS из базы данных
   *
   * @return mixed
   */
  public function getDatabaseEntryValue(string $name) : mixed
  {
    $CMSCore = $this->CMSCore;
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    $CMSConfigDatabase['dms'] = $CMSConfigDatabase['dms'] ?? CMSDMS::PostgreSQL;

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();

    $queryBuilderStatement = $queryBuilder->statement;
    $queryBuilderStatement->addSelections(['value']);
    $queryBuilderStatement->setClauseFrom();

    $queryBuilderStatementClauseFrom = $queryBuilderStatement->clauseFrom;
    $queryBuilderStatementClauseFrom->addTable('configurations');
    $queryBuilderStatementClauseFrom->assembly();
    $queryBuilderStatement->setClauseWhere();

    $queryBuilderStatementClauseWhere = $queryBuilderStatement->clauseWhere;
    $queryBuilderStatementClauseWhere->addConditionAdaptive([
      'mysql' => '`name` = :name',
      'postgresql' => '"name" = :name'
    ]);
    $queryBuilderStatementClauseWhere->assembly();
    $queryBuilderStatement->assembly();

    try {
      $CMSDatabaseConnector = $CMSCore->databaseConnector;
      $databaseConnection = $CMSDatabaseConnector !== null
        ? $CMSDatabaseConnector->database->connection
        : null;
      
      if ($databaseConnection !== null) {
        $databaseQuery = $databaseConnection->prepare($queryBuilderStatement->assembled);
        $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
        $databaseQuery->execute();

        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['value'] : null;
      }
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return null;
  }

  /**
   * Проверить наличие записи конфигураций CMS в базе данных
   * 
   * @param string $name
   * 
   * @return bool
   */
  public function existsDatabaseEntryValue(string $name) : bool
  {
    $CMSCore = $this->CMSCore;
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    $CMSConfigDatabase['dms'] = $CMSConfigDatabase['dms'] ?? CMSDMS::PostgreSQL;

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();

    $queryBuilderStatement = $queryBuilder->statement;
    $queryBuilderStatement->addSelections(['1']);
    $queryBuilderStatement->setClauseFrom();

    $queryBuilderStatementClauseFrom = $queryBuilderStatement->clauseFrom;
    $queryBuilderStatementClauseFrom->addTable('configurations');
    $queryBuilderStatementClauseFrom->assembly();
    $queryBuilderStatement->setClauseWhere();

    $queryBuilderStatementClauseWhere = $queryBuilderStatement->clauseWhere;
    $queryBuilderStatementClauseWhere->addConditionAdaptive([
      'mysql' => '`name` = :name',
      'postgresql' => '"name" = :name'
    ]);
    $queryBuilderStatementClauseWhere->assembly();
    $queryBuilderStatement->setClauseLimit(1);
    $queryBuilderStatement->assembly();

    try {
      $CMSDatabaseConnector = $CMSCore->databaseConnector;
      $databaseConnection = $CMSDatabaseConnector !== null
        ? $CMSDatabaseConnector->database->connection
        : null;
      
      if ($databaseConnection !== null) {
        $databaseQuery = $databaseConnection->prepare($queryBuilderStatement->assembled);
        $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
        $databaseQuery->execute();

        return $databaseQuery->fetchColumn() ? true : false;
      }
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return false;
  }

  /**
   * Добавить запись конфигураций CMS в базу данных
   * 
   * @param string $name
   * @param string $value
   * 
   * @return bool
   */
  public function insertDatabaseEntryValue(string $name, string $value) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('configurations');
    $queryBuilder->statement->addColumn('name');
    $queryBuilder->statement->addColumn('value');
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector !== null ? $this->CMSCore->databaseConnector->database->connection : null;
      
      if ($databaseConnection !== null) {
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':value', $value, \PDO::PARAM_STR);
        $execute = $databaseQuery->execute();

        return $execute ? true : false;
      }
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return false;
  }

  /**
   * Обновить запись конфигураций CMS в базе данных
   * 
   * @param string $name
   * @param string|int $value
   * 
   * @return mixed
   */
  public function updateDatabaseEntryValue(string $name, string|int $value) : mixed
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('configurations');
    $queryBuilder->statement->setClauseSet();
    $queryBuilder->statement->clauseSet->addColumn('value');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition('name = :name');
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector !== null ? $this->CMSCore->databaseConnector->database->connection : null;
      
      if ($databaseConnection !== null) {
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':value', $value, \PDO::PARAM_STR);
        $execute = $databaseQuery->execute();

        return $execute ? true : false;
      }
    } catch (PDOException $exception) {
      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return false;
  }
  
  /**
   * Получить данные файла-конфигурации CMS
   *
   * @return array
   */
  private function getFileData() : array
  {
    require_once CMS_ROOT_DIRECTORY . '/' . self::FILE_PATH;
    return $configuration ?? [];
  }
  
  /**
   * Назначить отдельного параметра конфигурации CMS
   *
   * @param  mixed $name
   * @param  mixed $value
   * @return void
   */
  public function set(string $name, mixed $value) : void
  {
    $this->data[$name] = $value;
  }

  /**
   * Получить отдельного параметра конфигураций CMS
   *
   * @param  mixed $name Наименование конфигурации
   * @return mixed
   */
  public function get(string $name) : mixed
  {
    return array_key_exists($name, $this->data) ? $this->data[$name] : null;
  }

  /**
   * Проверить наличие отдельного параметра конфигураций CMS
   *
   * @param  string $name Наименование конфигурации
   * @return bool
   */
  public function exists(string $name) : bool
  {
    return array_key_exists($name, $this->data);
  }
  
  /**
   * Объединить данные для конфигураций CMS
   *
   * @param  mixed $data
   * @return void
   */
  private function merge(array $data) : void
  {
    $this->data = array_merge($this->data, $data);
  }
}
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \PDOException as PDOException;
  use \PDO as PDO;

  /**
   * Class Configurator
   */
  final class Configurator {
    const FILE_PATH = 'core/configuration.php';

    public string $metaTitle = '';
    public string $metaDescription = '';
    public array $metaKeywords = [];
    private array $data = [];
    private SystemCore $CMSCore;
    
    /**
     * __construct
     *
     * @param  mixed $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
      $filePath = CMS_ROOT_DIRECTORY . '/' . self::FILE_PATH;

      if (file_exists($filePath)) {
        $this->merge($this->get_file_data());
      }
    }

    /**
     * Назначить заголовок для веб-сайта
     * 
     * @param string $value
     * 
     * @return void
     */
    public function set_meta_title(string $value) : void {
      $this->metaTitle = $value;
    }

    /**
     * Назначить описание для веб-сайта
     * 
     * @param string $value
     * 
     * @return void
     */
    public function set_meta_description(string $value) : void {
      $this->metaDescription = $value;
    }

    /**
     * Назначить ключевые слова для веб-сайта
     * 
     * @param array $values
     * 
     * @return void
     */
    public function set_meta_keywrords(array $values) : void {
      $this->metaKeywords = $values;
    }

    /**
     * Добавить ключевое слово
     * 
     * @param mixed $value
     * 
     * @return void
     */
    public function add_meta_keywrord(mixed $value) : void {
      array_push($this->metaKeywords, $value);
    }

    /**
     * Получить заголовок для веб-сайта
     * 
     * @return string
     */
    public function get_meta_title() : string {
      return $this->metaTitle;
    }

    /**
     * Получить описание для веб-сайта
     * 
     * @return string
     */
    public function get_meta_description() : string {
      return $this->metaDescription;
    }

    /**
     * Получить ключевые слова для веб-сайта
     * 
     * @return array
     */
    public function get_meta_keywords() : array {
      return $this->metaKeywords;
    }

    /**
     * Получить ключевые слова для веб-сайта в формате строки
     * 
     * @return string
     */
    public function get_meta_keywords_imploded() : string {
      return implode(', ', $this->metaKeywords);
    }

    /**
     * Получить ключевые слова для веб-сайта в формате JSON
     * 
     * @return string
     */
    public function get_meta_keywords_json() : string {
      return json_encode($this->metaKeywords);
    }

    /**
     * Получить заголовок для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_title() : string {
      return $this->exists_database_entry_value('base_site_title') ? $this->get_database_entry_value('base_site_title') : $this->CMSCore->get_cms_title();
    }

    /**
     * Получить описание для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_description() : string {
      return $this->exists_database_entry_value('seo_site_description') ? $this->get_database_entry_value('seo_site_description') : sprintf('%s %s developed by www.garbalo.com', $this->CMSCore->get_cms_title(), $this->CMSCore->get_cms_version());
    }

    /**
     * Получить ключевые слова для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_keywords() : string {
      return $this->exists_database_entry_value('seo_site_keywords') ? implode(', ', json_decode($this->get_database_entry_value('seo_site_keywords'), true)) : implode(', ', ['cms girvas', 'empty site', 'karelian developer']);
    }

    /**
     * Получить кодировку веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_charset() : string {
      return $this->exists_database_entry_value('base_site_charset') ? $this->get_database_entry_value('base_site_charset') : 'UTF-8';
    }

    /**
     * Получить временную зону веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_timezone() : string {
      return $this->exists_database_entry_value('base_timezone') ? $this->get_database_entry_value('base_timezone') : 'Europe/Moscow';
    }

    /**
     * Получить максимальный вес загружаемого файла
     * 
     * @return int
     */
    public function get_upload_file_weight_max() : int {
      return $this->exists_database_entry_value('files_upload_file_weight_max') ? (int)$this->get_database_entry_value('files_upload_file_weight_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого изображения
     * 
     * @return int
     */
    public function get_upload_file_image_width_max() : int {
      return $this->exists_database_entry_value('files_upload_file_image_width_max') ? (int)$this->get_database_entry_value('files_upload_file_image_width_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого изображения
     * 
     * @return int
     */
    public function get_upload_file_image_height_max() : int {
      return $this->exists_database_entry_value('files_upload_file_image_height_max') ? (int)$this->get_database_entry_value('files_upload_file_image_height_max') : 0;
    }

    /**
     * Получить максимальный вес загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_weight_max() : int {
      return $this->exists_database_entry_value('files_upload_file_image_avatar_weight_max') ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_weight_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_width_max() : int {
      return $this->exists_database_entry_value('files_upload_file_image_avatar_width_max') ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_width_max') : 0;
    }

    /**
     * Получить максимальную высоту загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_height_max() : int {
      return $this->exists_database_entry_value('files_upload_file_image_avatar_height_max') ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_height_max') : 0;
    }

    /**
     * Получить статус состояния раздела "Записи"
     * 
     * @return string|bool
     */
    public function get_section_entries_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_entries_status')) {
          return $this->get_database_entry_value('base_section_entries_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_entries_status') ? $this->get_database_entry_value('base_section_entries_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Статические страницы"
     * 
     * @return string|bool
     */
    public function get_section_static_pages_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_static_pages_status')) {
          return $this->get_database_entry_value('base_section_static_pages_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_static_pages_status') ? $this->get_database_entry_value('base_section_static_pages_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Модули"
     * 
     * @return string|bool
     */
    public function get_section_modules_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_modules_status')) {
          return $this->get_database_entry_value('base_section_modules_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_modules_status') ? $this->get_database_entry_value('base_section_modules_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Шаблоны"
     * 
     * @return string|bool
     */
    public function get_section_templates_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_templates_status')) {
          return $this->get_database_entry_value('base_section_templates_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_templates_status') ? $this->get_database_entry_value('base_section_templates_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Пользователи"
     * 
     * @return string|bool
     */
    public function get_section_users_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_users_status')) {
          return $this->get_database_entry_value('base_section_users_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_users_status') ? $this->get_database_entry_value('base_section_users_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Медиа"
     * 
     * @return string|bool
     */
    public function get_section_media_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_media_status')) {
          return $this->get_database_entry_value('base_section_media_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_media_status') ? $this->get_database_entry_value('base_section_media_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Фиды"
     * 
     * @return string|bool
     */
    public function get_section_feeds_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_feeds_status')) {
          return $this->get_database_entry_value('base_section_feeds_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_feeds_status') ? $this->get_database_entry_value('base_section_feeds_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Аналитика"
     * 
     * @return string|bool
     */
    public function get_section_analytics_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_section_analytics_status')) {
          return $this->get_database_entry_value('base_section_analytics_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_section_analytics_status') ? $this->get_database_entry_value('base_section_analytics_status') : 'off';
    }

    /**
     * Получить статус состояния технических работ
     * 
     * @return string|bool
     */
    public function get_engineering_works_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('base_engineering_works_status')) {
          return $this->get_database_entry_value('base_engineering_works_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('base_engineering_works_status') ? $this->get_database_entry_value('base_engineering_works_status') : 'off';
    }

    /**
     * Получить причину закрытия сайта на технические работы
     * 
     * @return string
     */
    public function get_engineering_works_text() : string {
      return $this->exists_database_entry_value('base_engineering_works_text') ? (string)$this->get_database_entry_value('base_engineering_works_text') : '';
    }

    /**
     * Получить статус настройки автоматической конвертации изображений
     * 
     * @param bool $isBoolean
     * 
     * @return string
     */
    public function get_auto_convert_file_image_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('files_auto_convert_file_image_status')) {
          return $this->get_database_entry_value('files_auto_convert_file_image_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('files_auto_convert_file_image_status') ? $this->get_database_entry_value('files_auto_convert_file_image_status') : 'off';
    }

    /**
     * Получить расширение для автоматический конвертации изображения
     * 
     * @return string
     */
    public function get_auto_convert_file_image_extension() : string {
      return $this->exists_database_entry_value('files_auto_convert_file_image_extension') ? $this->get_database_entry_value('files_auto_convert_file_image_extension') : '';
    }

    /**
     * Получить статус возможности загрузки аватаров пользователей
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_upload_avatar_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_upload_avatar_status')) {
          return $this->get_database_entry_value('users_upload_avatar_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_upload_avatar_status') ? $this->get_database_entry_value('users_upload_avatar_status') : 'off';
    }

    /**
     * Получить минимальное количество символов для логина пользователя
     * 
     * @return int
     */
    public function get_users_login_length_min() : int {
      return $this->exists_database_entry_value('users_login_length_min') ? (int)$this->get_database_entry_value('users_login_length_min') : 4;
    }

    /**
     * Получить максимальное количество символов для логина пользователя
     * 
     * @return int
     */
    public function get_users_login_length_max() : int {
      return $this->exists_database_entry_value('users_login_length_max') ? (int)$this->get_database_entry_value('users_login_length_max') : 0;
    }

    /**
     * Получить статус возможности редактирования логинов пользователей
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_login_edit_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_login_edit_status')) {
          return $this->get_database_entry_value('users_login_edit_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_login_edit_status') ? $this->get_database_entry_value('users_login_edit_status') : 'off';
    }

    /**
     * Получить статус возможности использования специальных символов в логине
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_login_special_symbols_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_login_special_symbols_status')) {
          return $this->get_database_entry_value('users_login_special_symbols_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_login_special_symbols_status') ? $this->get_database_entry_value('users_login_special_symbols_status') : 'off';
    }

    /**
     * Получить статус учета регистра символов в логине пользователя
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_login_register_accounting_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_login_register_accounting_status')) {
          return $this->get_database_entry_value('users_login_register_accounting_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_login_register_accounting_status') ? $this->get_database_entry_value('users_login_register_accounting_status') : 'off';
    }

    /**
     * Получить минимальное количество символов для пароля пользователя
     * 
     * @return int
     */
    public function get_users_password_length_min() : int {
      return $this->exists_database_entry_value('users_password_length_min') ? (int)$this->get_database_entry_value('users_password_length_min') : 6;
    }

    /**
     * Получить максимальное количество символов для пароля пользователя
     * 
     * @return int
     */
    public function get_users_password_length_max() : int {
      return $this->exists_database_entry_value('users_password_length_max') ? (int)$this->get_database_entry_value('users_password_length_max') : 0;
    }

    /**
     * Получить статус возможности использования специальных символов в пароле
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_password_special_symbols_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_password_special_symbols_status')) {
          return $this->get_database_entry_value('users_password_special_symbols_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_password_special_symbols_status') ? $this->get_database_entry_value('users_password_special_symbols_status') : 'off';
    }

    /**
     * Получить статус использования фильтра для логинов
     * 
     * @param bool $isBoolean
     * 
     * @return string|bool
     */
    public function get_users_logins_blacklist_status(bool $isBoolean = false) : string|bool {
      if ($isBoolean) {
        if ($this->exists_database_entry_value('users_logins_blacklist_status')) {
          return $this->get_database_entry_value('users_logins_blacklist_status') === 'on' ? true : false;
        }

        return false;
      }

      return $this->exists_database_entry_value('users_logins_blacklist_status') ? $this->get_database_entry_value('users_logins_blacklist_status') : 'off';
    }

    /**
     * Получить список заблокированных логинов для пользователей в виде строки
     * 
     * @return string|array
     */
    public function get_users_logins_blacklist(bool $isArray = false) : string|array {
      if (!$isArray) {
        return $this->exists_database_entry_value('users_logins_blacklist') ? implode(', ', json_decode($this->get_database_entry_value('users_logins_blacklist'), true)) : implode(', ', ['cms_girvas', 'garbalo', 'cms', 'girvas', 'admin', 'administrator', 'moder', 'moderator']);
      }
      
      return json_decode($this->get_database_entry_value('users_logins_blacklist'), true);
    }

    /**
     * Получить правило SCP веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_security_scp() : string {
      $domainAddress = sprintf('%s://%s', ($this->get('SSLIsEnabled')) ? 'https' : 'http', $this->get('domain'));
      $domainAliases = (is_array($this->get('domainAliases'))) ? implode(' ', $this->get('domainAliases')) : '';

      $csp = ($this->exists('ssl_csp')) ? $this->get('ssl_csp') : '';
      if (is_array($csp)) $csp = implode('; ', $csp);

      $csp = str_replace('{SCRIPT_HASH}', $this->CMSCore->scp_scripts_hash, $csp);
      $csp = str_replace('{DOMAIN}', $domainAddress, $csp);
      $csp = str_replace('{DOMAIN_ALIASES}', $domainAliases, $csp);
      return str_replace('&quot;', '\'', $csp);
    }

    /**
     * Получить статус принудительной переадресации на поддомен WWW
     * 
     * @return string
     */
    public function get_permanent_redirect_to_www_status() : bool {
      $value = $this->exists_database_entry_value('seo_permanent_redirect_www_status') ? $this->get_database_entry_value('seo_permanent_redirect_www_status') : 'off';
      return $value === 'on' ? true : false;
    }

    /**
     * Получить данные конфигурации CMS из базы данных
     *
     * @return array
     */
    public function get_database_entry_value(string $name) : mixed {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['value']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('configurations');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('name = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = !is_null($this->CMSCore->databaseConnector) ? $this->CMSCore->databaseConnector->database->connection : null;
        
        if (!is_null($databaseConnection)) {
          $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
          $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
          $databaseQuery->execute();

          $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
          return ($result) ? $result['value'] : null;
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
    public function exists_database_entry_value(string $name) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('configurations');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('name = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = (!is_null($this->CMSCore->databaseConnector)) ? $this->CMSCore->databaseConnector->database->connection : null;
        
        if (!is_null($databaseConnection)) {
          $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
          $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
          $databaseQuery->execute();

          return ($databaseQuery->fetchColumn()) ? true : false;
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
    public function insert_database_entry_value(string $name, string $value) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('configurations');
      $queryBuilder->statement->add_column('name');
      $queryBuilder->statement->add_column('value');
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = (!is_null($this->CMSCore->databaseConnector)) ? $this->CMSCore->databaseConnector->database->connection : null;
        
        if (!is_null($databaseConnection)) {
          $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
          $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
          $databaseQuery->bindParam(':value', $value, \PDO::PARAM_STR);
          $execute = $databaseQuery->execute();

          return ($execute) ? true : false;
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
    public function update_database_entry_value(string $name, string|int $value) : mixed {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('configurations');
      $queryBuilder->statement->set_clause_set();
      $queryBuilder->statement->clauseSet->add_column('value');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('name = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = (!is_null($this->CMSCore->databaseConnector)) ? $this->CMSCore->databaseConnector->database->connection : null;
        
        if (!is_null($databaseConnection)) {
          $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
          $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
          $databaseQuery->bindParam(':value', $value, \PDO::PARAM_STR);
          $execute = $databaseQuery->execute();

          return ($execute) ? true : false;
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
    private function get_file_data() : array {
      require_once CMS_ROOT_DIRECTORY . '/' . self::FILE_PATH;
      return isset($configuration) ? $configuration : [];
    }
    
    /**
     * Назначить отдельного параметра конфигурации CMS
     *
     * @param  mixed $name
     * @param  mixed $value
     * @return void
     */
    public function set(string $name, mixed $value) : void {
      $this->data[$name] = $value;
    }

    /**
     * Получить отдельного параметра конфигураций CMS
     *
     * @param  mixed $name Наименование конфигурации
     * @return mixed
     */
    public function get(string $name) : mixed {
      return (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
    }

    /**
     * Проверить наличие отдельного параметра конфигураций CMS
     *
     * @param  string $name Наименование конфигурации
     * @return bool
     */
    public function exists(string $name) : bool {
      return array_key_exists($name, $this->data);
    }
    
    /**
     * Объединить данные для конфигураций CMS
     *
     * @param  mixed $data
     * @return void
     */
    private function merge(array $data) : void {
      $this->data = array_merge($this->data, $data);
    }
  }

}

?>
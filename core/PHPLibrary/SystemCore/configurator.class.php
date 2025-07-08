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

<<<<<<< HEAD
    public string $meta_title = '';
    public string $meta_description = '';
    public array $meta_keywords = [];
    private array $data = [];
    private SystemCore $system_core;
    
    /**
     * __construct
     *
     * @param  mixed $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;

      if (file_exists(sprintf('%s/%s', CMS_ROOT_DIRECTORY, self::FILE_PATH))) {
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
      $this->meta_title = $value;
    }

    /**
     * Назначить описание для веб-сайта
     * 
     * @param string $value
     * 
     * @return void
     */
    public function set_meta_description(string $value) : void {
      $this->meta_description = $value;
    }

    /**
     * Назначить ключевые слова для веб-сайта
     * 
     * @param array $values
     * 
     * @return void
     */
    public function set_meta_keywrords(array $values) : void {
      $this->meta_keywords = $values;
    }

    /**
     * Добавить ключевое слово
     * 
     * @param mixed $value
     * 
     * @return void
     */
    public function add_meta_keywrord(mixed $value) : void {
      array_push($this->meta_keywords, $value);
    }

    /**
     * Получить заголовок для веб-сайта
     * 
     * @return string
     */
    public function get_meta_title() : string {
      return $this->meta_title;
    }

    /**
     * Получить описание для веб-сайта
     * 
     * @return string
     */
    public function get_meta_description() : string {
      return $this->meta_description;
    }

    /**
     * Получить ключевые слова для веб-сайта
     * 
     * @return array
     */
    public function get_meta_keywords() : array {
      return $this->meta_keywords;
    }

    /**
     * Получить ключевые слова для веб-сайта в формате строки
     * 
     * @return string
     */
    public function get_meta_keywords_imploded() : string {
      return implode(', ', $this->meta_keywords);
    }

    /**
     * Получить ключевые слова для веб-сайта в формате JSON
     * 
     * @return string
     */
    public function get_meta_keywords_json() : string {
      return json_encode($this->meta_keywords);
    }

    /**
     * Получить заголовок для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_title() : string {
      return ($this->exists_database_entry_value('base_site_title')) ? $this->get_database_entry_value('base_site_title') : $this->system_core->get_cms_title();
    }

    /**
     * Получить описание для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_description() : string {
      return ($this->exists_database_entry_value('seo_site_description')) ? $this->get_database_entry_value('seo_site_description') : sprintf('%s %s developed by www.garbalo.com', $this->system_core->get_cms_title(), $this->system_core->get_cms_version());
    }

    /**
     * Получить ключевые слова для веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_keywords() : string {
      return ($this->exists_database_entry_value('seo_site_keywords')) ? implode(', ', json_decode($this->get_database_entry_value('seo_site_keywords'), true)) : implode(', ', ['cms girvas', 'garbalo', 'empty site']);
    }

    /**
     * Получить кодировку веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_charset() : string {
      return ($this->exists_database_entry_value('base_site_charset')) ? $this->get_database_entry_value('base_site_charset') : 'UTF-8';
    }

    /**
     * Получить временную зону веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_site_timezone() : string {
      return ($this->exists_database_entry_value('base_timezone')) ? $this->get_database_entry_value('base_timezone') : 'Europe/Moscow';
    }

    /**
     * Получить максимальный вес загружаемого файла
     * 
     * @return int
     */
    public function get_upload_file_weight_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_weight_max')) ? (int)$this->get_database_entry_value('files_upload_file_weight_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого изображения
     * 
     * @return int
     */
    public function get_upload_file_image_width_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_image_width_max')) ? (int)$this->get_database_entry_value('files_upload_file_image_width_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого изображения
     * 
     * @return int
     */
    public function get_upload_file_image_height_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_image_height_max')) ? (int)$this->get_database_entry_value('files_upload_file_image_height_max') : 0;
    }

    /**
     * Получить максимальный вес загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_weight_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_image_avatar_weight_max')) ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_weight_max') : 0;
    }

    /**
     * Получить максимальную ширину загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_width_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_image_avatar_width_max')) ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_width_max') : 0;
    }

    /**
     * Получить максимальную высоту загружаемого аватара
     * 
     * @return int
     */
    public function get_upload_file_image_avatar_height_max() : int {
      return ($this->exists_database_entry_value('files_upload_file_image_avatar_height_max')) ? (int)$this->get_database_entry_value('files_upload_file_image_avatar_height_max') : 0;
    }

    /**
     * Получить статус состояния раздела "Записи"
     * 
     * @return string|bool
     */
    public function get_section_entries_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_entries_status')) {
          return ($this->get_database_entry_value('base_section_entries_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_entries_status')) ? $this->get_database_entry_value('base_section_entries_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Статические страницы"
     * 
     * @return string|bool
     */
    public function get_section_static_pages_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_static_pages_status')) {
          return ($this->get_database_entry_value('base_section_static_pages_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_static_pages_status')) ? $this->get_database_entry_value('base_section_static_pages_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Модули"
     * 
     * @return string|bool
     */
    public function get_section_modules_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_modules_status')) {
          return ($this->get_database_entry_value('base_section_modules_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_modules_status')) ? $this->get_database_entry_value('base_section_modules_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Шаблоны"
     * 
     * @return string|bool
     */
    public function get_section_templates_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_templates_status')) {
          return ($this->get_database_entry_value('base_section_templates_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_templates_status')) ? $this->get_database_entry_value('base_section_templates_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Пользователи"
     * 
     * @return string|bool
     */
    public function get_section_users_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_users_status')) {
          return ($this->get_database_entry_value('base_section_users_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_users_status')) ? $this->get_database_entry_value('base_section_users_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Медиа"
     * 
     * @return string|bool
     */
    public function get_section_media_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_media_status')) {
          return ($this->get_database_entry_value('base_section_media_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_media_status')) ? $this->get_database_entry_value('base_section_media_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Фиды"
     * 
     * @return string|bool
     */
    public function get_section_feeds_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_feeds_status')) {
          return ($this->get_database_entry_value('base_section_feeds_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_feeds_status')) ? $this->get_database_entry_value('base_section_feeds_status') : 'off';
    }

    /**
     * Получить статус состояния раздела "Аналитика"
     * 
     * @return string|bool
     */
    public function get_section_analytics_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_section_analytics_status')) {
          return ($this->get_database_entry_value('base_section_analytics_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_section_analytics_status')) ? $this->get_database_entry_value('base_section_analytics_status') : 'off';
    }

    /**
     * Получить статус состояния технических работ
     * 
     * @return string|bool
     */
    public function get_engineering_works_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('base_engineering_works_status')) {
          return ($this->get_database_entry_value('base_engineering_works_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('base_engineering_works_status')) ? $this->get_database_entry_value('base_engineering_works_status') : 'off';
    }

    /**
     * Получить причину закрытия сайта на технические работы
     * 
     * @return string
     */
    public function get_engineering_works_text() : string {
      return ($this->exists_database_entry_value('base_engineering_works_text')) ? (string)$this->get_database_entry_value('base_engineering_works_text') : '';
    }

    /**
     * Получить статус настройки автоматической конвертации изображений
     * 
     * @param bool $is_bool
     * 
     * @return string
     */
    public function get_auto_convert_file_image_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('files_auto_convert_file_image_status')) {
          return ($this->get_database_entry_value('files_auto_convert_file_image_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('files_auto_convert_file_image_status')) ? $this->get_database_entry_value('files_auto_convert_file_image_status') : 'off';
    }

    /**
     * Получить расширение для автоматический конвертации изображения
     * 
     * @return string
     */
    public function get_auto_convert_file_image_extension() : string {
      return ($this->exists_database_entry_value('files_auto_convert_file_image_extension')) ? $this->get_database_entry_value('files_auto_convert_file_image_extension') : '';
    }

    /**
     * Получить статус возможности загрузки аватаров пользователей
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_upload_avatar_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_upload_avatar_status')) {
          return ($this->get_database_entry_value('users_upload_avatar_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_upload_avatar_status')) ? $this->get_database_entry_value('users_upload_avatar_status') : 'off';
    }

    /**
     * Получить минимальное количество символов для логина пользователя
     * 
     * @return int
     */
    public function get_users_login_length_min() : int {
      return ($this->exists_database_entry_value('users_login_length_min')) ? (int)$this->get_database_entry_value('users_login_length_min') : 4;
    }

    /**
     * Получить максимальное количество символов для логина пользователя
     * 
     * @return int
     */
    public function get_users_login_length_max() : int {
      return ($this->exists_database_entry_value('users_login_length_max')) ? (int)$this->get_database_entry_value('users_login_length_max') : 0;
    }

    /**
     * Получить статус возможности редактирования логинов пользователей
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_login_edit_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_login_edit_status')) {
          return ($this->get_database_entry_value('users_login_edit_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_login_edit_status')) ? $this->get_database_entry_value('users_login_edit_status') : 'off';
    }

    /**
     * Получить статус возможности использования специальных символов в логине
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_login_special_symbols_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_login_special_symbols_status')) {
          return ($this->get_database_entry_value('users_login_special_symbols_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_login_special_symbols_status')) ? $this->get_database_entry_value('users_login_special_symbols_status') : 'off';
    }

    /**
     * Получить статус учета регистра символов в логине пользователя
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_login_register_accounting_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_login_register_accounting_status')) {
          return ($this->get_database_entry_value('users_login_register_accounting_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_login_register_accounting_status')) ? $this->get_database_entry_value('users_login_register_accounting_status') : 'off';
    }

    /**
     * Получить минимальное количество символов для пароля пользователя
     * 
     * @return int
     */
    public function get_users_password_length_min() : int {
      return ($this->exists_database_entry_value('users_password_length_min')) ? (int)$this->get_database_entry_value('users_password_length_min') : 6;
    }

    /**
     * Получить максимальное количество символов для пароля пользователя
     * 
     * @return int
     */
    public function get_users_password_length_max() : int {
      return ($this->exists_database_entry_value('users_password_length_max')) ? (int)$this->get_database_entry_value('users_password_length_max') : 0;
    }

    /**
     * Получить статус возможности использования специальных символов в пароле
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_password_special_symbols_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_password_special_symbols_status')) {
          return ($this->get_database_entry_value('users_password_special_symbols_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_password_special_symbols_status')) ? $this->get_database_entry_value('users_password_special_symbols_status') : 'off';
    }

    /**
     * Получить статус использования фильтра для логинов
     * 
     * @param bool $is_bool
     * 
     * @return string|bool
     */
    public function get_users_logins_blacklist_status(bool $is_bool = false) : string|bool {
      if ($is_bool) {
        if ($this->exists_database_entry_value('users_logins_blacklist_status')) {
          return ($this->get_database_entry_value('users_logins_blacklist_status') == 'on') ? true : false;
        }

        return false;
      }

      return ($this->exists_database_entry_value('users_logins_blacklist_status')) ? $this->get_database_entry_value('users_logins_blacklist_status') : 'off';
    }

    /**
     * Получить список заблокированных логинов для пользователей в виде строки
     * 
     * @return string|array
     */
    public function get_users_logins_blacklist(bool $is_array = false) : string|array {
      if (!$is_array) {
        return ($this->exists_database_entry_value('users_logins_blacklist')) ? implode(', ', json_decode($this->get_database_entry_value('users_logins_blacklist'), true)) : implode(', ', ['cms_girvas', 'garbalo', 'cms', 'girvas', 'admin', 'administrator', 'moder', 'moderator']);
      }
      
      return json_decode($this->get_database_entry_value('users_logins_blacklist'), true);
    }

    /**
     * Получить правило SCP веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_security_scp() : string {
      $domain_address = sprintf('%s://%s', ($this->get('ssl_is_enabled')) ? 'https' : 'http', $this->get('domain'));
      $domain_aliases = (is_array($this->get('domain_aliases'))) ? implode(' ', $this->get('domain_aliases')) : '';

      $csp = ($this->exists('ssl_csp')) ? $this->get('ssl_csp') : '';
      if (is_array($csp)) $csp = implode('; ', $csp);

      $csp = str_replace('{SCRIPT_HASH}', $this->system_core->scp_scripts_hash, $csp);
      $csp = str_replace('{DOMAIN}', $domain_address, $csp);
      $csp = str_replace('{DOMAIN_ALIASES}', $domain_aliases, $csp);
      return str_replace('&quot;', '\'', $csp);
    }

    /**
     * Получить статус принудительной переадресации на поддомен WWW
     * 
     * @return string
     */
    public function get_permanent_redirect_to_www_status() : bool {
      $value = ($this->exists_database_entry_value('seo_permanent_redirect_www_status')) ? $this->get_database_entry_value('seo_permanent_redirect_www_status') : 'off';
      return ($value == 'on') ? true : false;
    }

    /**
     * Получить данные конфигурации CMS из базы данных
     *
     * @return array
     */
    public function get_database_entry_value(string $name) : mixed {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['value']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('configurations');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      try {
        $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
        
        if (!is_null($database_connection)) {
          $database_query = $database_connection->prepare($query_builder->statement->assembled);
          $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
          $database_query->execute();

          $result = $database_query->fetch(\PDO::FETCH_ASSOC);
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('configurations');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      try {
        $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
        
        if (!is_null($database_connection)) {
          $database_query = $database_connection->prepare($query_builder->statement->assembled);
          $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
          $database_query->execute();

          return ($database_query->fetchColumn()) ? true : false;
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('configurations');
      $query_builder->statement->add_column('name');
      $query_builder->statement->add_column('value');
      $query_builder->statement->assembly();

      try {
        $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
        
        if (!is_null($database_connection)) {
          $database_query = $database_connection->prepare($query_builder->statement->assembled);
          $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
          $database_query->bindParam(':value', $value, \PDO::PARAM_STR);
          $execute = $database_query->execute();

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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('configurations');
      $query_builder->statement->set_clause_set();
      $query_builder->statement->clause_set->add_column('value');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('name = :name');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      try {
        $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
        
        if (!is_null($database_connection)) {
          $database_query = $database_connection->prepare($query_builder->statement->assembled);
          $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
          $database_query->bindParam(':value', $value, \PDO::PARAM_STR);
          $execute = $database_query->execute();

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
      require_once(sprintf('%s/%s', CMS_ROOT_DIRECTORY, self::FILE_PATH));
      return (isset($configuration)) ? $configuration : [];
    }
    
    /**
     * Назначить отдельного параметра конфигурации CMS
     *
     * @param  mixed $configuration_name
     * @param  mixed $configuration_value
     * @return void
     */
    public function set(string $configuration_name, mixed $configuration_value) : void {
      $this->data[$configuration_name] = $configuration_value;
    }

    /**
     * Получить отдельного параметра конфигураций CMS
     *
     * @param  mixed $configuration_name Наименование конфигурации
     * @return mixed
     */
    public function get(string $configuration_name) : mixed {
      return (array_key_exists($configuration_name, $this->data)) ? $this->data[$configuration_name] : null;
    }

    /**
     * Проверить наличие отдельного параметра конфигураций CMS
     *
     * @param  string $configuration_name Наименование конфигурации
     * @return bool
     */
    public function exists(string $configuration_name) : bool {
      return array_key_exists($configuration_name, $this->data);
    }
    
    /**
     * Объединить данные для конфигураций CMS
     *
     * @param  mixed $data
     * @return void
     */
    private function merge(array $data) : void {
      $this->data = array_merge($this->data, $data);
=======
    if (file_exists($filePath)) {
      $this->merge($this->getFileData());
>>>>>>> develop
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
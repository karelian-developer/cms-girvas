<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\SystemCore as SystemCore;

  /**
   * Class Configurator
   */
  final class Configurator {
    const FILE_PATH = 'core/configuration.php';

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
     * Получить правило SCP веб-сайта из базы данных
     * 
     * @return string
     */
    public function get_security_scp() : string {
      $domain_address = sprintf('%s://%s', ($this->get('ssl_is_enabled')) ? 'https' : 'http', $this->get('domain'));
      
      $csp = ($this->exists('ssl_csp')) ? $this->get('ssl_csp') : '';
      if (is_array($csp)) $csp = implode('; ', $csp);

      $csp = str_replace('{SCRIPT_HASH}', $this->system_core->scp_scripts_hash, $csp);
      $csp = str_replace('{DOMAIN}', $domain_address, $csp);
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

      $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
      
      if (!is_null($database_connection)) {
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->execute();

        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? $result['value'] : null;
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

      $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
      
      if (!is_null($database_connection)) {
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->execute();

        return ($database_query->fetchColumn()) ? true : false;
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

      $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
      
      if (!is_null($database_connection)) {
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->bindParam(':value', $value, \PDO::PARAM_STR);
        $execute = $database_query->execute();

        return ($execute) ? true : false;
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

      $database_connection = (!is_null($this->system_core->database_connector)) ? $this->system_core->database_connector->database->connection : null;
      
      if (!is_null($database_connection)) {
        $database_query = $database_connection->prepare($query_builder->statement->assembled);
        $database_query->bindParam(':name', $name, \PDO::PARAM_STR);
        $database_query->bindParam(':value', $value, \PDO::PARAM_STR);
        $execute = $database_query->execute();

        return ($execute) ? true : false;
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
    }
  }

}

?>
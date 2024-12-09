<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\SystemCore as SystemCore;

  final class Locale {
    public SystemCore $system_core;
    public const DEFAULT_LOCALE_NAME = 'en_US';
    private string $name;
    private string $core_path;
    private string $data_path;

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param string $name
     * @param string $dir
     */
    public function __construct(SystemCore $system_core, string $name, string $dir = 'base') {
      $this->system_core = $system_core;
      $this->set_name($name);

      /** @var string Абсолютный путь до корня локализации */
      $locale_core_path = sprintf('%s/locales/%s', $system_core->get_cms_path(), $name);
      /** @var string Абсолютный путь до корня категории локализации */
      $locale_data_path = sprintf('%s/locales/%s/%s', $system_core->get_cms_path(), $name, $dir);
      
      $this->set_core_path($locale_core_path);
      $this->set_data_path($locale_data_path);
    }

    /**
     * Получить URL до иконки локализации
     * 
     * @return string
     */
    public function get_icon_url() : string {
      return sprintf('/locales/%s/icons/16.png', $this->get_name());
    }
  
    /**
     * Установить наименование локализации
     * 
     * @param string $value
     * 
     * @return void
     */
    private function set_name(string $value) : void {
      $this->name = $value;
    }
  
    /**
     * Получить наименование локализации
     * 
     * @return string
     */
    public function get_name() : string {
      return $this->name;
    }
    
    /**
     * Назначить путь до локализации
     *
     * @param  string $path Путь до локализации
     * @return void
     */
    public function set_core_path(string $path) : void {
      $this->core_path = $path;
    }
    
    /**
     * Получить путь до локализации
     *
     * @return string
     */
    public function get_core_path() : string {
      return $this->core_path;
    }
    
    /**
     * Назначить путь до данных локализации
     *
     * @param  string $path Путь до локализации
     * @return void
     */
    public function set_data_path(string $path) : void {
      $this->data_path = $path;
    }
    
    /**
     * Получить путь до данных локализации
     *
     * @return string
     */
    public function get_data_path() : string {
      return $this->data_path;
    }

    /**
     * Получить заголовок локализации
     * 
     * @return string
     */
    public function get_title() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['title'])) ? $metadata['title'] : '';
    }

    /**
     * Получить имя автора локализации
     * 
     * @return string
     */
    public function get_author_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['authorName'])) ? $metadata['authorName'] : '';
    }

    /**
     * Получить код локализации стандарта ISO-639-1
     * 
     * @return string
     */
    public function get_iso_639_1() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['iso639_1'])) ? $metadata['iso639_1'] : '';
    }

    /**
     * Получить код локализации стандарта ISO-639-2
     * 
     * @return string
     */
    public function get_iso_639_2() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['iso639_2'])) ? $metadata['iso639_2'] : '';
    }

    /**
     * Проверить наличие файла с данными локализации в формате JSON
     * 
     * @return bool
     */
    public function exists_file_data_json() : bool {
      return file_exists($this->get_file_data_json_path());
    }

    /**
     * Получить абсолютный путь до файла с данными локализации в формате JSON
     * 
     * @return string
     */
    public function get_file_data_json_path() : string {
      return sprintf('%s/data.json', $this->get_data_path());
    }

    /**
     * Проверить наличие файла с реестром локализации в формате JSON
     * 
     * @return bool
     */
    public function exists_file_registry_json() : bool {
      return file_exists($this->get_file_data_json_path());
    }

    /**
     * Получить абсолютный путь до файла с реестром локализации в формате JSON
     * 
     * @return string
     */
    public function get_file_registry_json_path() : string {
      return sprintf('%s/registry.json', $this->get_data_path());
    }

    /**
     * Получить данные локализации
     * 
     * @return array
     */
    public function get_data() : array|bool|null {
      $file_path = $this->get_file_data_json_path();
      $file_content = (file_exists($file_path)) ? file_get_contents($file_path) : '{}';

      return json_decode($file_content, true);
    }

    /**
     * Получить данные реестра локализации
     * 
     * @return array
     */
    public function get_registry_array() : array {
      $file_path = $this->get_file_registry_json_path();
      $file_content = (file_exists($file_path)) ? file_get_contents($file_path) : '{}';

      return json_decode($file_content, true);
    }

    /**
     * Получить одиночное значение из данных локализации
     * 
     * @param string $key
     * 
     * @return string
     */
    public function get_single_value_by_key(string $key) : string {
      $locale_data = $this->get_data();
      return (isset($locale_data[$key])) ? $locale_data[$key] : '[ ??? ]';
    }

    /**
     * Проверить наличие файла с метаданными локализации в формате JSON
     * 
     * @return bool
     */
    public function exists_file_metadata_json() : bool {
      return file_exists($this->get_file_metadata_json_path());
    }

    /**
     * Получить абсолютный путь до файла с метаданными локализации в формате JSON
     * 
     * @return string
     */
    public function get_file_metadata_json_path() : string {
      return sprintf('%s/metadata.json', $this->get_core_path());
    }

    /**
     * Получить метаданные локализации
     * 
     * @return array
     */
    public function get_metadata() : array|null {
      $file_path = $this->get_file_metadata_json_path();
      $file_content = file_get_contents($file_path);

      return json_decode($file_content, true);
    }
  }
}

?>
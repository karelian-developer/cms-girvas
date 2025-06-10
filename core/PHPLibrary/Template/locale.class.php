<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Template as Template;

  final class Locale {
    public SystemCore $CMSCore;
    public Template $theme;
    private string $name;
    private string $corePath;
    private string $dataPath;

    /**
     * __construct
     * 
     * @param Template $theme
     * @param string $name
     */
    public function __construct(Template $theme, string $name) {
      $this->CMSCore = $theme->CMSCore;
      $this->theme = $theme;
      $this->set_name($name);

      $corePath = sprintf('%s/templates/%s/locales/%s', $this->CMSCore->get_cms_path(), $theme->get_name(), $name);
      $dataPath = sprintf('%s/templates/%s/locales/%s', $this->CMSCore->get_cms_path(), $theme->get_name(), $name);
      $this->set_core_path($corePath);
      $this->set_data_path($dataPath);
    }

    /**
     * Получить URL до иконки локализации
     * 
     * @return string
     */
    public function get_icon_url() : string {
      return sprintf('/templates/%s/locales/%s/icons/16.png', $this->theme->get_name(), $this->get_name());
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
      $filePath = $this->get_file_data_json_path();
      $fileContent = (file_exists($filePath)) ? file_get_contents($filePath) : '{}';

      return json_decode($fileContent, true);
    }

    /**
     * Получить значение элемента локализации
     * 
     * @param array $data
     * @param string $name
     * 
     * @return array
     */
    public static function get_data_value(array $data, string $name) : string {
      if (array_key_exists($name, $data)) {
        return $data[$name];
      }

      return sprintf('<span style="background-color: red;color: white;">[%s]</span>', $name);
    }

    /**
     * Получить данные реестра локализации
     * 
     * @return array
     */
    public function get_registry_array() : array {
      $filePath = $this->get_file_registry_json_path();
      $fileContent = (file_exists($filePath)) ? file_get_contents($filePath) : '{}';

      return json_decode($fileContent, true);
    }

    /**
     * Получить одиночное значение из данных локализации
     * 
     * @param string $key
     * 
     * @return string
     */
    public function get_single_value_by_key(string $key) : string {
      $data = $this->get_data();
      return (isset($data[$key])) ? $data[$key] : '[ ??? ]';
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
      $filePath = $this->get_file_metadata_json_path();
      $fileContent = file_get_contents($filePath);

      return json_decode($fileContent, true);
    }
  }
}

?>
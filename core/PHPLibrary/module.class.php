<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  use \core\PHPLibrary\Module\Locale as ModuleLocale;

  /**
   * Модуль CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.2
   */
  #[\AllowDynamicProperties]
  final class Module {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $system_core = null;
    /** @var ModuleLocale|null Объект локализации */
    public ModuleLocale|null $locale = null;
    /** @var string|null Техническое наименование модуля */
    private string|null $name = null;
    /** @var string|null Абсолютный путь до файлов модуля */
    private string|null $path = null;
    /** @var string|null URL до файлов модуля */
    private string|null $url = null;
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return void
     */
    public function __construct(SystemCore $system_core, string $name) {
      $this->system_core = $system_core;
      $this->set_name($name);
      
      $cms_base_locale_setted_name = $system_core->configurator->get_database_entry_value('base_locale');
      $url_base_locale_setted_name = $system_core->urlp->get_param('locale');
      $cookie_base_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;

      $cms_base_locale_name = (!is_null($url_base_locale_setted_name)) ? $url_base_locale_setted_name : $cookie_base_locale_setted_name;
      $cms_base_locale_name = (!is_null($cms_base_locale_name)) ? $cms_base_locale_name : $cms_base_locale_setted_name;
      $cms_base_locale_name = (is_null($cms_base_locale_name)) ? 'en_US' : $cms_base_locale_name;
      $cms_base_locale = new ModuleLocale($this, $cms_base_locale_name);
      if (!$cms_base_locale->exists_file_data_json()) {
        $cms_base_locale = new ModuleLocale($this, $cms_base_locale_name);
      }

      $this->locale = $cms_base_locale;

      $module_path = sprintf('%s/modules/%s', CMS_ROOT_DIRECTORY, $name);
      $module_url = sprintf('modules/%s', CMS_ROOT_DIRECTORY, $name);

      $this->set_path($module_path);
      $this->set_url($module_url);
    }
    
    /**
     * Назначение абсолютного пути до файлов модуля
     *
     * @param  mixed $path
     * @return void
     */
    private function set_path(string $path) : void {
      $this->path = $path;
    }
    
    /**
     * Назначить URL до модуля
     *
     * @param  string Путь до шаблона
     * @return void
     */
    public function set_url(string $url) : void {
      $this->url = $url;
    }

    /**
     * Получить URL до изображения-превью модуля
     * 
     * @return string
     */
    public function get_preview_url() : string {
      return sprintf('/%s/preview.png', $this->get_url());
    }

    /**
     * Получить абсолютный путь до скриншотов модуля
     * 
     * @return string
     */
    public function get_screenshots_path() : string {
      return sprintf('%s/screenshots', $this->get_path());
    }

    /**
     * Получить URL до скриншотов модуля
     * 
     * @return string
     */
    public function get_screenshots_url() : string {
      return sprintf('/%s/screenshots', $this->get_url());
    }

    /**
     * Получить массив со скриншотами модуля
     * 
     * @return array
     */
    public function get_screenshots_array() : array {
      $screenshots_path = $this->get_screenshots_path();
      return (file_exists($screenshots_path)) ? array_diff(scandir($screenshots_path), ['.', '..']) : [];
    }
    
    /**
     * Получение абсолютного пути до файлов модуля
     *
     * @return string
     */
    public function get_path() : string {
      return $this->path;
    }
    
    /**
     * Получить URL до модуля
     *
     * @return string
     */
    public function get_url() : string {
      return $this->url;
    }
    
    /**
     * Получение технического имени модуля
     *
     * @return string
     */
    public function get_name() : string {
      return $this->name;
    }
    
    /**
     * Получение заголовка модуля (из метаданных)
     *
     * @return string
     */
    public function get_title() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['title'])) ? $metadata['title'] : '';
    }
    
    /**
     * Получение описания модуля (из метаданных)
     *
     * @return string
     */
    public function get_description() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['description'])) ? $metadata['description'] : '';
    }
    
    /**
     * Получение имени автора модуля (из метаданных)
     *
     * @return string
     */
    public function get_author_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['authorName'])) ? $metadata['authorName'] : '';
    }
    
    /**
     * Назначение технического имени модуля
     *
     * @param  string $value
     * @return void
     */
    protected function set_name(string $value) : void {
      $this->name = $value;
    }
    
    /**
     * Подключние файла ядра модуля
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return bool
     */
    public static function connect_core(SystemCore $system_core, string $name) : bool {
      $module = new Module($system_core, $name);
      
      if ($module->exists_core_file()) {
        require_once($module->get_core_path());
        $core_class = $module->get_core_class();
        $system_core->modules[$name] = new $core_class($system_core, $module);

        return true;
      }

      return false;
    }
    
    /**
     * Проверить включение модуля
     *
     * @return bool
     */
    public function is_enabled() : bool {
      $file_path = sprintf('%s/modules/%s/enabled', CMS_ROOT_DIRECTORY, $this->get_name());
      return file_exists($file_path);
    }

    /**
     * Проверить установку модуля
     * 
     * @return bool
     */
    public function is_installed() : bool {
      $file_path = sprintf('%s/modules/%s/installed', CMS_ROOT_DIRECTORY, $this->get_name());
      return file_exists($file_path);
    }

    /**
     * Установить модуля
     * 
     * @return bool
     */
    public function install() : bool {
      if (!$this->is_installed()) {
        $file_path = sprintf('%s/modules/%s/installed', CMS_ROOT_DIRECTORY, $this->get_name());
        $file = fopen($file_path, 'w');

        return true;
      }

      return false;
    }

    /**
     * Удаленить модуль
     * 
     * @return bool
     */
    public function delete() : bool {
      if (!$this->is_installed()) {
        $path = sprintf('%s/modules/%s', CMS_ROOT_DIRECTORY, $this->get_name());
        $this->system_core::recursive_files_remove($path);

        return true;
      }

      return false;
    }

    /**
     * Включить модуль
     * 
     * @return bool
     */
    public function enable() : bool {
      if (!$this->is_enabled()) {
        $file_path = sprintf('%s/modules/%s/enabled', CMS_ROOT_DIRECTORY, $this->get_name());
        $file = fopen($file_path, 'w');

        return true;
      }

      return false;
    }

    /**
     * Отключить модуль
     * 
     * @return bool
     */
    public function disable() : bool {
      if ($this->is_enabled()) {
        $file_path = sprintf('%s/modules/%s/enabled', CMS_ROOT_DIRECTORY, $this->get_name());
        unlink($file_path);

        return true;
      }

      return false;
    }
    
    /**
     * Проверка наличия файла ядра модуля
     *
     * @return bool
     */
    public function exists_core_file() : bool {
      $file_path = sprintf('%s/modules/%s/core.class.php', CMS_ROOT_DIRECTORY, $this->get_name());
      return file_exists($file_path);
    }
    
    /**
     * Получение абсолютного пути до файла ядра модуля
     *
     * @return string
     */
    public function get_core_path() : string {
      return sprintf('%s/core.class.php', $this->get_path());
    }
    
    /**
     * Получение пространства имен для ядра модуля
     *
     * @return string
     */
    private function get_core_class() : string {
      return sprintf('\\modules\\%s\\Core', $this->get_name());
    }
    
    /**
     * Получение даты создания файла ядра модуля (в UNIX-формате)
     *
     * @return int
     */
    public function get_core_created_unix_timestamp() : int {
      $path = $this->get_core_path();
      return filectime($path);
    }
    
    /**
     * Проверка наличия файла с метаданными модуля
     *
     * @return bool
     */
    public function exists_file_metadata_json() : bool {
      return file_exists($this->get_file_metadata_json_path());
    }
    
    /**
     * Получение абсолютного пути до файла с метаданными модуля
     *
     * @return string
     */
    public function get_file_metadata_json_path() : string {
      return sprintf('%s/metadata.json', $this->get_path());
    }
    
    /**
     * Получение массива метаданных модуля
     *
     * @return array
     */
    public function get_metadata() : array|null {
      $file_path = $this->get_file_metadata_json_path();
      $file_content = file_get_contents($file_path);

      return json_decode($file_content, true);
    }

    /**
     * Получить абсолютный путь до файла README.md
     * 
     * @return string
     */
    public function get_file_readme_md_path() : string {
      return sprintf('%s/README.md', $this->get_path());
    }

    /**
     * Получить содержимое файла README.md
     * 
     * @return string
     */
    public function get_content_file_readme_md() : string {
      return ($this->exists_file_readme_md()) ? file_get_contents($this->get_file_readme_md_path()) : '';
    }

    /**
     * Проверить наличие файла README.md
     * 
     * @return bool
     */
    public function exists_file_readme_md() : bool {
      return file_exists($this->get_file_readme_md_path());
    }
  }
}

?>
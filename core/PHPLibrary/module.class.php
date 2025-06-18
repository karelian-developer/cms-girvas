<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  use \core\PHPLibrary\Module\Locale as ModuleLocale;
  use \core\PHPLibrary\Module\EnumMetadata as ModuleEnumMetadata;
  use \core\PHPLibrary\Module\EnumWeight as ModuleEnumWeight;

  /**
   * Модуль CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.2
   */
  #[\AllowDynamicProperties]
  final class Module {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $CMSCore = null;
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
     * @param  SystemCore $CMSCore
     * @param  string $name
     * @return void
     */
    public function __construct(SystemCore $CMSCore, string $name) {
      $this->CMSCore = $CMSCore;
      $this->set_name($name);
      
      $CMSBaseLocaleSettedName = $CMSCore->configurator->get_database_entry_value('base_locale');
      $URLBaseLocaleSettedName = $CMSCore->urlp->get_param('locale');
      $CookieBaseLocaleSettedName = $_COOKIE['locale'] ?? null;

      $CMSBaseLocaleName = $URLBaseLocaleSettedName ?? $CookieBaseLocaleSettedName;
      $CMSBaseLocaleName = $CMSBaseLocaleName ?? $CMSBaseLocaleSettedName;
      $CMSBaseLocaleName = $CMSBaseLocaleName ?? 'en_US';
      $CMSBaseLocale = new ModuleLocale($this, $CMSBaseLocaleName);

      if (!$CMSBaseLocale->exists_file_data_json()) {
        $CMSBaseLocale = new ModuleLocale($this, $CMSBaseLocaleName);
      }

      $this->locale = $CMSBaseLocale;

      $modulePath = CMS_ROOT_DIRECTORY . '/modules/' . $name;
      $moduleURL = 'modules/' . $name;

      $this->set_path($modulePath);
      $this->set_url($moduleURL);
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
      return '/' . $this->get_url() . '/preview.png';
    }

    /**
     * Получить абсолютный путь до скриншотов модуля
     * 
     * @return string
     */
    public function get_screenshots_path() : string {
      return $this->get_path() . '/screenshots';
    }

    /**
     * Получить URL до скриншотов модуля
     * 
     * @return string
     */
    public function get_screenshots_url() : string {
      return '/' . $this->get_url() . '/screenshots';
    }

    /**
     * Получить массив со скриншотами модуля
     * 
     * @return array
     */
    public function get_screenshots_array() : array {
      $path = $this->get_screenshots_path();
      return (file_exists($path)) ? array_diff(scandir($path), ['.', '..']) : [];
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
     * Получить вес модуля в байтах
     * 
     * @param ModuleEnumWeight $enumWeight
     * 
     * @return float
     */
    public static function get_weight(Module $module, ModuleEnumWeight $enumWeight) : float {
      $modulePath = $module->get_path();
      $totalWeight = 0;
      
      $directoryFiles = array_diff(scandir($modulePath), ['.', '..']);
      $callbackFunction = function(string $path, array $files, $callback, &$totalWeight) : void {
        foreach ($files as $file) {
          $filePath = $path . '/' . $file;

          if (is_dir($filePath)) {
            $directoryFiles = array_diff(scandir($filePath), ['.', '..']);
            $callback($filePath, $directoryFiles, $callback, $totalWeight);
          } else {
            $totalWeight += filesize($filePath);
          }
        }
      };

      $callbackFunction($modulePath, $directoryFiles, $callbackFunction, $totalWeight);

      $totalWeight = match ($enumWeight) {
        ModuleEnumWeight::BYTES => $totalWeight,
        ModuleEnumWeight::KILOBYTES => $totalWeight / 1024,
        ModuleEnumWeight::MEGABYTES => $totalWeight / (1024 ^ 2),
        ModuleEnumWeight::GIGABYTES => $totalWeight / (1024 ^ 3),
        ModuleEnumWeight::TERABYTES => $totalWeight / (1024 ^ 4),
        ModuleEnumWeight::PETABYTES => $totalWeight / (1024 ^ 5),
        ModuleEnumWeight::EXABYTES => $totalWeight / (1024 ^ 6),
        ModuleEnumWeight::ZETTABYTES => $totalWeight / (1024 ^ 7),
        ModuleEnumWeight::YOTTABYTES => $totalWeight / (1024 ^ 8),
      };

      return $totalWeight;
    }

    /**
     * Получение имени ячейки метаданных
     * 
     * @param ModuleEnumMetadata $metadata
     * 
     * @return string
     */
    public static function get_metadata_name(ModuleEnumMetadata $metadata) : string {
      return match ($metadata) {
        ModuleEnumMetadata::AUTHOR_NAME => 'authorName',
        ModuleEnumMetadata::AUTHOR_CODE_NAME => 'authorCodeName',
        ModuleEnumMetadata::AUTHOR_CODE_SERVER_NAME => 'authorCodeServerName',
        ModuleEnumMetadata::AUTHOR_CODE_CLIENT_NAME => 'authorCodeClientName',
        ModuleEnumMetadata::AUTHOR_DESIGNER_NAME => 'authorDesignerName',
        ModuleEnumMetadata::AUTHOR_LAYOUT_NAME => 'authorLayoutName',
        ModuleEnumMetadata::AUTHOR_SITE_LINK => 'authorSiteLink',
        ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK => 'authorSocialVKLink',
        ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK => 'authorSocialOKLink',
        ModuleEnumMetadata::CATEGORY_NAME => 'categoryName',
        ModuleEnumMetadata::WEIGHT => 'size',
        ModuleEnumMetadata::DATETIME_CREATED_UNIX => 'datetimeCreatedUnix',
        ModuleEnumMetadata::DATETIME_UPDATED_UNIX => 'datetimeUpdatedUnix',
        ModuleEnumMetadata::VERSION => 'version'
      };
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
     * @param  SystemCore $CMSCore
     * @param  string $name
     * @return bool
     */
    public static function connect_core(SystemCore $CMSCore, string $name) : bool {
      $module = new Module($CMSCore, $name);
      
      if ($module->exists_core_file()) {
        require_once($module->get_core_path());
        $coreClass = $module->get_core_class();
        $CMSCore->modules[$name] = new $coreClass($CMSCore, $module);

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
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/enabled';
      return file_exists($filePath);
    }

    /**
     * Проверить установку модуля
     * 
     * @return bool
     */
    public function is_installed() : bool {
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/installed';
      return file_exists($filePath);
    }

    /**
     * Установить модуля
     * 
     * @return bool
     */
    public function install() : bool {
      if (!$this->is_installed()) {
        $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/installed';
        $file = fopen($filePath, 'w');

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
        $path = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name();
        $this->CMSCore::recursive_files_remove($path);

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
        $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/enabled';
        $file = fopen($filePath, 'w');

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
        $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/enabled';
        unlink($filePath);

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
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->get_name() . '/core.class.php';
      return file_exists($filePath);
    }
    
    /**
     * Получение абсолютного пути до файла ядра модуля
     *
     * @return string
     */
    public function get_core_path() : string {
      return $this->get_path() . '/core.class.php';
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
      return $this->get_path() . '/metadata.json';
    }
    
    /**
     * Получение массива метаданных модуля
     *
     * @return array
     */
    public function get_metadata() : array|null {
      $filePath = $this->get_file_metadata_json_path();
      $fileContent = file_get_contents($filePath);

      return json_decode($fileContent, true);
    }

    /**
     * Получить абсолютный путь до файла README.md
     * 
     * @return string
     */
    public function get_file_readme_md_path() : string {
      return $this->get_path() . '/README.md';
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
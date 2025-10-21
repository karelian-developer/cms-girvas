<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\InterfaceModule as ModuleInterface;
use \core\PHPLibrary\Module\Collector as ModuleCollector;
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
final class Module implements ModuleInterface
{
  /** @var ModuleLocale|null Объект локализации */
  public ModuleLocale|null $locale = null;
  /** @var string|null Абсолютный путь до файлов модуля */
  private string|null $path = null;
  /** @var string|null URL до файлов модуля */
  private string|null $url = null;
  
  /**
   * __construct
   *
   * @param  CoreInterface $CMSCore
   * @param  string $name
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore,
    private string $name
  ) {
    $CMSBaseLocaleSettedName = $this->CMSCore->configurator->getDatabaseEntryValue('base_locale');
    $URLBaseLocaleSettedName = $this->CMSCore->urlp->getParam('locale');
    $CookieBaseLocaleSettedName = $_COOKIE['locale'] ?? null;

    $CMSBaseLocaleName = $URLBaseLocaleSettedName ?? $CookieBaseLocaleSettedName;
    $CMSBaseLocaleName = $CMSBaseLocaleName ?? $CMSBaseLocaleSettedName;
    $CMSBaseLocaleName = $CMSBaseLocaleName ?? 'en_US';
    $CMSBaseLocale = new ModuleLocale($this, $CMSBaseLocaleName);

    if (!$CMSBaseLocale->existsFileDataJSON()) {
      $CMSBaseLocale = new ModuleLocale($this, $CMSBaseLocaleName);
    }

    $this->locale = $CMSBaseLocale;

    $modulePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->name;
    $moduleURL = 'modules/' . $this->name;

    $this->setPath($modulePath);
    $this->setURL($moduleURL);
  }
  
  /**
   * Назначение абсолютного пути до файлов модуля
   *
   * @param  string $path
   * 
   * @return void
   */
  private function setPath(string $path) : void
  {
    $this->path = $path;
  }
  
  /**
   * Назначить URL до модуля
   *
   * @param  string Путь до шаблона
   * 
   * @return void
   */
  public function setURL(string $url) : void
  {
    $this->url = $url;
  }

  /**
   * Получить URL до изображения-превью модуля
   * 
   * @return string
   */
  public function getPreviewURL() : string
  {
    return '/' . $this->getURL() . '/preview.png';
  }

  /**
   * Получить абсолютный путь до скриншотов модуля
   * 
   * @return string
   */
  public function getScreenshotsPath() : string
  {
    return $this->getPath() . '/screenshots';
  }

  /**
   * Получить URL до скриншотов модуля
   * 
   * @return string
   */
  public function getScreenshotsURL() : string
  {
    return '/' . $this->getURL() . '/screenshots';
  }

  /**
   * Получить массив со скриншотами модуля
   * 
   * @return array
   */
  public function getScreenshotsArray() : array
  {
    $path = $this->getScreenshotsPath();
    return file_exists($path) ? array_diff(scandir($path), ['.', '..']) : [];
  }
  
  /**
   * Получение абсолютного пути до файлов модуля
   *
   * @return string
   */
  public function getPath() : string
  {
    return $this->path;
  }
  
  /**
   * Получить URL до модуля
   *
   * @return string
   */
  public function getURL() : string
  {
    return $this->url;
  }
  
  /**
   * Получение технического имени модуля
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name;
  }
  
  /**
   * Получение заголовка модуля (из метаданных)
   *
   * @return string
   */
  public function getTitle() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['title'] ?? '';
  }
  
  /**
   * Получение описания модуля (из метаданных)
   *
   * @return string
   */
  public function getDescription() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['description'] ?? '';
  }
  
  /**
   * Получение имени автора модуля (из метаданных)
   *
   * @return string
   */
  public function getAuthorName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['authorName'] ?? '';
  }

  /**
   * Получить вес модуля в байтах
   * 
   * @param ModuleEnumWeight $enumWeight
   * 
   * @return float
   */
  public static function getWeight(Module $module, ModuleEnumWeight $enumWeight) : float
  {
    $modulePath = $module->getPath();
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
  public static function getMetadataName(ModuleEnumMetadata $metadata) : string
  {
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
   * 
   * @return void
   */
  protected function setName(string $value) : void
  {
    $this->name = $value;
  }
  
  /**
   * Подключние файла ядра модуля
   *
   * @param  CoreInterface $CMSCore
   * @param  string $name
   * 
   * @return bool
   */
  public static function connectCore(CoreInterface $CMSCore, string $name) : bool
  {
    $module = new Module($CMSCore, $name);
    
    if ($module->existsCoreFile()) {
      require_once $module->getCorePath();
      $coreClass = $module->getCoreClass();
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
  public function isEnabled() : bool
  {
    $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/enabled';
    return file_exists($filePath);
  }

  /**
   * Проверить установку модуля
   * 
   * @return bool
   */
  public function isInstalled() : bool
  {
    $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/installed';
    return file_exists($filePath);
  }

  /**
   * Установить модуля
   * 
   * @return bool
   */
  public function install() : bool
  {
    if (!$this->isInstalled()) {
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/installed';
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
  public function delete() : bool
  {
    if ($this->isInstalled()) {
      $path = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName();
      $this->CMSCore::recursiveFilesRemove($path);

      return true;
    }

    return false;
  }

  /**
   * Включить модуль
   * 
   * @return bool
   */
  public function enable() : bool
  {
    if (!$this->isEnabled()) {
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/enabled';
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
  public function disable() : bool
  {
    if ($this->isEnabled()) {
      $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/enabled';
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
  public function existsCoreFile() : bool
  {
    $filePath = CMS_ROOT_DIRECTORY . '/modules/' . $this->getName() . '/core.class.php';
    return file_exists($filePath);
  }
  
  /**
   * Получение абсолютного пути до файла ядра модуля
   *
   * @return string
   */
  public function getCorePath() : string
  {
    return $this->getPath() . '/core.class.php';
  }
  
  /**
   * Получение пространства имен для ядра модуля
   *
   * @return string
   */
  private function getCoreClass() : string
  {
    return '\\modules\\' . $this->getName() . '\\Core';
  }
  
  /**
   * Получение даты создания файла ядра модуля (в UNIX-формате)
   *
   * @return int
   */
  public function getCoreCreatedUnixTimestamp() : int
  {
    $path = $this->getCorePath();
    return filectime($path);
  }
  
  /**
   * Проверка наличия файла с метаданными модуля
   *
   * @return bool
   */
  public function existsFileMetadataJSON() : bool
  {
    return file_exists($this->getFileMetadataJSONPath());
  }
  
  /**
   * Получение абсолютного пути до файла с метаданными модуля
   *
   * @return string
   */
  public function getFileMetadataJSONPath() : string
  {
    return $this->getPath() . '/metadata.json';
  }
  
  /**
   * Получение массива метаданных модуля
   *
   * @return ?array
   */
  public function getMetadata() : ?array
  {
    $filePath = $this->getFileMetadataJSONPath();
    $fileContent = file_get_contents($filePath);

    return json_decode($fileContent, true);
  }

  /**
   * Получить абсолютный путь до файла README.md
   * 
   * @return string
   */
  public function getFileReadmeMDPath() : string
  {
    return $this->getPath() . '/README.md';
  }

  /**
   * Получить содержимое файла README.md
   * 
   * @return string
   */
  public function getContentFileReadmeMD() : string
  {
    return $this->existsFileReadmeMD() ? file_get_contents($this->getFileReadmeMDPath()) : '';
  }

  /**
   * Проверить наличие файла README.md
   * 
   * @return bool
   */
  public function existsFileReadmeMD() : bool
  {
    return file_exists($this->getFileReadmeMDPath());
  }
}
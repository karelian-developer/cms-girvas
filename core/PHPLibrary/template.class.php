<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\SystemCore\FileConnector as SystemCoreFileConnector;
use \core\PHPLibrary\Template\EnumMetadata as ThemeEnumMetadata;
use \core\PHPLibrary\Template\EnumWeight as ThemeEnumWeight;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \core\PHPLibrary\Template\Locale as ThemeLocale;
use \DOMDocument as DOMDocument;

final class Template
{
  public SystemCore $CMSCore;
  public ThemeLocale $locale;
  public ThemeInterfaceCore $core;
  private string $path;
  private string $url;
  private string $name;
  private string $category;
  
  private array $styles = [];
  private array $scripts = [];

  private array $headLinks = [];

  private array $importantFiles = [
    'templates/html.tpl',
    'templates/header.tpl',
    'templates/main.tpl',
    'templates/footer.tpl',
    'templates/page.tpl',
    //'templates/page/entry.tpl',
    'templates/page/error.tpl',
    'templates/page/index.tpl',
    'metadata.json'
  ];

  private array $globalVars = [];
  
  /**
   * __construct
   *
   * @param  SystemCore $CMSCore Объект SystemCore
   * @param  string $themeName Наименование шаблона
   * @param  string $themeCategory Категория шаблона
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore, string $themeName = 'default', string $themeCategory = 'base')
  {
    // Установка технического имени шаблона
    $this->setName($themeName);
    // Установка категории шаблона
    $this->setCategory($themeCategory);

    /** @var SystemCore Объект системного ядра */
    $this->CMSCore = $CMSCore;

    if ($this->CMSCore->urlp->getPath(0) !== 'install') {
      /** @var ThemeLocale Объект локализации шаблона */
      $this->locale = new ThemeLocale($this, $this->CMSCore->locale->getName());
    }

    /** @var string Абсолютный путь до корневой директории шаблона */
    $themePath = $themeCategory !== 'base' ? CMS_ROOT_DIRECTORY . '/templates/' . $themeCategory . '/' . $themeName : CMS_ROOT_DIRECTORY . '/templates/' . $themeName;
    /** @var string Относительный URL до корневой директории шаблона */
    $themeURL = $themeCategory !== 'base' ? 'templates/' . $themeCategory . '/' . $themeName : 'templates/' . $themeName;
    
    // Установка абсолютного пути до шаблона
    $this->setPath($themePath);
    // Установка относительного URL до шаблона
    $this->setURL($themeURL);
  }
  
  /**
   * Инициализация шаблона
   *
   * @return mixed
   */
  public function init() : mixed
  {
    $this->addStyle(['href' => 'normalize.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-colors-scheme.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-base.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-fonts.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-forms.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-tables.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-interactive.css', 'rel' => 'stylesheet', 'isCore' => true]);
    $this->addStyle(['href' => 'default-notifications.css', 'rel' => 'stylesheet', 'isCore' => true]);

    /** @var string $corePath Путь до файла ядра шаблона */
    $corePath = $this->getCorePath();
    /** @var string $coreClass Класс ядра шаблона */
    $coreClass = $this->getCoreClass();
    if (file_exists($corePath)) {
      require_once $corePath;
      
      /** @var InterfaceCore $core Объект класса, имплементированного от InterfaceCore */
      $core = $this->getCoreObject($coreClass);

      if ($core !== null) {
        /** @var InterfaceCore $core Объект класса, имплементированного от InterfaceCore */
        $this->core = $core;
        $this->core->assembly();

        return true;
      }
    }

    // Если ядро не было найдено - завершаем работу с ошибкой
    die(sprintf('Template core "%s" is not exists!', $coreClass));
  }
  
  /**
   * Получить наименование шаблона
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name;
  }

  /**
   * Получить заголовок шаблона
   */
  public function getTitle() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['title'] ?? '';
  }

  /**
   * Получить описание шаблона
   *
   * @return string
   */
  public function getDescription() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['description'] ?? '';
  }

  /**
   * Получить имя автора шаблона
   *
   * @return string
   */
  public function getAuthorName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['authorName'] ?? '';
  }

  /**
   * Получить имя дизайнера шаблона
   *
   * @return string
   */
  public function getAuthorDesignerName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['authorDesignerName'] ?? '';
  }

  /**
   * Получить имя дизайнера шаблона
   *
   * @return string
   */
  public function getAuthorLayoutName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['authorLayoutName'] ?? '';
  }

  /**
   * Получить имя категории шаблона
   *
   * @return string
   */
  public function getCategoryName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['categoryName'] ?? 'base';
  }

  /**
   * Получить величину размера шаблона
   *
   * @return int
   */
  public function getSizeValue() : int
  {
    $metadata = $this->getMetadata();

    if (isset($metadata['size'])) {
      return is_numeric($metadata['size']) ? (int) $metadata['size'] : 0;
    }

    return 0;
  }
  
  /**
   * Назначить наименование категории шаблона
   *
   * @param  mixed $themeName Наименование шаблона
   * 
   * @return void
   */
  public function setCategory(string $themeCategory) : void
  {
    $this->category = $themeCategory;
  }
  
  /**
   * Получить наименование категории шаблона
   *
   * @return string
   */
  public function getCategory() : string
  {
    return $this->category;
  }
  
  /**
   * Назначить наименование шаблона
   *
   * @param  mixed $themeName Наименование шаблона
   * 
   * @return void
   */
  public function setName(string $themeName) : void
  {
    $this->name = $themeName;
  }
  
  /**
   * Получить путь до шаблона
   *
   * @return string
   */
  public function getPath() : string
  {
    return $this->path;
  }
  
  /**
   * Получить URL до шаблона
   *
   * @return string
   */
  public function getURL() : string
  {
    return $this->url;
  }
  
  /**
   * Назначить путь до шаблона
   *
   * @param  string $themePath Путь до шаблона
   * 
   * @return void
   */
  public function setPath(string $themePath) : void
  {
    $this->path = $themePath;
  }
  
  /**
   * Назначить URL до шаблона
   *
   * @param  string $themeURL Путь до шаблона
   * 
   * @return void
   */
  public function setURL(string $themeURL) : void
  {
    $this->url = $themeURL;
  }

  /**
   * Получить URL превью шаблона
   */
  public function getPreviewURL() : string
  {
    return '/' . $this->getURL() . '/preview.png';
  }

  /**
   * Получить путь до скриншотов шаблона
   *
   * @return string
   */
  public function getScreenshotsPath() : string
  {
    return $this->getPath() . '/screenshots';
  }

  /**
   * Получить URL скриншотов шаблона
   *
   * @return string
   */
  public function getScreenshotsURL() : string
  {
    return '/' . $this->getURL() . '/screenshots';
  }

  /**
   * Получить массив скриншотов шаблона
   *
   * @return array
   */
  public function getScreenshotsArray() : array
  {
    $path = $this->getScreenshotsPath();
    return file_exists($path) ? array_diff(scandir($path), ['.', '..']) : [];
  }
  
  /**
   * Получить массив стилей
   *
   * @return array
   */
  private function getStyles() : array
  {
    return $this->styles;
  }
  
  /**
   * Получить массив скриптов
   *
   * @return array
   */
  private function getScripts() : array
  {
    return $this->scripts;
  }
  
  /**
   * Добавить стиль в массив стилей
   *
   * @param  mixed $data
   * 
   * @return void
   */
  public function addStyle(array $data) : void
  {
    $this->styles[] = $data;
  }
  
  /**
   * Добавить скрипт в массив стилей
   *
   * @param  mixed $data
   * 
   * @return void
   */
  public function addScript(array $data, bool $isCMSCore = false) : void
  {
    $data['isCMSCore'] = $isCMSCore;
    $this->scripts[] = $data;
  }
  
  /**
   * Получить массив наименований обязательных файлов
   *
   * @return array
   */
  private function getImportantFiles() : array
  {
    return $this->importantFiles;
  }

  /**
   * Получить вес шаблона в байтах
   * 
   * @param ThemeEnumWeight $enumWeight
   * 
   * @return float
   */
  public static function getWeight(Template $theme, ThemeEnumWeight $enumWeight) : float
  {
    $themePath = $theme->getPath();
    $totalWeight = 0;
    
    $directoryFiles = array_diff(scandir($themePath), ['.', '..']);
    
    $callbackFunction = function(string $path, array $files, $callback, &$totalWeight) : void
    {
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

    $callbackFunction($themePath, $directoryFiles, $callbackFunction, $totalWeight);

    $totalWeight = match ($enumWeight) {
      ThemeEnumWeight::BYTES => $totalWeight,
      ThemeEnumWeight::KILOBYTES => $totalWeight / 1024,
      ThemeEnumWeight::MEGABYTES => $totalWeight / (1024 ^ 2),
      ThemeEnumWeight::GIGABYTES => $totalWeight / (1024 ^ 3),
      ThemeEnumWeight::TERABYTES => $totalWeight / (1024 ^ 4),
      ThemeEnumWeight::PETABYTES => $totalWeight / (1024 ^ 5),
      ThemeEnumWeight::EXABYTES => $totalWeight / (1024 ^ 6),
      ThemeEnumWeight::ZETTABYTES => $totalWeight / (1024 ^ 7),
      ThemeEnumWeight::YOTTABYTES => $totalWeight / (1024 ^ 8),
    };

    return $totalWeight;
  }
  
  /**
   * Проверка наличия обязательных файлов у шаблона
   *
   * @return bool
   */
  public function importantFilesExists() : bool
  {
    $themePath = $this->getPath();
    $importantFiles = $this->getImportantFiles();

    foreach ($importantFiles as $file) {
      $filePath = $themePath . '/' . $file;
      
      if (!file_exists($filePath)) {
        return false;
      }
    }

    return true;
  }
  
  /**
   * Добавить глобальную переменную
   *
   * @param  string $name
   * @param  string|int $value
   * 
   * @return void
   */
  public function addGlobalVariable(string $name, string|int $value) : void
  {
    $this->globalVars[$name] = $value;
  }

  public function assemblyGlobalVariables() : void {
    if (!empty($this->globalVars)) {
      $this->core->assembled = ThemeCollector::assembly($this->core->assembled, $this->globalVars);
    }
  }

  /**
   * Добавить каноническую ссылку
   *
   * @param string $href
   *
   * @return void
   */
  public function addLinkCanonical(string $href) : void
  {
    array_push($this->headLinks, [
      'rel' => 'canonical',
      'href' => $href
    ]);
  }

  /**
   * Получение имени ячейки метаданных
   * 
   * @param ThemeEnumMetadata $enumMetadata
   * 
   * @return string
   */
  public static function getMetadataName(ThemeEnumMetadata $enumMetadata) : string
  {
    return match ($enumMetadata) {
      ThemeEnumMetadata::AUTHOR_NAME => 'authorName',
      ThemeEnumMetadata::AUTHOR_CODE_NAME => 'authorCodeName',
      ThemeEnumMetadata::AUTHOR_CODE_SERVER_NAME => 'authorCodeServerName',
      ThemeEnumMetadata::AUTHOR_CODE_CLIENT_NAME => 'authorCodeClientName',
      ThemeEnumMetadata::AUTHOR_DESIGNER_NAME => 'authorDesignerName',
      ThemeEnumMetadata::AUTHOR_LAYOUT_NAME => 'authorLayoutName',
      ThemeEnumMetadata::AUTHOR_SITE_LINK => 'authorSiteLink',
      ThemeEnumMetadata::AUTHOR_SOCIAL_VK_LINK => 'authorSocialVKLink',
      ThemeEnumMetadata::AUTHOR_SOCIAL_OK_LINK => 'authorSocialOKLink',
      ThemeEnumMetadata::CATEGORY_NAME => 'categoryName',
      ThemeEnumMetadata::WEIGHT => 'size',
      ThemeEnumMetadata::DATETIME_CREATED_UNIX => 'datetimeCreatedUnix',
      ThemeEnumMetadata::DATETIME_UPDATED_UNIX => 'datetimeUpdatedUnix',
      ThemeEnumMetadata::VERSION => 'version'
    };
  }

  /**
   * Получить сборку шаблона ядра
   *
   * @return string
   */
  public function getCoreAssembled() : string
  {
    if (isset($this->core->assembled)) {
      /** @var bool Режим установщика */
      $isInstallationMode = $this->CMSCore->urlp->getParam('mode') === 'install';

      if ($isInstallationMode) {
        $siteTitle = 'Installation | ' . SystemCore::CMS_TITLE;
        $siteDescription = 'This site is not installed yet, but will be soon.';
        $siteKeywords = 'girvas';
        $siteCharset = 'UTF-8';
      } else {
        $CMSConfigurator = $this->CMSCore->configurator;
        $CMSLocale = $this->CMSCore->locale;

        $localeData = $CMSLocale->getData();
        $systemLocaleName = $CMSLocale->getName();

        $siteTitle = $CMSConfigurator->getMetaTitle() ?: $CMSConfigurator->getSiteTitle();
        $siteDescription = $CMSConfigurator->getMetaDescription() ?: $CMSConfigurator->getSiteDescription();
        $siteKeywords = $CMSConfigurator->getMetaKeywordsImploded() ?: $CMSConfigurator->getSiteKeywords();
        $siteCharset = $CMSConfigurator->getSiteCharset();
      }

      $systemStageDevelopingLabel = str_replace('-', '_', strtoupper($this->CMSCore->getCMSStageDeveloping()));

      $themeCategory = $this->getCategory();
      $themeVariablesArray = [
        // Стили веб-страницы в DOM-элементе HEAD
        'SITE_STYLES' => ThemeCollector::assemblyStyles($this, $this->getStyles()),
        // Скрипты веб-страницы в DOM-элементе HEAD
        'SITE_SCRIPTS' => ThemeCollector::assemblyScripts($this, $this->getScripts()),
        'SITE_TEMPLATE_URL' => $themeCategory !== 'base' ? '/templates/' . $themeCategory . '/' . $this->getName() : '/templates/' . $this->getName(),
        'SITE_TITLE' => $siteTitle,
        'SITE_DESCRIPTION' => $siteDescription,
        'SITE_KEYWORDS' => $siteKeywords,
        'SITE_CHARSET' => $siteCharset,
        'CMS_VERSION' => $this->CMSCore->getCMSVersion(),
        'CMS_VERSION_LABEL' => ThemeCollector::assembly(sprintf('{CMS_VERSION} {LANG:VERSION_%s_LABEL}', $systemStageDevelopingLabel), [
          'CMS_VERSION' => $this->CMSCore->getCMSVersion(),
        ]),
        'CMS_STAGE_DEVELOPING' => $this->CMSCore->getCMSStageDeveloping(),
        'CMS_TITLE' => $this->CMSCore->getCMSTitle(),
        'CMS_DOMAIN' => $this->CMSCore->getCMSDomain(),
        'CMS_PRODUCT_SITE_LINK' => $this->CMSCore::CMS_PRODUCT_SITE_LINK,
        'CMS_DEVELOPER_SITE_LINK' => $this->CMSCore::CMS_DEVELOPER_SITE_LINK,
        'CMS_DEVELOPER_TITLE' => $this->CMSCore::CMS_DEVELOPER_TITLE,
        'CMS_REESTR_DIGITAL_GOV_LINK' => $this->CMSCore::CMS_REESTR_DIGITAL_GOV_LINK,
        'CMS_COPYRIGHT' => $this->CMSCore::getCopyrightString()
      ];

      if (!$isInstallationMode && $this->CMSCore->urlp->getPath(0) !== 'install') {
        $entriesSamples = new EntriesSamples($this->CMSCore);
        $entriesSamplesArray = $entriesSamples->getAll();

        foreach ($entriesSamplesArray as $entriesSample) {
          $entriesSample->initData(['name', 'texts', 'metadata']);

          $themeNameCamelCase = function($string): string {
            $parts = explode('-', $string);
            $parts = array_map('ucfirst', $parts);
            return lcfirst(implode('', $parts));
          };
          
          $themeSamplePath = 'templates/samples/' . $themeNameCamelCase($entriesSample->getName());

          $entriesAssembled = [];
          $entriesArray = $entriesSample->getEntries([], true);
          if (count($entriesArray) > 0) {
            foreach ($entriesArray as $entry) {
              $entry->initData(['name', 'texts', 'metadata', 'categoryID', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
            }
          }

          $entriesSortVariablesMethods = [
            1 => 'getPublishedUnixTimestamp',
            2 => 'getCreatedUnixTimestamp',
            3 => 'getViewsCount',
            4 => 'getCommentsCount',
            5 => 'getRelevancePoints',
          ];

          $entriesSampleSortTypeID = $entriesSample->getSortTypeID();
          
          if (array_key_exists($entriesSampleSortTypeID, $entriesSortVariablesMethods)) {
            $entriesSortVariableMethod = $entriesSortVariablesMethods[$entriesSampleSortTypeID];
            $isReverse = true;

            usort($entriesArray, function($a, $b) use ($entriesSortVariableMethod, $isReverse) {
                $result = $a->$entriesSortVariableMethod() <=> $b->$entriesSortVariableMethod();
                return ($isReverse) ? -$result : $result;
            });
          }

          foreach ($entriesArray as $entry) {
            $entryCategory = $entry->getCategory();
            $entryCategoryTitle = $entryCategory->getTitle($systemLocaleName);

            $entryCreatedUnixTimestamp = $entry->getCreatedUnixTimestamp();
            $entryPublishedUnixTimestamp = $entry->getPublishedUnixTimestamp();
            $entryUpdatedUnixTimestamp = $entry->getUpdatedUnixTimestamp();

            $entryCreatedDateTimestamp = date('d.m.Y H:i:s', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestamp = date('d.m.Y H:i:s', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestamp = date('d.m.Y H:i:s', $entryUpdatedUnixTimestamp);

            $entryCreatedDateTimestampWithoutTime = date('d.m.Y', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestampWithoutTime = date('d.m.Y', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestampWithoutTime = date('d.m.Y', $entryUpdatedUnixTimestamp);
    
            $entryCreatedDateTimestampWithoutDate = date('H:i:s', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestampWithoutDate = date('H:i:s', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestampWithoutDate = date('H:i:s', $entryUpdatedUnixTimestamp);

            $entryCreatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestampISO8601 = date('Y-m-dH:i:s', $entryUpdatedUnixTimestamp);

            $entryCreatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestampISO8601WithoutTime = date('Y-m-d', $entryUpdatedUnixTimestamp);
    
            $entryCreatedDateTimestampISO8601WithoutDate = date('H:i:s', $entryCreatedUnixTimestamp);
            $entryPublishedDateTimestampISO8601WithoutDate = date('H:i:s', $entryPublishedUnixTimestamp);
            $entryUpdatedDateTimestampISO8601WithoutDate = date('H:i:s', $entryUpdatedUnixTimestamp);

            array_push($entriesAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, $themeSamplePath . '/item.tpl', [
              'ENTRY_ID' => $entry->getID(),
              'ENTRY_NAME' => $entry->getName(),
              'ENTRY_TITLE' => $entry->getTitle($systemLocaleName),
              'ENTRY_DESCRIPTION' => $entry->getDescription($systemLocaleName),
              'ENTRY_URL' => $entry->getURL(),
              'ENTRY_PREVIEW_URL' => $entry->getPreviewURL() !== '' ? $entry->getPreviewURL() : Entry::getPreviewDefaultURL($this->CMSCore, 512),
              'ENTRY_CATEGORY_TITLE' => $entryCategoryTitle,
              'ENTRY_CATEGORY_URL' => $entryCategory->getURL(),
              'ENTRY_CREATED_DATE_TIMESTAMP' => $entryCreatedDateTimestamp,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP' => $entryPublishedUnixTimestamp > 0 ? $entryPublishedDateTimestamp : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP' => $entryUpdatedDateTimestamp,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryCreatedDateTimestampWithoutTime,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryPublishedUnixTimestamp > 0 ? $entryPublishedDateTimestampWithoutTime : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryUpdatedDateTimestampWithoutTime,
              'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryCreatedDateTimestampWithoutDate,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryPublishedUnixTimestamp > 0 ? $entryPublishedDateTimestampWithoutDate : '-',
              'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryUpdatedDateTimestampWithoutDate,
              'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601' => $entryCreatedDateTimestampISO8601,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $entryPublishedDateTimestampISO8601,
              'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601' => $entryUpdatedDateTimestampISO8601,
              'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entryCreatedDateTimestampISO8601WithoutTime,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entryPublishedDateTimestampISO8601WithoutTime,
              'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entryUpdatedDateTimestampISO8601WithoutTime,
              'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entryCreatedDateTimestampISO8601WithoutDate,
              'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entryPublishedDateTimestampISO8601WithoutDate,
              'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entryUpdatedDateTimestampISO8601WithoutDate
            ]));
          }

          $themeSampleNameVariable = strtoupper(str_replace('-', '_', $entriesSample->getName()));
          $themeVariablesArray['ENTRIES_SAMPLE_' . $themeSampleNameVariable] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $themeSamplePath . '/wrapper.tpl', [
            'SAMPLE_ENTRIES_LIST' => implode('', $entriesAssembled),
            'SAMPLE_TITLE' => $entriesSample->getTitle($systemLocaleName),
            'SAMPLE_DESCRIPTION' => $entriesSample->getDescription($systemLocaleName)
          ]);
        }
      }

      // Внедрение значений свойств темы
      $this->core->assembled = ThemeCollector::assemblyPropertiesValues($this->core->assembled, $this->getFilePropertiesData());

      // Внедрение значений глобальных шаблонных переменных
      $this->core->assembled = ThemeCollector::assembly($this->core->assembled, $themeVariablesArray);

      // Сборка локализации по общим данным (глобальные языковые переменные)
      $this->core->assembled = ThemeCollector::assemblyLocale($this->core->assembled, $this->CMSCore->locale);

      if ($this->CMSCore->urlp->getPath(0) !== 'install') {
        $this->core->assembled = ThemeCollector::assemblyLocale($this->core->assembled, $this->locale);
      }

      // Сборка локализации на основе реестра (глобальные языковые переменные) с парсингом MarkDown-разметки
      $this->core->assembled = ThemeCollector::assemblyLocaleMarkdown($this->core->assembled, $this->CMSCore->locale);

      if ($this->CMSCore->urlp->getPath(0) !== 'install') {
        $this->core->assembled = ThemeCollector::assemblyLocaleMarkdown($this->core->assembled, $this->locale);
      }

      // Вычищаем память
      unset($themeVariablesArray);
      unset($entriesAssembled);

      $documentAssembledEncoded = mb_encode_numericentity($this->core->assembled, [0x80, 0x10FFFF, 0, ~0], 'UTF-8');

      libxml_use_internal_errors(true);

      $document = new DOMDocument();
      $document->loadHTML($documentAssembledEncoded);

      $elementHead = $document->getElementsByTagName('head');

      /**
       * Добавление стилей в секцию HEAD
       */

      $headStyles = $this->getStyles();
      if (isset($elementHead[0])) {
        foreach ($headStyles as $elementData) {
          $elementLink = $document->createElement('link');

          if (isset($elementData['rel']) && isset($elementData['href'])) {
            $styleIsCore = false;
            if (array_key_exists('isCore', $elementData)) {
              if ($elementData['isCore'] === true) {
                $styleIsCore = true;
                $styleHref = '/core/CSSCore/' . $elementData['href'];
              }
            }

            if (!$styleIsCore) {
              $themeName = $this->getName();
              $themeCategoryName = $this->getCategory();

              $styleHrefIsNotBase = '/templates/' . $themeCategoryName . '/' . $themeName . '/' . $elementData['href'];
              $styleHrefIsBase = '/templates/' . $themeName . '/' . $elementData['href'];
              $styleHref = $themeCategoryName !== 'base' ? $styleHrefIsNotBase : $styleHrefIsBase;
            }

            $attributeRel = $document->createAttribute('rel');
            $attributeRel->value = $elementData['rel'];

            $attributeHref = $document->createAttribute('href');
            $attributeHref->value = $styleHref;
            
            $elementLink->appendChild($attributeRel);
            $elementLink->appendChild($attributeHref);
          }

          $elementHead[0]->appendChild($elementLink);
        }
      }

      $headScripts = $this->getScripts();
      if (isset($elementHead[0])) {
        foreach ($headScripts as $elementData) {
          $elementScript = $document->createElement('script');

          if ($this->getCategory() !== 'base') {
            $scriptURL = !$elementData['isCMSCore'] ? '/templates/' . $this->getCategory() . '/' . $this->getName() . '/' . $elementData['src'] : '/core/JSLibrary/' . $elementData['src'];
          } else {
            $scriptURL = !$elementData['isCMSCore'] ? '/templates/' . $this->getName() . '/' . $elementData['src'] : '/core/JSLibrary/' . $elementData['src'];
          }

          if (array_key_exists('src', $elementData)) {
            foreach ($elementData as $attributeName => $attributeValue) {
              if ($attributeName !== 'isCMSCore') {
                $attribute = $document->createAttribute($attributeName);
                $attribute->value = $attributeName !== 'src' ? $attributeValue : $scriptURL;
                
                $elementScript->appendChild($attribute);
              }
            }

            $elementHead[0]->appendChild($elementScript);
          }
        }
      }


      if (isset($elementHead[0])) {
        foreach ($this->headLinks as $elementData) {
          $elementLink = $document->createElement('link');
          
          if (isset($elementData['rel']) && isset($elementData['href'])) {
            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
              $protocol = 'https';
            }
            else {
              $protocol = 'http';
            }

            $attributeRel = $document->createAttribute('rel');
            $attributeRel->value = $elementData['rel'];

            $attributeHref = $document->createAttribute('href');
            $attributeHref->value = $protocol . '://' . $_SERVER['HTTP_HOST'] . $elementData['href'];
            
            $elementLink->appendChild($attributeRel);
            $elementLink->appendChild($attributeHref);
          }

          $elementHead[0]->appendChild($elementLink);
        }
      }

      // Итоговая сборка шаблона веб-страницы
      return ThemeCollector::assembly($document->saveHTML(), []);
    }

    return 'Template core don\'t have a assembled templates files.';
  }
  
  /**
   * Получить полного пути до ядра шаблона
   *
   * @return string
   */
  private function getCorePath() : string
  {
    return $this->getPath() . '/core.class.php';
  }

  /**
   * Получить временную отметку создания ядра шаблона
   *
   * @return int
   */
  public function getCoreCreatedUnixTimestamp() : int
  {
    $path = $this->getCorePath();
    return filectime($path);
  }
  
  /**
   * Получить класс ядра шаблона
   *
   * @return string
   */
  private function getCoreClass() : string
  {
    $themeName = $this->getName();
    $themeCategory = $this->getCategory();

    $themeCoreNamespaceNotBase = '\\templates\\' . $themeCategory . '\\' . $themeName . '\\Core';
    $themeCoreNamespaceBase = '\\templates\\' . $themeName . '\\Core';

    return $themeCategory !== 'base' ? $themeCoreNamespaceNotBase : $themeCoreNamespaceBase;
  }
  
  /**
   * Получить объект ядра шаблона
   *
   * @param  mixed $themeClass
   * 
   * @return mixed
   */
  public function getCoreObject(string $themeClass) : mixed
  {
    if (class_exists($themeClass)) {
      return new $themeClass($this);
    }

    return null;
  }
  
  /**
   * Проверка наличия файла ядра шаблона
   *
   * @return bool
   */
  public function existsCoreFile() : bool
  {
    $filePath = $this->getCategory() === 'base' ? $this->getPath() . '/core.class.php' : $this->getPath() . '/' . $this->getCategory() . '/core.class.php';
    return file_exists($filePath);
  }

  /**
   * Проверить наличие файла JSON метаданных шаблона
   * 
   * @return bool
   */
  public function existsFileMetadataJSON() : bool
  {
    return file_exists($this->getFileMetadataJSONPath());
  }

  /**
   * Получить путь до файла JSON метаданных шаблона
   * 
   * @return string
   */
  public function getFileMetadataJSONPath() : string
  {
    return $this->getPath() . '/metadata.json';
  }

  /**
   * Получить метаданные шаблона
   * 
   * @return array|null
   */
  public function getMetadata() : array|null
  {
    $filePath = $this->getFileMetadataJSONPath();
    $fileContent = file_get_contents($filePath);

    return json_decode($fileContent, true);
  }

  /**
   * Получить путь до файла README.md шаблона
   * 
   * @return string
   */
  public function getFileReadmeMDPath() : string
  {
    return $this->getPath() . '/README.md';
  }

  /**
   * Получить содержимое файла README.md шаблона
   * 
   * @return string
   */
  public function getContentFileReadmeMD() : string
  {
    return $this->existsFileReadmeMD() ? file_get_contents($this->getFileReadmeMDPath()) : '';
  }

  /**
   * Проверить наличие файла README.md шаблона
   * 
   * @return bool
   */
  public function existsFileReadmeMD() : bool
  {
    return file_exists($this->getFileReadmeMDPath());
  }

  /**
   * Получить абсолютный путь файла со свойствами темы
   * 
   * @return string
   */
  public function getFilePropertiesPath() : string
  {
    return $this->getPath() . '/properties.json';
  }

  /**
   * Получить статус наличия файла со свойствами темы
   * 
   * @return bool
   */
  public function existsFileProperties() : bool
  {
    return file_exists($this->getFilePropertiesPath());
  }

  /**
   * Получить данные свойств темы из файла
   * 
   * @return array
   */
  public function getFilePropertiesData() : array
  {
    $fileData = ($this->existsFileProperties())
      ? file_get_contents($this->getFilePropertiesPath())
      : '{}';
    $dataJSON = json_decode($fileData, true);

    return $dataJSON !== null ? $dataJSON : [];
  }
}
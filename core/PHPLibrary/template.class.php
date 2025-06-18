<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\SystemCore\FileConnector as SystemCoreFileConnector;
  use \core\PHPLibrary\Template\EnumMetadata as TemplateEnumMetadata;
  use \core\PHPLibrary\Template\EnumWeight as TemplateEnumWeight;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Template\Locale as TemplateLocale;
  use \DOMDocument as DOMDocument;

  final class Template {
    public SystemCore $CMSCore;
    public TemplateLocale $locale;
    public mixed $core;
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
     * @param  mixed $themeName Наименование шаблона
     * @param  mixed $themeCategory Категория шаблона
     * @return void
     */
    public function __construct(SystemCore $CMSCore, string $themeName = 'default', string $themeCategory = 'base') {
      // Установка технического имени шаблона
      $this->set_name($themeName);
      // Установка категории шаблона
      $this->set_category($themeCategory);

      /** @var SystemCore Объект системного ядра */
      $this->CMSCore = $CMSCore;

      if ($this->CMSCore->urlp->get_path(0) != 'install') {
        /** @var TemplateLocale Объект локализации шаблона */
        $this->locale = new TemplateLocale($this, $this->CMSCore->locale->get_name());
      }

      /** @var string Абсолютный путь до корневой директории шаблона */
      $themePath = ($themeCategory != 'base') ? sprintf('%s/templates/%s/%s', CMS_ROOT_DIRECTORY, $themeCategory, $themeName) : sprintf('%s/templates/%s', CMS_ROOT_DIRECTORY, $themeName);
      /** @var string Относительный URL до корневой директории шаблона */
      $themeURL = ($themeCategory != 'base') ? sprintf('templates/%s/%s', $themeCategory, $themeName) : sprintf('templates/%s', $themeName);
      
      // Установка абсолютного пути до шаблона
      $this->set_path($themePath);
      // Установка относительного URL до шаблона
      $this->set_url($themeURL);
    }
    
    /**
     * Инициализация шаблона
     *
     * @return mixed
     */
    public function init() : mixed {
      $this->add_style(['href' => 'normalize.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-colors-scheme.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-base.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-fonts.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-forms.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-tables.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-interactive.css', 'rel' => 'stylesheet', 'is_core' => true]);
      $this->add_style(['href' => 'default-notifications.css', 'rel' => 'stylesheet', 'is_core' => true]);

      /** @var string $corePath Путь до файла ядра шаблона */
      $corePath = $this->get_core_path();
      /** @var string $coreClass Класс ядра шаблона */
      $coreClass = $this->get_core_class();
      if (file_exists($corePath)) {
        require_once($corePath);
        
        /** @var InterfaceCore $core Объект класса, имплементированного от InterfaceCore */
        $core = $this->get_core_object($coreClass);

        if (!is_null($core)) {
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
    public function get_name() : string {
      return $this->name;
    }

    /**
     * Получить заголовок шаблона
     */
    public function get_title() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['title'])) ? $metadata['title'] : '';
    }

    /**
     * Получить описание шаблона
     *
     * @return string
     */
    public function get_description() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['description'])) ? $metadata['description'] : '';
    }

    /**
     * Получить имя автора шаблона
     *
     * @return string
     */
    public function get_author_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['authorName'])) ? $metadata['authorName'] : '';
    }

    /**
     * Получить имя дизайнера шаблона
     *
     * @return string
     */
    public function get_author_designer_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['authorDesignerName'])) ? $metadata['authorDesignerName'] : '';
    }

    /**
     * Получить имя дизайнера шаблона
     *
     * @return string
     */
    public function get_author_layout_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['authorLayoutName'])) ? $metadata['authorLayoutName'] : '';
    }

    /**
     * Получить имя категории шаблона
     *
     * @return string
     */
    public function get_category_name() : string {
      $metadata = $this->get_metadata();
      return (isset($metadata['categoryName'])) ? $metadata['categoryName'] : 'base';
    }

    /**
     * Получить величину размера шаблона
     *
     * @return int
     */
    public function get_size_value() : int {
      $metadata = $this->get_metadata();
      return (isset($metadata['size'])) ? (int)$metadata['size'] : '';
    }
    
    /**
     * Назначить наименование категории шаблона
     *
     * @param  mixed $themeName Наименование шаблона
     * 
     * @return void
     */
    public function set_category(string $themeCategory) : void {
      $this->category = $themeCategory;
    }
    
    /**
     * Получить наименование категории шаблона
     *
     * @return string
     */
    public function get_category() : string {
      return $this->category;
    }
    
    /**
     * Назначить наименование шаблона
     *
     * @param  mixed $themeName Наименование шаблона
     * 
     * @return void
     */
    public function set_name(string $themeName) : void {
      $this->name = $themeName;
    }
    
    /**
     * Получить путь до шаблона
     *
     * @return string
     */
    public function get_path() : string {
      return $this->path;
    }
    
    /**
     * Получить URL до шаблона
     *
     * @return string
     */
    public function get_url() : string {
      return $this->url;
    }
    
    /**
     * Назначить путь до шаблона
     *
     * @param  string $themePath Путь до шаблона
     * 
     * @return void
     */
    public function set_path(string $themePath) : void {
      $this->path = $themePath;
    }
    
    /**
     * Назначить URL до шаблона
     *
     * @param  string $themeURL Путь до шаблона
     * 
     * @return void
     */
    public function set_url(string $themeURL) : void {
      $this->url = $themeURL;
    }

    /**
     * Получить URL превью шаблона
     */
    public function get_preview_url() : string {
      return sprintf('/%s/preview.png', $this->get_url());
    }

    /**
     * Получить путь до скриншотов шаблона
     *
     * @return string
     */
    public function get_screenshots_path() : string {
      return sprintf('%s/screenshots', $this->get_path());
    }

    /**
     * Получить URL скриншотов шаблона
     *
     * @return string
     */
    public function get_screenshots_url() : string {
      return sprintf('/%s/screenshots', $this->get_url());
    }

    /**
     * Получить массив скриншотов шаблона
     *
     * @return array
     */
    public function get_screenshots_array() : array {
      $screenshotsPath = $this->get_screenshots_path();
      return array_diff(scandir($screenshotsPath), ['.', '..']);
    }
    
    /**
     * Получить массив стилей
     *
     * @return array
     */
    private function get_styles() : array {
      return $this->styles;
    }
    
    /**
     * Получить массив скриптов
     *
     * @return array
     */
    private function get_scripts() : array {
      return $this->scripts;
    }
    
    /**
     * Добавить стиль в массив стилей
     *
     * @param  mixed $data
     * @return void
     */
    public function add_style(array $data) : void {
      array_push($this->styles, $data);
    }
    
    /**
     * Добавить скрипт в массив стилей
     *
     * @param  mixed $data
     * @return void
     */
    public function add_script(array $data, bool $isCMSCore = false) : void {
      $data['is_cms_core'] = $isCMSCore;
      array_push($this->scripts, $data);
    }
    
    /**
     * Получить массив наименований обязательных файлов
     *
     * @return array
     */
    private function get_important_files() : array {
      return $this->important_files;
    }

    /**
     * Получить вес шаблона в байтах
     * 
     * @param TemplateEnumWeight $enumWeight
     * 
     * @return float
     */
    public static function get_weight(Template $theme, TemplateEnumWeight $enumWeight) : float {
      $themePath = $theme->get_path();
      $totalWeight = 0;
      
      $directoryFiles = array_diff(scandir($themePath), ['.', '..']);
      $callbackFunction = function(string $path, array $files, $callback, &$totalWeight) : void {
        foreach ($files as $file) {
          $filePath = sprintf('%s/%s', $path, $file);

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
        TemplateEnumWeight::BYTES => $totalWeight,
        TemplateEnumWeight::KILOBYTES => $totalWeight / 1024,
        TemplateEnumWeight::MEGABYTES => $totalWeight / (1024 ^ 2),
        TemplateEnumWeight::GIGABYTES => $totalWeight / (1024 ^ 3),
        TemplateEnumWeight::TERABYTES => $totalWeight / (1024 ^ 4),
        TemplateEnumWeight::PETABYTES => $totalWeight / (1024 ^ 5),
        TemplateEnumWeight::EXABYTES => $totalWeight / (1024 ^ 6),
        TemplateEnumWeight::ZETTABYTES => $totalWeight / (1024 ^ 7),
        TemplateEnumWeight::YOTTABYTES => $totalWeight / (1024 ^ 8),
      };

      return $totalWeight;
    }
    
    /**
     * Проверка наличия обязательных файлов у шаблона
     *
     * @return bool
     */
    public function important_files_exists() : bool {
      $themePath = $this->get_path();
      $importantFiles = $this->get_important_files();

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
     * @return void
     */
    public function add_global_variable(string $name, string|int $value) : void {
      $this->globalVars[$name] = $value;
    }

    public function assembly_global_variables() : void {
      if (!empty($this->globalVars)) {
        $this->core->assembled = TemplateCollector::assembly($this->core->assembled, $this->globalVars);
      }
    }

    /**
     * Добавить каноническую ссылку
     *
     * @param string $href
     *
     * @return void
     */
    public function add_link_canonical(string $href) : void {
      array_push($this->headLinks, [
        'rel' => 'canonical',
        'href' => $href
      ]);
    }

    /**
     * Получение имени ячейки метаданных
     * 
     * @param TemplateEnumMetadata $enumMetadata
     * 
     * @return string
     */
    public static function get_metadata_name(TemplateEnumMetadata $enumMetadata) : string {
      return match ($enumMetadata) {
        TemplateEnumMetadata::AUTHOR_NAME => 'authorName',
        TemplateEnumMetadata::AUTHOR_CODE_NAME => 'authorCodeName',
        TemplateEnumMetadata::AUTHOR_CODE_SERVER_NAME => 'authorCodeServerName',
        TemplateEnumMetadata::AUTHOR_CODE_CLIENT_NAME => 'authorCodeClientName',
        TemplateEnumMetadata::AUTHOR_DESIGNER_NAME => 'authorDesignerName',
        TemplateEnumMetadata::AUTHOR_LAYOUT_NAME => 'authorLayoutName',
        TemplateEnumMetadata::AUTHOR_SITE_LINK => 'authorSiteLink',
        TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK => 'authorSocialVKLink',
        TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK => 'authorSocialOKLink',
        TemplateEnumMetadata::CATEGORY_NAME => 'categoryName',
        TemplateEnumMetadata::WEIGHT => 'size',
        TemplateEnumMetadata::DATETIME_CREATED_UNIX => 'datetimeCreatedUnix',
        TemplateEnumMetadata::DATETIME_UPDATED_UNIX => 'datetimeUpdatedUnix',
        TemplateEnumMetadata::VERSION => 'version'
      };
    }

    /**
     * Получить сборку шаблона ядра
     *
     * @return string
     */
    public function get_core_assembled() : string {
      if (isset($this->core->assembled)) {
        /** @var bool Режим установщика */
        $isInstallationMode = $this->CMSCore->urlp->get_param('mode') === 'install';

        if ($isInstallationMode) {
          $siteTitle = 'Installation | ' . SystemCore::CMS_TITLE;
          $siteDescription = 'This site is not installed yet, but will be soon.';
          $siteKeywords = 'girvas';
          $siteCharset = 'UTF-8';
        } else {
          $localeData = $this->CMSCore->locale->get_data();
          $systemLocaleName = $this->CMSCore->locale->get_name();

          $systemConfigurator = $this->CMSCore->configurator;
          $siteTitle = $systemConfigurator->get_meta_title() ?: $systemConfigurator->get_site_title();
          $siteDescription = $systemConfigurator->get_meta_description() ?: $systemConfigurator->get_site_description();
          $siteKeywords = $systemConfigurator->get_meta_keywords_imploded() ?: $systemConfigurator->get_site_keywords();
          $siteCharset = $systemConfigurator->get_site_charset();
        }

        $systemStageDevelopingLabel = str_replace('-', '_', strtoupper($this->CMSCore->get_cms_stage_developing()));

        $themeCategory = $this->get_category();
        $themeVariablesArray = [
          // Стили веб-страницы в DOM-элементе HEAD
          'SITE_STYLES' => TemplateCollector::assembly_styles($this, $this->get_styles()),
          // Скрипты веб-страницы в DOM-элементе HEAD
          'SITE_SCRIPTS' => TemplateCollector::assembly_scripts($this, $this->get_scripts()),
          'SITE_TEMPLATE_URL' => ($themeCategory != 'base') ? sprintf('/templates/%s/%s', $themeCategory, $this->get_name()) : sprintf('/templates/%s', $this->get_name()),
          'SITE_TITLE' => $siteTitle,
          'SITE_DESCRIPTION' => $siteDescription,
          'SITE_KEYWORDS' => $siteKeywords,
          'SITE_CHARSET' => $siteCharset,
          'CMS_VERSION' => $this->CMSCore->get_cms_version(),
          'CMS_VERSION_LABEL' => TemplateCollector::assembly(sprintf('{CMS_VERSION} {LANG:VERSION_%s_LABEL}', $systemStageDevelopingLabel), [
            'CMS_VERSION' => $this->CMSCore->get_cms_version(),
          ]),
          'CMS_STAGE_DEVELOPING' => $this->CMSCore->get_cms_stage_developing(),
          'CMS_TITLE' => $this->CMSCore->get_cms_title(),
          'CMS_DOMAIN' => $this->CMSCore->get_cms_domain(),
          'CMS_PRODUCT_SITE_LINK' => $this->CMSCore::CMS_PRODUCT_SITE_LINK,
          'CMS_DEVELOPER_SITE_LINK' => $this->CMSCore::CMS_DEVELOPER_SITE_LINK,
          'CMS_DEVELOPER_TITLE' => $this->CMSCore::CMS_DEVELOPER_TITLE,
          'CMS_REESTR_DIGITAL_GOV_LINK' => $this->CMSCore::CMS_REESTR_DIGITAL_GOV_LINK,
          'CMS_COPYRIGHT' => $this->CMSCore::get_copyright_string()
        ];

        if (!$isInstallationMode && $this->CMSCore->urlp->get_path(0) !== 'install') {
          $entriesSamples = new EntriesSamples($this->CMSCore);
          $entriesSamplesArray = $entriesSamples->get_all();

          foreach ($entriesSamplesArray as $entriesSample) {
            $entriesSample->init_data(['name', 'texts', 'metadata']);

            $themeNameCamelCase = function($string): string {
              $parts = explode('-', $string);
              $parts = array_map('ucfirst', $parts);
              return lcfirst(implode('', $parts));
            };
            
            $themeSamplePath = 'templates/samples/' . $themeNameCamelCase($entriesSample->get_name());

            $entriesAssembled = [];
            $entriesArray = $entriesSample->get_entries([], true);
            if (count($entriesArray) > 0) {
              foreach ($entriesArray as $entry) {
                $entry->init_data(['name', 'texts', 'metadata', 'category_id', 'created_unix_timestamp', 'updated_unix_timestamp']);
              }
            }

            $entriesSortVariablesMethods = [
              1 => 'get_published_unix_timestamp',
              2 => 'get_created_unix_timestamp',
              3 => 'get_views_count',
              4 => 'get_comments_count',
              5 => 'get_relevance_points',
            ];

            $entriesSampleSortTypeID = $entriesSample->get_sort_type_id();
            
            if (array_key_exists($entriesSampleSortTypeID, $entriesSortVariablesMethods)) {
              $entriesSortVariableMethod = $entriesSortVariablesMethods[$entriesSampleSortTypeID];
              $isReverse = true;

              usort($entriesArray, function($a, $b) use ($entriesSortVariableMethod, $isReverse) {
                  $result = $a->$entriesSortVariableMethod() <=> $b->$entriesSortVariableMethod();
                  return ($isReverse) ? -$result : $result;
              });
            }

            foreach ($entriesArray as $entry) {
              $entryCategory = $entry->get_category();
              $entryCategoryTitle = $entryCategory->get_title($systemLocaleName);

              $entryCreatedUnixTimestamp = $entry->get_created_unix_timestamp();
              $entryPublishedUnixTimestamp = $entry->get_published_unix_timestamp();
              $entryUpdatedUnixTimestamp = $entry->get_updated_unix_timestamp();

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

              array_push($entriesAssembled, TemplateCollector::assembly_file_content($this->CMSCore->template, sprintf('%s/item.tpl', $themeSamplePath), [
                'ENTRY_ID' => $entry->get_id(),
                'ENTRY_NAME' => $entry->get_name(),
                'ENTRY_TITLE' => $entry->get_title($systemLocaleName),
                'ENTRY_DESCRIPTION' => $entry->get_description($systemLocaleName),
                'ENTRY_URL' => $entry->get_url(),
                'ENTRY_PREVIEW_URL' => ($entry->get_preview_url() != '') ? $entry->get_preview_url() : Entry::get_preview_default_url($this->CMSCore, 512),
                'ENTRY_CATEGORY_TITLE' => $entryCategoryTitle,
                'ENTRY_CATEGORY_URL' => $entryCategory->get_url(),
                'ENTRY_CREATED_DATE_TIMESTAMP' => $entryCreatedDateTimestamp,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entryPublishedUnixTimestamp > 0) ? $entryPublishedDateTimestamp : '-',
                'ENTRY_UPDATED_DATE_TIMESTAMP' => $entryUpdatedDateTimestamp,
                'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryCreatedDateTimestampWithoutTime,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entryPublishedUnixTimestamp > 0) ? $entryPublishedDateTimestampWithoutTime : '-',
                'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entryUpdatedDateTimestampWithoutTime,
                'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entryCreatedDateTimestampWithoutDate,
                'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entryPublishedUnixTimestamp > 0) ? $entryPublishedDateTimestampWithoutDate : '-',
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

            $themeSampleNameVariable = strtoupper(str_replace('-', '_', $entriesSample->get_name()));
            $themeVariablesArray['ENTRIES_SAMPLE_' . $themeSampleNameVariable] = TemplateCollector::assembly_file_content($this->CMSCore->template, $themeSamplePath . '/wrapper.tpl', [
              'SAMPLE_ENTRIES_LIST' => implode('', $entriesAssembled),
              'SAMPLE_TITLE' => $entriesSample->get_title($systemLocaleName),
              'SAMPLE_DESCRIPTION' => $entriesSample->get_description($systemLocaleName)
            ]);
          }
        }

        // Внедрение значений глобальных шаблонных переменных
        $this->core->assembled = TemplateCollector::assembly($this->core->assembled, $themeVariablesArray);

        // Сборка локализации по общим данным (глобальные языковые переменные)
        $this->core->assembled = TemplateCollector::assembly_locale($this->core->assembled, $this->CMSCore->locale);

        if ($this->CMSCore->urlp->get_path(0) !== 'install') {
          $this->core->assembled = TemplateCollector::assembly_locale($this->core->assembled, $this->locale);
        }

        // Сборка локализации на основе реестра (глобальные языковые переменные) с парсингом MarkDown-разметки
        $this->core->assembled = TemplateCollector::assembly_locale_markdown($this->core->assembled, $this->CMSCore->locale);

        if ($this->CMSCore->urlp->get_path(0) !== 'install') {
          $this->core->assembled = TemplateCollector::assembly_locale_markdown($this->core->assembled, $this->locale);
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

        $headStyles = $this->get_styles();
        if (isset($elementHead[0])) {
          foreach ($headStyles as $elementData) {
            $elementLink = $document->createElement('link');

            if (isset($elementData['rel']) && isset($elementData['href'])) {
              $styleIsCore = false;
              if (array_key_exists('is_core', $elementData)) {
                if ($elementData['is_core'] === true) {
                  $styleIsCore = true;
                  $styleHref = '/core/CSSCore/' . $elementData['href'];
                }
              }

              if (!$styleIsCore) {
                $themeName = $this->get_name();
                $themeCategoryName = $this->get_category();

                $styleHrefIsNotBase = '/templates/' . $themeCategoryName . '/' . $themeName . '/' . $elementData['href'];
                $styleHrefIsBase = '/templates/' . $themeName . '/' . $elementData['href'];
                $styleHref = ($themeCategoryName !== 'base') ? $styleHrefIsNotBase : $styleHrefIsBase;
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

        $headScripts = $this->get_scripts();
        if (isset($elementHead[0])) {
          foreach ($headScripts as $elementData) {
            $elementScript = $document->createElement('script');

            if ($this->get_category() != 'base') {
              $scriptURL = (!$elementData['is_cms_core']) ? '/templates/' . $this->get_category() . '/' . $this->get_name() . '/' . $elementData['src'] : '/core/JSLibrary/' . $elementData['src'];
            } else {
              $scriptURL = (!$elementData['is_cms_core']) ? '/templates/' . $this->get_name() . '/' . $elementData['src'] : '/core/JSLibrary/' . $elementData['src'];
            }

            if (array_key_exists('src', $elementData)) {
              foreach ($elementData as $attributeName => $attributeValue) {
                if ($attributeName != 'is_cms_core') {
                  $attribute = $document->createAttribute($attributeName);
                  $attribute->value = ($attributeName != 'src') ? $attributeValue : $scriptURL;
                  
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
              if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $protocol = 'https';
              }
              else {
                $protocol = 'http';
              }

              $attributeRel = $document->createAttribute('rel');
              $attributeRel->value = $elementData['rel'];

              $attributeHref = $document->createAttribute('href');
              $attributeHref->value = sprintf('%s://%s%s', $protocol, $_SERVER['HTTP_HOST'], $elementData['href']);
              
              $elementLink->appendChild($attributeRel);
              $elementLink->appendChild($attributeHref);
            }

            $elementHead[0]->appendChild($elementLink);
          }
        }

        // Итоговая сборка шаблона веб-страницы
        return TemplateCollector::assembly($document->saveHTML(), []);
      }

      return 'Template core don\'t have a assembled templates files.';
    }
    
    /**
     * Получить полного пути до ядра шаблона
     *
     * @return string
     */
    private function get_core_path() : string {
      return sprintf('%s/core.class.php', $this->get_path());
    }

    /**
     * Получить временную отметку создания ядра шаблона
     *
     * @return int
     */
    public function get_core_created_unix_timestamp() : int {
      $path = $this->get_core_path();
      return filectime($path);
    }
    
    /**
     * Получить класс ядра шаблона
     *
     * @return string
     */
    private function get_core_class() : string {
      /** @var string $themeName Наименование шаблона */
      $themeName = $this->get_name();
      $themeCategory = $this->get_category();
      return ($themeCategory != 'base') ? sprintf('\\templates\\%s\\%s\\Core', $themeCategory, $themeName) :  sprintf('\\templates\\%s\\Core', $themeName);
    }
    
    /**
     * Получить объект ядра шаблона
     *
     * @param  mixed $themeClass
     * 
     * @return mixed
     */
    public function get_core_object(string $themeClass) : mixed {
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
    public function exists_core_file() : bool {
      $filePath = ($this->get_category() == 'base') ? sprintf('%s/core.class.php', $this->get_path()) : sprintf('%s/%s/core.class.php', $this->get_path(), $this->get_category());
      return file_exists($filePath);
    }

    /**
     * Проверить наличие файла JSON метаданных шаблона
     * 
     * @return bool
     */
    public function exists_file_metadata_json() : bool {
      return file_exists($this->get_file_metadata_json_path());
    }

    /**
     * Получить путь до файла JSON метаданных шаблона
     * 
     * @return string
     */
    public function get_file_metadata_json_path() : string {
      return sprintf('%s/metadata.json', $this->get_path());
    }

    /**
     * Получить метаданные шаблона
     * 
     * @return array|null
     */
    public function get_metadata() : array|null {
      $filePath = $this->get_file_metadata_json_path();
      $fileContent = file_get_contents($filePath);

      return json_decode($fileContent, true);
    }

    /**
     * Получить путь до файла README.md шаблона
     * 
     * @return string
     */
    public function get_file_readme_md_path() : string {
      return sprintf('%s/README.md', $this->get_path());
    }

    /**
     * Получить содержимое файла README.md шаблона
     * 
     * @return string
     */
    public function get_content_file_readme_md() : string {
      return ($this->exists_file_readme_md()) ? file_get_contents($this->get_file_readme_md_path()) : '';
    }

    /**
     * Проверить наличие файла README.md шаблона
     * 
     * @return bool
     */
    public function exists_file_readme_md() : bool {
      return file_exists($this->get_file_readme_md_path());
    }
  }

}

?>
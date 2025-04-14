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
    public SystemCore $system_core;
    public TemplateLocale $locale;
    public mixed $core;
    private string $path;
    private string $url;
    private string $name;
    private string $category;
    
    private array $styles = [];
    private array $scripts = [];

    private array $head_links = [];

    private array $important_files = [
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

    private array $global_variables = [];
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core Объект SystemCore
     * @param  mixed $template_name Наименование шаблона
     * @param  mixed $template_category Категория шаблона
     * @return void
     */
    public function __construct(SystemCore $system_core, string $template_name = 'default', string $template_category = 'base') {
      // Установка технического имени шаблона
      $this->set_name($template_name);
      // Установка категории шаблона
      $this->set_category($template_category);

      /** @var SystemCore Объект системного ядра */
      $this->system_core = $system_core;
      /** @var TemplateLocale Объект локализации шаблона */
      $this->locale = new TemplateLocale($this, $this->system_core->locale->get_name());

      /** @var string Абсолютный путь до корневой директории шаблона */
      $template_path = ($template_category != 'base') ? sprintf('%s/templates/%s/%s', CMS_ROOT_DIRECTORY, $template_category, $template_name) : sprintf('%s/templates/%s', CMS_ROOT_DIRECTORY, $template_name);
      /** @var string Относительный URL до корневой директории шаблона */
      $template_url = ($template_category != 'base') ? sprintf('templates/%s/%s', $template_category, $template_name) : sprintf('templates/%s', $template_name);
      
      // Установка абсолютного пути до шаблона
      $this->set_path($template_path);
      // Установка относительного URL до шаблона
      $this->set_url($template_url);
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

      /** @var string $core_path Путь до файла ядра шаблона */
      $core_path = $this->get_core_path();
      /** @var string $core_class Класс ядра шаблона */
      $core_class = $this->get_core_class();
      if (file_exists($core_path)) {
        require_once($core_path);
        
        /** @var InterfaceCore $core Объект класса, имплементированного от InterfaceCore */
        $core = $this->get_core_object($core_class);

        if (!is_null($core)) {
          /** @var InterfaceCore $core Объект класса, имплементированного от InterfaceCore */
          $this->core = $core;
          $this->core->assembly();

          return true;
        }
      }

      // Если ядро не было найдено - завершаем работу с ошибкой
      die(sprintf('Template core "%s" is not exists!', $core_class));
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
      return (isset($metadata['categoryName'])) ? $metadata['categoryName'] : 'default';
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
     * @param  mixed $template_name Наименование шаблона
     * 
     * @return void
     */
    public function set_category(string $template_category) : void {
      $this->category = $template_category;
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
     * @param  mixed $template_name Наименование шаблона
     * 
     * @return void
     */
    public function set_name(string $template_name) : void {
      $this->name = $template_name;
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
     * @param  string $template_path Путь до шаблона
     * 
     * @return void
     */
    public function set_path(string $template_path) : void {
      $this->path = $template_path;
    }
    
    /**
     * Назначить URL до шаблона
     *
     * @param  string $template_url Путь до шаблона
     * 
     * @return void
     */
    public function set_url(string $template_url) : void {
      $this->url = $template_url;
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
      $screenshots_path = $this->get_screenshots_path();
      return array_diff(scandir($screenshots_path), ['.', '..']);
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
     * @param  mixed $style_data
     * @return void
     */
    public function add_style(array $style_data) : void {
      array_push($this->styles, $style_data);
    }
    
    /**
     * Добавить скрипт в массив стилей
     *
     * @param  mixed $script_data
     * @return void
     */
    public function add_script(array $script_data, bool $is_cms_core = false) : void {
      $script_data['is_cms_core'] = $is_cms_core;
      array_push($this->scripts, $script_data);
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
     * @param TemplateEnumWeight $enum_weight
     * 
     * @return float
     */
    public static function get_weight(Template $template, TemplateEnumWeight $enum_weight) : float {
      $template_path = $template->get_path();
      $total_weight = 0;
      
      $directory_files = array_diff(scandir($template_path), ['.', '..']);
      $callback_function = function(string $path, array $files, $callback, &$total_weight) : void {
        foreach ($files as $file) {
          $file_path = sprintf('%s/%s', $path, $file);

          if (is_dir($file_path)) {
            $directory_files = array_diff(scandir($file_path), ['.', '..']);
            $callback($file_path, $directory_files, $callback, $total_weight);
          } else {
            $total_weight += filesize($file_path);
          }
        }
      };

      $callback_function($template_path, $directory_files, $callback_function, $total_weight);

      $total_weight = match ($enum_weight) {
        TemplateEnumWeight::BYTES => $total_weight,
        TemplateEnumWeight::KILOBYTES => $total_weight / 1024,
        TemplateEnumWeight::MEGABYTES => $total_weight / (1024 ^ 2),
        TemplateEnumWeight::GIGABYTES => $total_weight / (1024 ^ 3),
        TemplateEnumWeight::TERABYTES => $total_weight / (1024 ^ 4),
        TemplateEnumWeight::PETABYTES => $total_weight / (1024 ^ 5),
        TemplateEnumWeight::EXABYTES => $total_weight / (1024 ^ 6),
        TemplateEnumWeight::ZETTABYTES => $total_weight / (1024 ^ 7),
        TemplateEnumWeight::YOTTABYTES => $total_weight / (1024 ^ 8),
      };

      return $total_weight;
    }
    
    /**
     * Проверка наличия обязательных файлов у шаблона
     *
     * @return bool
     */
    public function important_files_exists() : bool {
      $template_path = $this->get_path();
      $important_files = $this->get_important_files();
      foreach ($important_files as $important_file) {
        $file_path = sprintf('%s/%s', $template_path, $important_file);
        if (!file_exists($file_path)) {
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
      $this->global_variables[$name] = $value;
    }

    public function assembly_global_variables() : void {
      if (!empty($this->global_variables)) {
        $this->core->assembled = TemplateCollector::assembly($this->core->assembled, $this->global_variables);
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
      array_push($this->head_links, [
        'rel' => 'canonical',
        'href' => $href
      ]);
    }

    /**
     * Получение имени ячейки метаданных
     * 
     * @param TemplateEnumMetadata $enum_metadata
     * 
     * @return string
     */
    public static function get_metadata_name(TemplateEnumMetadata $enum_metadata) : string {
      return match ($enum_metadata) {
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
        if ($this->system_core->urlp->get_param('mode') == 'install') {
          $site_title = 'Installation | CMS GIRVAS';
          $site_description = '';
          $site_keywords = '';
          $site_charset = 'UTF-8';
        } else {
          $locale_data = $this->system_core->locale->get_data();
          $cms_locale_name = $this->system_core->locale->get_name();

          $site_title = (empty($this->system_core->configurator->get_meta_title())) ? $this->system_core->configurator->get_site_title() : $this->system_core->configurator->get_meta_title();
          $site_description = (empty($this->system_core->configurator->get_meta_description())) ? $this->system_core->configurator->get_site_description() : $this->system_core->configurator->get_meta_description();
          $site_keywords = (empty($this->system_core->configurator->get_meta_keywords())) ? $this->system_core->configurator->get_site_keywords() : $this->system_core->configurator->get_meta_keywords_imploded();
          $site_charset = $this->system_core->configurator->get_site_charset();
        }

        $template_category = $this->get_category();
        $template_tags_array = [
          // Стили веб-страницы в DOM-элементе HEAD
          'SITE_STYLES' => TemplateCollector::assembly_styles($this, $this->get_styles()),
          // Скрипты веб-страницы в DOM-элементе HEAD
          'SITE_SCRIPTS' => TemplateCollector::assembly_scripts($this, $this->get_scripts()),
          'SITE_TEMPLATE_URL' => ($template_category != 'base') ? sprintf('/templates/%s/%s', $template_category, $this->get_name()) : sprintf('/templates/%s', $this->get_name()),
          'SITE_TITLE' => $site_title,
          'SITE_DESCRIPTION' => $site_description,
          'SITE_KEYWORDS' => $site_keywords,
          'SITE_CHARSET' => $site_charset,
          'CMS_VERSION' => $this->system_core->get_cms_version(),
          'CMS_VERSION_LABEL' => TemplateCollector::assembly(sprintf('{CMS_VERSION} {LANG:VERSION_%s_LABEL}', str_replace('-', '_', strtoupper($this->system_core->get_cms_stage_developing()))), [
            'CMS_VERSION' => $this->system_core->get_cms_version(),
          ]),
          'CMS_STAGE_DEVELOPING' => $this->system_core->get_cms_stage_developing(),
          'CMS_TITLE' => $this->system_core->get_cms_title(),
          'CMS_DOMAIN' => $this->system_core->get_cms_domain(),
          'CMS_PRODUCT_SITE_LINK' => $this->system_core::CMS_PRODUCT_SITE_LINK,
          'CMS_DEVELOPER_SITE_LINK' => $this->system_core::CMS_DEVELOPER_SITE_LINK,
          'CMS_DEVELOPER_TITLE' => $this->system_core::CMS_DEVELOPER_TITLE,
          'CMS_REESTR_DIGITAL_GOV_LINK' => $this->system_core::CMS_REESTR_DIGITAL_GOV_LINK,
          'CMS_COPYRIGHT' => $this->system_core::get_copyright_string()
        ];

        if ($this->system_core->urlp->get_param('mode') != 'install' && $this->system_core->urlp->get_path(0) != 'install') {
          $entries_samples = new EntriesSamples($this->system_core);
          $entries_samples_objects_array = $entries_samples->get_all();
          if (count($entries_samples_objects_array) > 0) {
            foreach ($entries_samples_objects_array as $entries_sample) {
              $entries_sample->init_data(['name', 'texts', 'metadata']);

              $template_name_camel_case = function($string) {
                $parts = explode('-', $string);
                $parts = array_map('ucfirst', $parts);

                return lcfirst(implode('', $parts));
              };
              
              $template_sample_path = sprintf('templates/samples/%s', $template_name_camel_case($entries_sample->get_name()));

              $entries_assembled = [];
              $entries_objects_array = $entries_sample->get_entries();
              if (count($entries_objects_array) > 0) {
                foreach ($entries_objects_array as $entry) {
                  $entry->init_data(['name', 'texts', 'metadata', 'category_id', 'created_unix_timestamp', 'updated_unix_timestamp']);
                }
              }

              $entries_sort_variables_methods = [
                1 => 'get_published_unix_timestamp',
                2 => 'get_created_unix_timestamp',
                3 => 'get_views_count',
                4 => 'get_comments_count',
                5 => 'get_relevance_points',
              ];

              $entries_sample_sort_type_id = $entries_sample->get_sort_type_id();
              
              if (array_key_exists($entries_sample_sort_type_id, $entries_sort_variables_methods)) {
                $entries_sort_variable_method = $entries_sort_variables_methods[$entries_sample_sort_type_id];
                $is_reverse = true;

                usort($entries_objects_array, function($a, $b) use ($entries_sort_variable_method, $is_reverse) {
                    $result = $a->$entries_sort_variable_method() <=> $b->$entries_sort_variable_method();
                    return ($is_reverse) ? -$result : $result;
                });
              }

              if (count($entries_objects_array) > 0) {
                foreach ($entries_objects_array as $entry) {
                  $entry_category = $entry->get_category();
                  $entry_category_title = $entry_category->get_title($cms_locale_name);

                  $entry_created_date_timestamp = date('d.m.Y H:i:s', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp = date('d.m.Y H:i:s', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp = date('d.m.Y H:i:s', $entry->get_updated_unix_timestamp());

                  $entry_created_date_timestamp_without_time = date('d.m.Y', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp_without_time = date('d.m.Y', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp_without_time = date('d.m.Y', $entry->get_updated_unix_timestamp());
          
                  $entry_created_date_timestamp_without_date = date('H:i:s', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp_without_date = date('H:i:s', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp_without_date = date('H:i:s', $entry->get_updated_unix_timestamp());

                  $entry_created_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp_iso_8601 = date('Y-m-dH:i:s', $entry->get_updated_unix_timestamp());

                  $entry_created_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp_iso_8601_without_time = date('Y-m-d', $entry->get_updated_unix_timestamp());
          
                  $entry_created_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_created_unix_timestamp());
                  $entry_published_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_published_unix_timestamp());
                  $entry_updated_date_timestamp_iso_8601_without_date = date('H:i:s', $entry->get_updated_unix_timestamp());

                  array_push($entries_assembled, TemplateCollector::assembly_file_content($this->system_core->template, sprintf('%s/item.tpl', $template_sample_path), [
                    'ENTRY_NAME' => $entry->get_name(),
                    'ENTRY_TITLE' => $entry->get_title($cms_locale_name),
                    'ENTRY_DESCRIPTION' => $entry->get_description($cms_locale_name),
                    'ENTRY_CATEGORY_TITLE' => $entry_category_title,
                    'ENTRY_CATEGORY_URL' => $entry_category->get_url(),
                    'ENTRY_CREATED_DATE_TIMESTAMP' => $entry_created_date_timestamp,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp : '-',
                    'ENTRY_UPDATED_DATE_TIMESTAMP' => $entry_updated_date_timestamp,
                    'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_created_date_timestamp_without_time,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_time : '-',
                    'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $entry_updated_date_timestamp_without_time,
                    'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry_created_date_timestamp_without_date,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => ($entry->get_published_unix_timestamp() > 0) ? $entry_published_date_timestamp_without_date : '-',
                    'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $entry_updated_date_timestamp_without_date,
                    'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601' => $entry_created_date_timestamp_iso_8601,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $entry_published_date_timestamp_iso_8601,
                    'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601' => $entry_updated_date_timestamp_iso_8601,
                    'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_created_date_timestamp_iso_8601_without_time,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_published_date_timestamp_iso_8601_without_time,
                    'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $entry_updated_date_timestamp_iso_8601_without_time,
                    'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_created_date_timestamp_iso_8601_without_date,
                    'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_published_date_timestamp_iso_8601_without_date,
                    'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $entry_updated_date_timestamp_iso_8601_without_date
                  ]));
                }
              }

              $template_sample_name_var = strtoupper(str_replace('-', '_', $entries_sample->get_name()));
              $template_tags_array[sprintf('ENTRIES_SAMPLE_%s', $template_sample_name_var)] = TemplateCollector::assembly_file_content($this->system_core->template, sprintf('%s/wrapper.tpl', $template_sample_path), [
                'SAMPLE_ENTRIES_LIST' => implode('', $entries_assembled),
                'SAMPLE_TITLE' => $entries_sample->get_title($cms_locale_name),
                'SAMPLE_DESCRIPTION' => $entries_sample->get_description($cms_locale_name)
              ]);
            }
          }
        }

        // Внедрение значений глобальных шаблонных переменных
        $this->core->assembled = TemplateCollector::assembly($this->core->assembled, $template_tags_array);
        
        // Сборка локализации по общим данным (глобальные языковые переменные)
        $this->core->assembled = TemplateCollector::assembly_locale($this->core->assembled, $this->system_core->locale);
        $this->core->assembled = TemplateCollector::assembly_locale($this->core->assembled, $this->locale);

        // Сборка локализации на основе реестра (глобальные языковые переменные) с парсингом MarkDown-разметки
        $this->core->assembled = TemplateCollector::assembly_locale_markdown($this->core->assembled, $this->system_core->locale);
        $this->core->assembled = TemplateCollector::assembly_locale_markdown($this->core->assembled, $this->locale);

        // Вычищаем память
        unset($template_tags_array);

        $document_assembled_encoded = mb_encode_numericentity($this->core->assembled, [0x80, 0x10FFFF, 0, ~0], 'UTF-8');

        libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTML($document_assembled_encoded);

        $element_head = $document->getElementsByTagName('head');

        /**
         * Добавление стилей в секцию HEAD
         */

        $head_styles = $this->get_styles();
        if (isset($element_head[0])) {
          if (count($head_styles) > 0) {
            foreach ($head_styles as $element_index => $element_data) {
              $element_link = $document->createElement('link');

              if (isset($element_data['rel']) && isset($element_data['href'])) {
                $style_is_core = false;
                if (array_key_exists('is_core', $element_data)) {
                  if ($element_data['is_core'] == true) {
                    $style_is_core = true;
                    $style_href = sprintf('/core/CSSCore/%s', $element_data['href']);
                  }
                }

                if (!$style_is_core) {
                  $style_href = ($this->get_category() != 'base') ? sprintf('/templates/%s/%s/%s', $this->get_category(), $this->get_name(), $element_data['href']) : sprintf('/templates/%s/%s', $this->get_name(), $element_data['href']);
                }

                $attribute_rel = $document->createAttribute('rel');
                $attribute_rel->value = $element_data['rel'];

                $attribute_href = $document->createAttribute('href');
                $attribute_href->value = $style_href;
                
                $element_link->appendChild($attribute_rel);
                $element_link->appendChild($attribute_href);
              }

              $element_head[0]->appendChild($element_link);
            }
          }
        }

        $head_scripts = $this->get_scripts();
        if (isset($element_head[0])) {
          if (count($head_scripts) > 0) {
            foreach ($head_scripts as $element_index => $element_data) {
              $element_script = $document->createElement('script');

              if ($this->get_category() != 'base') {
                $script_url = (!$element_data['is_cms_core']) ? sprintf('/templates/%s/%s/%s', $this->get_category(), $this->get_name(), $element_data['src']) : sprintf('/core/JSLibrary/%s', $element_data['src']);
              } else {
                $script_url = (!$element_data['is_cms_core']) ? sprintf('/templates/%s/%s', $this->get_name(), $element_data['src']) : sprintf('/core/JSLibrary/%s', $element_data['src']);
              }

              if (array_key_exists('src', $element_data)) {
                foreach ($element_data as $attribute_name => $attribute_value) {
                  if ($attribute_name != 'is_cms_core') {
                    $attribute = $document->createAttribute($attribute_name);
                    $attribute->value = ($attribute_name != 'src') ? $attribute_value : $script_url;
                    
                    $element_script->appendChild($attribute);
                  }
                }

                $element_head[0]->appendChild($element_script);
              }
            }
          }
        }


        if (isset($element_head[0])) {
          if (count($this->head_links) > 0) {
            foreach ($this->head_links as $element_index => $element_data) {
              $element_link = $document->createElement('link');
              
              if (isset($element_data['rel']) && isset($element_data['href'])) {
                if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                  $protocol = 'https';
                }
                else {
                  $protocol = 'http';
                }

                $attribute_rel = $document->createAttribute('rel');
                $attribute_rel->value = $element_data['rel'];

                $attribute_href = $document->createAttribute('href');
                $attribute_href->value = sprintf('%s://%s%s', $protocol, $_SERVER['HTTP_HOST'], $element_data['href']);
                
                $element_link->appendChild($attribute_rel);
                $element_link->appendChild($attribute_href);
              }

              $element_head[0]->appendChild($element_link);
            }
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
      /** @var string $template_name Наименование шаблона */
      $template_name = $this->get_name();
      $template_category = $this->get_category();
      return ($template_category != 'base') ? sprintf('\\templates\\%s\\%s\\Core', $template_category, $template_name) :  sprintf('\\templates\\%s\\Core', $template_name);
    }
    
    /**
     * Получить объект ядра шаблона
     *
     * @param  mixed $template_class
     * 
     * @return mixed
     */
    public function get_core_object(string $template_class) : mixed {
      if (class_exists($template_class)) {
        return new $template_class($this);
      }

      return null;
    }
    
    /**
     * Проверка наличия файла ядра шаблона
     *
     * @return bool
     */
    public function exists_core_file() : bool {
      $file_path = ($this->get_category() == 'default') ? sprintf('%s/core.class.php', $this->get_path()) : sprintf('%s/%s/core.class.php', $this->get_path(), $this->get_category());
      return file_exists($file_path);
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
      $file_path = $this->get_file_metadata_json_path();
      $file_content = file_get_contents($file_path);

      return json_decode($file_content, true);
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
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * Класс системного ядра является главным классом в CMS GIRVAS, поскольку он управляет
 * подключением всех необходимых файлов для работы системы, а также проводит иницилизацию
 * необходимых объектов, таких как: шаблон системы, локализация системы, парсер адресной строки,
 * сборщик шаблона, клиент и так далее.
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\SystemCore\Configurator as SystemCoreConfigurator;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\SystemCore\DatabaseConnector as SystemCoreDatabaseConnector;
  use \core\PHPLibrary\SystemCore\FileConnector as SystemCoreFileConnector;
  use \core\PHPLibrary\SystemCore\Report as SystemCoreReport;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Client as Client;
  
  /**
   * Class SystemCore
   * @package core\PHPLibrary
   * 
   * @property-read string CMS_CORE_PATH Полный путь до ядра CMS
   * @property-read string CMS_CORE_PHP_LIBRARY_PATH Полный путь до PHP-библиотеки CMS
   * @property-read string CMS_CORE_JS_LIBRARY_PATH Полный путь до JavaScript-библиотеки CMS
   * @property-read string CMS_CORE_TS_LIBRARY_PATH Полный путь до TypeScript-библиотеки CMS
   * @property      array $configuration Массив с конфигурациями CMS
   * @property      URLParser $urlp Объект класса URLParser
   */
  final class SystemCore {
    public const CMS_CORE_PATH = 'core';
    public const CMS_CORE_PHP_LIBRARY_PATH = 'core/PHPLibrary';
    public const CMS_CORE_JS_LIBRARY_PATH = 'core/JSLibrary';
    public const CMS_CORE_TS_LIBRARY_PATH = 'core/TSLibrary';
    public const CMS_MODULES_PATH = 'modules';
    public const CMS_TITLE = 'CMS GIRVAS';
    public const CMS_VERSION = '0.1.0-5 Alpha';
    public const CMS_DEVELOPER_TITLE = 'Garbalo (IE SHESTAKOV A.R.)';
    public const CMS_DEVELOPER_SITE_LINK = 'https://www.garbalo.com';
    public const CMS_PRODUCT_SITE_LINK = 'https://www.cms-girvas.ru';

    /** 
     * @var \core\PHPLibrary\SystemCore\Configurator Конфигуратор системы
     */
    public SystemCoreConfigurator|null $configurator = null;
    /** 
     * @var \core\PHPLibrary\SystemCore\DatabaseConnector Класс системы подключения к БД 
     */
    public SystemCoreDatabaseConnector|null $database_connector = null;
    /** 
     * @var \core\PHPLibrary\SystemCore\Locale Класс локализации ядра 
     */
    public SystemCoreLocale|null $locale = null;
    /**
     * @var \core\PHPLibrary\URLParser Класс парсера адресной строки 
     */
    public URLParser|null $urlp = null;
    /** 
     * @var \core\PHPLibrary\Client Класс клиента
     */
    public Client|null $client = null;
    /** 
     * @var \core\PHPLibrary\Template Класс шаблона системы 
     */
    public Template|null $template = null;

    /**
     *  @var array Массив активированных модулей
     * */
    public array $modules = [];
    /**
     * @var array Массив элементов пути до инициализированной страницы
     */
    public array $page_dir_array = [];
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
      // Инициализация ядра системы
      $this->init();
    }

    /**
     * Получить наименование системы
     * 
     * @return string
     */
    public function get_cms_title() : string {
      return self::CMS_TITLE;
    }

    /**
     * Получить текущую версию системы
     * 
     * @return string
     */
    public function get_cms_version() : string {
      return self::CMS_VERSION;
    }

    /**
     * Получить объект локализации ядра
     * 
     * Стандартный набор:
     * base - базовая локализация (веб-сайт)
     * admin - административная локализация (АП)
     * 
     * @param string $locale_type
     * 
     * @return SystemCoreLocale
     */
    public function get_cms_locale(string $locale_type = 'base') : SystemCoreLocale {
      switch ($locale_type) {
        case 'base': $locale_name = (!is_null($this->configurator->get_database_entry_value('base_locale'))) ? $this->configurator->get_database_entry_value('base_locale') : SystemCoreLocale::DEFAULT_LOCALE_NAME; break;
        case 'admin': $locale_name = (!is_null($this->configurator->get_database_entry_value('base_admin_locale'))) ? $this->configurator->get_database_entry_value('base_admin_locale') : SystemCoreLocale::DEFAULT_LOCALE_NAME; break;
        default: $locale_name = (!is_null($this->configurator->get_database_entry_value($locale_type . '_locale'))) ? $this->configurator->get_database_entry_value($locale_type . '_locale') : SystemCoreLocale::DEFAULT_LOCALE_NAME;
      }

      return new SystemCoreLocale($this, $locale_name, $locale_type);
    }

    /**
     * Получить домен из конфигурации
     * 
     * @return string
     */
    public function get_cms_domain() : string {
      return (!is_null($this->configurator->get('domain'))) ? $this->configurator->get('domain') : 'www.garbalo.com';
    }

    /**
     * Установить шаблон для системы
     * 
     * @param Template $template
     * 
     * @return void
     */
    public function set_template(Template $template) : void {
      $this->template = $template;
    }

    /**
     * Получить текущий шаблон
     * 
     * @return Template
     */
    public function get_template() : Template {
      return $this->template;
    }

    /**
     * Получить инициализированную страницу
     * 
     * @return InterfacePage
     */
    public function get_inited_page() : InterfacePage {
      return $this->page_dir_array[array_key_last($this->page_dir_array)];
    }

    /**
     * Получить копирайт в виде строки
     * 
     * @return string
     */
    public static function get_copyright_string() : string {
      return sprintf('<div class="footer__copyright"><span>&copy;&nbsp;<a href="%s" title="Garbalo Site Official" target="_blank">%s</a>.</span> <span>2021&nbsp;&mdash;&nbsp;%d. <span>All&nbsp;rights&nbsp;reserved.</span> <span>Powered&nbsp;by&nbsp;<a href="%s" title="CMS Site Official" target="_blank">CMS&nbsp;&laquo;GIRVAS&raquo;</a>.</span></div>', self::CMS_DEVELOPER_SITE_LINK, self::CMS_DEVELOPER_TITLE, date('Y'), self::CMS_PRODUCT_SITE_LINK);
    }

    /**
     * Инициализация страницы
     * 
     * @param string $dir
     * 
     * @return bool
     */
    public function init_page(string $dir) : bool {
      $dir = ($dir == '') ? 'index' : $dir;
      $dir = rtrim($dir, '/');
      
      $this->page_dir_array = explode('/', $dir);
      $this->page_dir_array[count($this->page_dir_array) - 1] = explode('?', $this->page_dir_array[count($this->page_dir_array) - 1]);
      $this->page_dir_array[count($this->page_dir_array) - 1] = $this->page_dir_array[count($this->page_dir_array) - 1][0];
      
      if ($this->page_dir_array[0] == $this->template->get_category()) {
        $this->page_dir_array[0] = ucfirst($this->page_dir_array[0]);
        array_push($this->page_dir_array, 'index');
      }
      
      $current_dir_final_array = [];
      for ($index_a = 0; $index_a < count($this->page_dir_array); $index_a++) {
        $current_dir_array = [];
        for ($index_b = 0; $index_b < $index_a + 1; $index_b++) {
          array_push($current_dir_array, $this->page_dir_array[$index_b]);
        }

        $current_dir = implode('/', $current_dir_array);
        $class_path = sprintf('%s/core/PHPLibrary/Page/%s.class.php', CMS_ROOT_DIRECTORY, $current_dir);
        
        if (file_exists($class_path)) {
          $current_dir_array[array_key_last($current_dir_array)] = 'Page' . ucfirst($current_dir_array[array_key_last($current_dir_array)]);
          $current_dir = implode('/', $current_dir_array);
          $current_dir = str_replace('/', '\\', $current_dir);
          
          $class = sprintf('\\core\\PHPLibrary\\Page\\%s', $current_dir);
          $page_object = new $class($this, new Page($this, $current_dir_array));
          
          if ($current_dir_array[0] == $this->template->get_category()) unset($current_dir_array[0]);
          $current_dir_array[array_key_last($current_dir_array)] = &$page_object;
          $current_dir_final_array = $current_dir_array;
          break;
        }
      }

      if (empty($current_dir_final_array)) {
        $current_dir_final_array['oh_shit'] = 'karelia_forever';
      }

      if (gettype($current_dir_final_array[array_key_last($current_dir_final_array)]) == 'string') {
        $this->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
        
        $class = sprintf('\\core\\PHPLibrary\\Page\\PageError', $current_dir);
        $current_dir_final_array[array_key_last($current_dir_final_array)] = new $class($this, new Page($this, $current_dir_final_array), 404);
      }

      $this->page_dir_array = $current_dir_final_array;

      return true;
    }
    
    /**
     * Инициализация ядра системы и всех необходимых ее компонентов
     *
     * @return void
     */
    private function init() {
      require_once(sprintf('%s/%s/SystemCore/fileConnector.interface.php', CMS_ROOT_DIRECTORY, self::CMS_CORE_PHP_LIBRARY_PATH));
      require_once(sprintf('%s/%s/SystemCore/fileConnector.class.php', CMS_ROOT_DIRECTORY, self::CMS_CORE_PHP_LIBRARY_PATH));

      $file_connector = new SystemCoreFileConnector($this);
      $file_connector->set_start_directory(self::CMS_CORE_PHP_LIBRARY_PATH);
      $file_connector->set_current_directory(self::CMS_CORE_PHP_LIBRARY_PATH);
      // Подключение файлов с перечислениями
      $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.enum\.php$/');
      $file_connector->reset_current_directory();
      // Подключение файлов с интерфейсами
      $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.interface\.php$/');
      $file_connector->reset_current_directory();
      // Подключение файлов с классами
      $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.class\.php$/');
      $file_connector->reset_current_directory();

      $template = null;

      $this->init_url_parser();

      // Если настройка системы не была произведена и пользователь не находится на странице инсталлятора,
      // то его необходимо перенаправить на страницу инсталлятора.
      if (!file_exists(sprintf('%s/INSTALLED', CMS_ROOT_DIRECTORY)) && $this->urlp->get_path(0) != 'install' && $this->urlp->get_path(0) != 'handler') {
        header('location: /install');
      }

      $this->configurator = new SystemCoreConfigurator($this);

      if ($this->urlp->get_path(0) != 'install' && $this->urlp->get_path(1) != 'install') {
        $this->database_connector = new SystemCoreDatabaseConnector($this, $this->configurator);
      }

      $this->client = new Client($this);

      if ($this->urlp->get_path(0) == 'install' && $this->urlp->get_path(1) != 'install') {
        $install_locale = (!is_null($this->urlp->get_param('locale'))) ? $this->urlp->get_param('locale') : 'en_US';
      }

      $modules_installed = Modules::get_installed_modules_array();
      if (!empty($modules_installed)) {
        foreach ($modules_installed as $index => $folder_name) {
          $module_path = sprintf('%s/%s', Modules::get_absolute_modules_path(), $folder_name);
          $module = new Module($this, $folder_name);
          if ($module->is_enabled()) {
            $file_connector->set_start_directory($module_path);
            $file_connector->set_current_directory($module_path);

            // Подключение файлов с перечислениями
            $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.enum\.php$/');
            $file_connector->reset_current_directory();
            // Подключение файлов с интерфейсами
            $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.interface\.php$/');
            $file_connector->reset_current_directory();
            // Подключение файлов с классами
            $file_connector->connect_files_recursive('/^([a-zA-Z_0-9]+)\.class\.php$/');
            $file_connector->reset_current_directory();

            Module::connect_core($this, $folder_name);
          }

          unset($module);
        }
      }

      $modules_installed = Modules::get_installed_modules_array();
      if (!empty($this->modules)) {
        foreach ($this->modules as $name => $module_core) {
          $module = new Module($this, $name);
          if ($module->is_installed() && $module->is_enabled()) {
            $module_core->preparation();
          }

          unset($module);
        }
      }

      if ($this->urlp->get_path(0) != 'handler' && $this->urlp->get_path(0) != 'feed') {

        if ($this->urlp->get_path(0) != 'install') {
          
          $template_base_name = ($this->configurator->exists_database_entry_value('base_template')) ? $this->configurator->get_database_entry_value('base_template') : 'default';
          $cms_base_locale_name = ($this->configurator->exists_database_entry_value('base_locale')) ? $this->configurator->get_database_entry_value('base_locale') : 'en_US';
          $cms_admin_locale_name = ($this->configurator->exists_database_entry_value('base_admin_locale')) ? $this->configurator->get_database_entry_value('base_admin_locale') : 'en_US';
        
        }

        $install_locale = ($this->urlp->get_param('locale') != null) ? $this->urlp->get_param('locale') : 'ru_RU';

        if ($this->urlp->get_path(0) == 'install' && !file_exists(sprintf('%s/INSTALLED', CMS_ROOT_DIRECTORY))) {
          $this->set_template(new Template($this, 'default', 'install'));
          $this->locale = new SystemCoreLocale($this, $install_locale, 'install');
        } else {
          switch ($this->urlp->get_path(0)) {
            case 'admin': $this->set_template(new Template($this, 'default', 'admin')); $this->locale = new SystemCoreLocale($this, $cms_admin_locale_name, 'admin'); break;
            case 'install': die('CMS is already installed.');
            default: $this->set_template(new Template($this, $template_base_name)); $this->locale = new SystemCoreLocale($this, $cms_base_locale_name, 'base'); break;
          }
        }

        $template = $this->get_template();
        $template->init();

        $template->core->assembled = $template->get_core_assembled();
      } else {
        $handler_locale_name = (!is_null($this->urlp->get_param('localeMessage'))) ? $this->urlp->get_param('localeMessage') : 'en_US';
        $this->locale = new SystemCoreLocale($this, $handler_locale_name, 'handler');
      }
      
      $modules_installed = Modules::get_installed_modules_array();
      if (!empty($this->modules)) {
        foreach ($this->modules as $name => $module_core) {
          $module = new Module($this, $name);
          if ($module->is_installed() && $module->is_enabled()) {
            if (!is_null($template)) {
              $template->core->assembled = TemplateCollector::assembly_locale($template->core->assembled, $module->locale);
            }
            $module_core->init();
          }

          unset($module);
        }
      }
    }
    
    /**
     * Получить путь до корня ситемы
     *
     * @return string
     */
    public function get_cms_path() : string {
      return $_SERVER['DOCUMENT_ROOT'];
    }
    
    /**
     * Получить массив имен загруженных шаблонов
     *
     * @return array
     */
    public function get_array_uploaded_templates_names() : array {
      $path = sprintf('%s/templates', $this->get_cms_path());
      return array_diff(scandir(sprintf($path)), ['..', '.']);
    }
    
    /**
     * Получить массив имен загруженных модулей
     *
     * @return array
     */
    public function get_array_uploaded_modules_names() : array {
      $path = sprintf('%s/modules', $this->get_cms_path());
      return array_diff(scandir(sprintf($path)), ['..', '.']);
    }
    
    /**
     * Получить массив имен локализаций
     *
     * @return array
     */
    public function get_array_locales_names() : array {
      $locales_path = sprintf('%s/locales', $this->get_cms_path());
      return array_diff(scandir(sprintf($locales_path)), ['..', '.']);
    }
    
    /**
     * Получить внешнюю ссылку до сайта
     *
     * @return string
     */
    public function get_site_url() : string {
      return ($this->configurator->get('ssl_is_enabled')) ? sprintf('https://%s', $this->configurator->get('domain')) : sprintf('http://%s', $this->configurator->get('domain'));
    }
    
    /**
     * Инициализация URL-парсера
     *
     * @return void
     */
    private function init_url_parser() {
      $this->urlp = new URLParser();
    }

    /**
     * Рекурсивно удалить файлы
     * 
     * @param string $path
     * 
     * @return bool
     */
    public static function recursive_files_remove(string $path) : bool {
      $files_array_on_path = array_diff(scandir($path), ['..', '.']);

      if (count($files_array_on_path) > 0) {
        foreach ($files_array_on_path as $file) {
          $file_path = sprintf('%s/%s', $path, $file);
          
          if (is_dir($file_path)) {
            self::recursive_files_remove($file_path);
          } else {
            unlink($file_path);
          }
        }

        rmdir($path);
        return true;
      } else {
        rmdir($path);
        return true;
      }

      return false;
    }
    
    /**
     * Парсинг HTTP-запроса
     *
     * @param  mixed $input_string
     * @return void
     */
    public static function parse_raw_http_request(string $input_string, string $content_type) {
      // grab multipart boundary from content type header
      preg_match('/boundary=(.*)$/', $content_type, $matches);
      $boundary = $matches[1];
      
      // split content by boundary and get rid of last -- element
      $array_blocks = preg_split("/-+$boundary/", $input_string);
      array_pop($array_blocks);
      
      $data_array = [];
      // loop data blocks
      foreach ($array_blocks as $index => $block) {
        if (empty($block)) continue;

        // parse uploaded files
        if (strpos($block, 'application/octet-stream') !== false) {
          // match "name", then everything after "stream" (optional) except for prepending newlines 
          preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
        } else {
          // match "name" and optional value in between newline sequences
          preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
        }

        if (isset($matches[2])) {
          if (preg_match('/(.*)\[\]$/', $matches[1], $matches_name)) {
            $data_array[$matches_name[1]][] = $matches[2];
          } else {
            $data_array[$matches[1]] = $matches[2];
          }
        }
      }   
      
      return $data_array;
    }

  }

}

?>
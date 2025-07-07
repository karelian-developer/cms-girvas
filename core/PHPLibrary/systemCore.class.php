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
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\SystemCore\Configurator as CMSConfigurator;
use \core\PHPLibrary\SystemCore\Header as CMSHeader;
use \core\PHPLibrary\SystemCore\Header\HTTPReferrerPolicy as CMSHeaderHTTPReferrerPolicy;
use \core\PHPLibrary\SystemCore\Header\EnumHTTPReferrerPolicy as CMSHeaderEnumHTTPReferrerPolicy;
use \core\PHPLibrary\SystemCore\EnumHeader as CMSEnumHeader;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\SystemCore\DatabaseConnector as CMSDatabaseConnector;
use \core\PHPLibrary\SystemCore\FileConnector as CMSFileConnector;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Client as Client;
use \DOMDocument as DOMDocument;
  
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
final class SystemCore
{
  public const CMS_CORE_PATH = 'core';
  public const CMS_CORE_PHP_LIBRARY_PATH = 'core/PHPLibrary';
  public const CMS_CORE_JS_LIBRARY_PATH = 'core/JSLibrary';
  public const CMS_CORE_TS_LIBRARY_PATH = 'core/TSLibrary';
  public const CMS_MODULES_PATH = 'modules';
  public const CMS_TITLE = 'CMS GIRVAS';
  public const CMS_VERSION = '0.1.36-19.4';
  public const CMS_STAGE_DEVELOPING = 'alpha';
  public const CMS_DEVELOPER_TITLE = 'Карельский разработчик';
  public const CMS_DEVELOPER_SITE_LINK = 'https://www.garbalo.com';
  public const CMS_PRODUCT_SITE_LINK = 'https://www.cms-girvas.ru';
  public const CMS_REESTR_DIGITAL_GOV_LINK = 'https://reestr.digital.gov.ru/reestr/2840045/?sphrase_id=5944628';
  public string $CSPScriptsHash, $CSPStylesHash;

  /** 
   * @var CMSConfigurator Конфигуратор системы
   */
  public CMSConfigurator|null $configurator = null;
  /** 
   * @var CMSDatabaseConnector Класс системы подключения к БД 
   */
  public CMSDatabaseConnector|null $databaseConnector = null;
  /** 
   * @var CMSLocale Класс локализации ядра 
   */
  public CMSLocale|null $locale = null;
  /**
   * @var URLParser Класс парсера адресной строки 
   */
  public URLParser|null $urlp = null;
  /** 
   * @var Client Класс клиента
   */
  public Client|null $client = null;
  /** 
   * @var Theme Класс шаблона системы 
   */
  public Theme|null $theme = null;

  /**
   *  @var array Массив активированных модулей
   * */
  public array $modules = [];
  /**
   * @var array Массив элементов пути до инициализированной страницы
   */
  public array $pageDirArray = [];
  /**
   * @var array Объект текущей страницы
   */
  public mixed $page = null;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    // Инициализация ядра системы
    $this->init();
  }

  /**
   * Получить наименование системы
   * 
   * @return string
   */
  public function getCMSTitle() : string
  {
    return self::CMS_TITLE;
  }

  /**
   * Получить текущую версию системы
   * 
   * @return string
   */
  public function getCMSVersion() : string
  {
    return self::CMS_VERSION;
  }

  /**
   * Получить наименование стадии разработки
   * 
   * @return string
   */
  public function getCMSStageDeveloping() : string
  {
    return self::CMS_STAGE_DEVELOPING;
  }

  /**
   * Получить объект локализации ядра
   * 
   * Стандартный набор:
   * base - базовая локализация (веб-сайт)
   * admin - административная локализация (АП)
   * 
   * @param string $localeType
   * 
   * @return CMSLocale
   */
  public function getCMSLocale(string $localeType = 'base') : CMSLocale
  {
    $CMSConfigurator = $this->configurator;

    $localeName = match ($localeType) {
      'base' => $CMSConfigurator->getDatabaseEntryValue('base_locale') ?? CMSLocale::DEFAULT_LOCALE_NAME,
      'admin' => $CMSConfigurator->getDatabaseEntryValue('base_admin_locale') ?? CMSLocale::DEFAULT_LOCALE_NAME,
      default => $CMSConfigurator->getDatabaseEntryValue($localeType . '_locale') ?? CMSLocale::DEFAULT_LOCALE_NAME,
    };

    $CMSLocale = new CMSLocale($this, $localeName);
    $CMSLocale->setTypeName($localeType);
    $CMSLocale->initPathes();

    return $CMSLocale;
  }

  /**
   * Получить домен из конфигурации
   * 
   * @return string
   */
  public function getCMSDomain() : string
  {
    $CMSConfigurator = $this->configurator;
    return $CMSConfigurator->get('domain') ?? 'errorhost';
  }

  /**
   * Получить внешнюю ссылку на систему
   * 
   * @return string
   */
  public function getCMSLink() : string
  {
    $CMSConfigurator = $this->configurator;

    if ($CMSConfigurator->get('domain') !== null) {
      $CMSConfigDomain = $CMSConfigurator->get('domain');

      return $CMSConfigurator->get('SSLIsEnabled')
        ? 'https://' . $CMSConfigDomain . '/'
        : 'http://' . $CMSConfigDomain . '/';
    }

    return 'errorhost';
  }

  /**
   * Установить шаблон для системы
   * 
   * @param Theme $theme
   * 
   * @return void
   */
  public function setTheme(Theme $theme) : void
  {
    $this->theme = $theme;
  }

  /**
   * Получить текущий шаблон
   * 
   * @return Theme
   */
  public function getTheme() : Theme
  {
    return $this->theme;
  }

  /**
   * Получить инициализированную страницу
   * 
   * @return InterfacePage
   */
  public function getInitedPage() : InterfacePage
  {
    return $this->pageDirArray[array_key_last($this->pageDirArray)];
  }

  /**
   * Получить копирайт в виде строки
   * 
   * @return string
   */
  public static function getCopyrightString() : string
  {
    // $document = new DOMDocument();

    // $copyrightContainerElement = $document->createElement('div');
    // $copyrightContainerElement->setAttribute('class', 'footer__copyright copyright');

    // $copyrightLabelSymbolNodeElement = $document->createTextNode('copy');
    // $copyrightLabelSpaceNodeElement = $document->createTextNode('nbsp');

    // $copyrightLabelSiteLinkElement = $document->createElement('a', '&laquo;Карельский разработчик&raquo;');
    // $copyrightLabelSiteLinkElement->setAttribute('href', 'https://xn----7sbbafuqffehcie7cvgcl5a9h7d.xn--p1ai/');
    // $copyrightLabelSiteLinkElement->setAttribute('title', 'Компания &laquo;Карельский разработчик&raquo;');
    // $copyrightLabelSiteLinkElement->setAttribute('target', '_blank');

    // $copyrightLabelDatesElement = $document->createElement('span', '&laquo;Карельский разработчик&raquo;');

    // $copyrightContainerElement->appendChild($copyrightLabelSymbolNodeElement);
    // $copyrightContainerElement->appendChild($copyrightLabelSpaceNodeElement);
    // $document->appendChild($copyrightContainerElement);

    return sprintf('<div class="footer__copyright"><span>&copy;&nbsp;<a href="%s" title="Garbalo Site Official" target="_blank">%s</a>.</span> <span>2021&nbsp;&mdash;&nbsp;%d. <span>All&nbsp;rights&nbsp;reserved.</span> <span>Powered&nbsp;by&nbsp;<a href="%s" title="CMS Site Official" target="_blank">CMS&nbsp;&laquo;GIRVAS&raquo;</a>.</span></div>', self::CMS_DEVELOPER_SITE_LINK, self::CMS_DEVELOPER_TITLE, date('Y'), self::CMS_PRODUCT_SITE_LINK);
  }

  /**
   * Инициализация страницы
   * 
   * @param string $dir
   * 
   * @return bool
   */
  public function initPage(string $dir) : bool
  {
    $CMSTheme = $this->theme;

    $dir = $dir === '' ? 'index' : $dir;
    $dir = rtrim($dir, '/');
    
    $this->pageDirArray = explode('/', $dir);

    $pageDirLastIndex = count($this->pageDirArray) - 1;
    $pageDirFirstElement = $this->pageDirArray[0];

    $this->pageDirArray[$pageDirLastIndex] = explode('?', $this->pageDirArray[$pageDirLastIndex]);
    $this->pageDirArray[$pageDirLastIndex] = $this->pageDirArray[$pageDirLastIndex][0];
    
    if ($pageDirFirstElement === $CMSTheme->getCategory()) {
      $this->pageDirArray[0] = ucfirst($pageDirFirstElement);
      array_push($this->pageDirArray, 'index');
    }
    
    $currentDirFinalArray = [];
    for ($indexA = 0; $indexA < count($this->pageDirArray); $indexA++) {
      $currentDirArray = [];
      for ($indexB = 0; $indexB < $indexA + 1; $indexB++) {
        array_push($currentDirArray, $this->pageDirArray[$indexB]);
      }

      $currentDir = implode('/', $currentDirArray);
      $classPath = CMS_ROOT_DIRECTORY . '/core/PHPLibrary/Page/' . $currentDir . '.class.php';
      
      if (file_exists($classPath)) {
        $currentDirLastKey = array_key_last($currentDirArray);
        $currentDirArray[$currentDirLastKey] = 'Page' . ucfirst($currentDirArray[$currentDirLastKey]);
        $currentDir = implode('/', $currentDirArray);
        $currentDir = str_replace('/', '\\', $currentDir);
        
        $class = '\\core\\PHPLibrary\\Page\\' . $currentDir;
        $this->page = new $class($this, new Page($this, $currentDirArray));

        if ($currentDirArray[0] === $CMSTheme->getCategory()) unset($currentDirArray[0]);
        $currentDirArray[$currentDirLastKey] =& $this->page;
        $currentDirFinalArray = $currentDirArray;
        break;
      }
    }

    if (empty($currentDirFinalArray)) {
      $currentDirFinalArray['oh_shit'] = 'karelia_forever';
    }

    $currentDirFinalLastKey = array_key_last($currentDirFinalArray);

    if (is_string($currentDirFinalArray[$currentDirFinalLastKey])) {
      $CMSTheme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      
      $class = sprintf('\\core\\PHPLibrary\\Page\\PageError', $currentDir);
      $this->page = new $class($this, new Page($this, $currentDirFinalArray), 404);
      $currentDirFinalArray[$currentDirFinalLastKey] =& $this->page;
    }

    $this->pageDirArray = $currentDirFinalArray;

    return true;
  }
  
  /**
   * Инициализация ядра системы и всех необходимых ее компонентов
   *
   * @return void
   */
  private function init()
  {
    // Принудительное подключение класса файлового подключателя
    require_once CMS_ROOT_DIRECTORY . '/' . self::CMS_CORE_PHP_LIBRARY_PATH . '/SystemCore/fileConnector.interface.php';
    require_once CMS_ROOT_DIRECTORY . '/' . self::CMS_CORE_PHP_LIBRARY_PATH . '/SystemCore/fileConnector.class.php';

    /** @var string Равномерно выбранные случайные байты */
    $bytes = random_bytes(16);

    /** @var string Случайная хэш-строка для идентификации ранее встроенных скриптов */
    $this->CSPScriptsHash = bin2hex($bytes);

    /** @var string Равномерно выбранные случайные байты */
    $bytes = random_bytes(16);

    /** @var string Случайная хэш-строка для идентификации ранее встроенных стилей (CSS) */
    $this->CSPStylesHash = bin2hex($bytes);

    /** @var CMSFileConnector Объект подключателя файлов */
    $CMSFileConnector = new CMSFileConnector($this);
    $CMSFileConnector = $this->autoloadComponents(
      $CMSFileConnector,
      self::CMS_CORE_PHP_LIBRARY_PATH,
      ['enum', 'interface', 'trait', 'class']
    );

    /** @var null Переменная для будущего объекта шаблона */
    $theme = null;

    // Инициализация URL-парсера
    $this->initURLParser();
    $CMSURLP = $this->urlp;

    /*
     * Если настройка системы не была произведена и пользователь не находится на странице
     * инсталлятора, то его необходимо перенаправить на страницу инсталлятора.
     */
    if (
      !self::CMSIsInstall()
      && $CMSURLP->getPath(0) !== 'install'
      && $CMSURLP->getPath(0) !== 'handler'
    ) {
      header('location: /install');
    }

    if ($this->isLocationInstallerActive() && self::CMSIsInstall()) {
      die('This system is already installed!');
    }

    /** @var CMSConfigurator Объект конфигуратора системного ядра */
    $this->configurator = new CMSConfigurator($this);
    $CMSConfigurator = $this->configurator;

    // Подключение к базе данных
    if ($CMSURLP->getPath(0) !== 'install' && $CMSURLP->getPath(1) !== 'install' && $CMSURLP->getParam('installation-mode') !== 'true') {
      /** @var CMSDatabaseConnector Объект подключения к базе данных */
      $this->databaseConnector = new CMSDatabaseConnector($this, $CMSConfigurator);

      /** @var Client Объект клиента */
      $this->client = new Client($this);

      $serverHTTPHost = $_SERVER['HTTP_HOST'] ?? '';
      $serverRequestURI = $_SERVER['REQUEST_URI'] ?? '';

      // Ядро перенаправляет клиент на HTTPS-протокол, в случае, если в CMS включена принудительная
      // переадресация на этот порт.
      if (!self::isHTTPS() && $CMSConfigurator->get('SSLPermRedirect')) {
        /* 
        * Ядро перенаправляет клиент на поддомен WWW в случае, если данная опция включена
        * в настройках CMS.
        */
        if (
          $CMSConfigurator->getPermanentRedirectToWWWStatus()
          && !preg_match('/^www\./', $serverHTTPHost)
        ) {
          /** @var string Адрес для переадресации по HTTPS-протоколу (поддомен www) */
          $HTTPSRedirect = 'https://www.' . $serverHTTPHost . $serverRequestURI;
        } else {
          /** @var string Адрес для переадресации по HTTPS-протоколу */
          $HTTPSRedirect = 'https://' . $serverHTTPHost . $serverRequestURI;
        }

        // Сообщаем браузеру, что это принудительная переадресация
        CMSHeader::add(CMSEnumHeader::HTTP_RESPONSE_CODE, 301);
        CMSHeader::add(CMSEnumHeader::HTTP_LOCATION, $HTTPSRedirect);
        exit();
      }
      
      /* 
      * Ядро перенаправляет клиент на поддомен WWW в случае, если данная опция включена
      * в настройках CMS.
      */
      if (
        $CMSConfigurator->getPermanentRedirectToWWWStatus()
        && !preg_match('/^www\./', $serverHTTPHost)
      ) {
        /** @var string Адрес для переадресации по HTTP-протоколу (поддомен www) */
        $HTTPRedirect = 'http://www.' . $serverHTTPHost . $serverRequestURI;
        
        // Сообщаем браузеру, что это принудительная переадресация
        CMSHeader::add(CMSEnumHeader::HTTP_RESPONSE_CODE, 301);
        CMSHeader::add(CMSEnumHeader::HTTP_LOCATION, $HTTPRedirect);
        exit();
      }

      // Указываем серверу, что будем использовать временную зону для расчета времени, указанную в настройках CMS. 
      date_default_timezone_set($CMSConfigurator->getSiteTimezone());

      // Сообщаем браузеру, что для сайта деятсвуют особые правила безопасности
      CMSHeader::add(CMSEnumHeader::HTTP_CONTENT_SECURITY_POLICY, $CMSConfigurator->getSecurityCSP());
      header('Referrer-Policy: strict-origin-when-cross-origin');
      header('X-Content-Type-Options: nosniff');
      
      if ($CMSConfigurator->get('SSLIsEnabled')) {
        $HSTSVars = [];

        if ($CMSConfigurator->exists('SSLHSTSMaxAge')) {
          $CMSConfigSSLHSTSMaxAge = $CMSConfigurator->get('SSLHSTSMaxAge');

          if (is_integer($CMSConfigSSLHSTSMaxAge)) {
            array_push($HSTSVars, 'max-age=' . $CMSConfigSSLHSTSMaxAge);
          }
        }

        if ($CMSConfigurator->exists('SSLHSTSIncludeSubdomains')) {
          $CMSConfigSSLHSTSIncludeSubdomains = $CMSConfigurator->get('SSLHSTSIncludeSubdomains');

          if (is_bool($CMSConfigSSLHSTSIncludeSubdomains)) {
            if ($CMSConfigSSLHSTSIncludeSubdomains === true) {
              array_push($HSTSVars, 'includeSubDomains');
            }
          }
        }

        if ($CMSConfigurator->exists('SSLHSTSPreload')) {
          $CMSConfigSSLHSTSPreload = $CMSConfigurator->get('SSLHSTSPreload');

          if (is_bool($CMSConfigSSLHSTSPreload) && $CMSConfigSSLHSTSPreload === true) {
            array_push($HSTSVars, 'preload');
          }
        }

        header('Strict-Transport-Security: ' . implode('; ', $HSTSVars));
      }
    }

    if ($CMSURLP->getPath(0) === 'install' && $CMSURLP->getPath(1) !== 'install') {
      $installLocaleName = $CMSURLP->getParam('locale') ?? 'en_US';
    }

    /** @var array Массив установленных модулей в системе */
    $modulesInstalled = Modules::getInstalledModulesArray();
    if (!empty($modulesInstalled)) {
      foreach ($modulesInstalled as $index => $directoryName) {
        /** @var string Абсолютный путь до директории с модулями */
        $modulesDirectoryPath = Modules::getAbsoluteModulesPath();
        /** @var string Абсолютный путь до директории с модулем */
        $moduleDirectoryPath = $modulesDirectoryPath . '/' . $directoryName;
        /** @var Module Объект модуля */
        $module = new Module($this, $directoryName);

        if ($module->isEnabled()) {
          /** @var CMSFileConnector Объект подключателя файлов */
          $CMSFileConnector = new CMSFileConnector($this);
          $CMSFileConnector = $this->autoloadComponents(
            $CMSFileConnector,
            $moduleDirectoryPath,
            ['enum', 'interface', 'trait', 'class']
          );
          
          Module::connectCore($this, $directoryName);
        }

        unset($module);
      }
    }

    /** @var array Массив установленных модулей в системе */
    $modulesInstalled = Modules::getInstalledModulesArray();
    if (!empty($this->modules)) {
      foreach ($this->modules as $name => $moduleCore) {
        $module = new Module($this, $name);

        if ($module->isInstalled() && $module->isEnabled()) {
          $moduleCore->preparation();
        }

        unset($module);
      }
    }

    // ============================================================
    // Инициализация локализации системного ядра
    // ============================================================

    // Проверка активности локаций обработчика и фида
    if (!$this->isLocationHandlerActive() && !$this->isLocationFeedActive()) {
      // Проверка активности локации инсталлятора
      if (!$this->isLocationInstallerActive()) {
        /** @var string Наименование шаблона сайта */
        $themeBaseName = $CMSConfigurator->existsDatabaseEntryValue('base_template')
          ? $CMSConfigurator->getDatabaseEntryValue('base_template')
          : 'default';
        /** @var string Наименование шаблона административной панели */
        $themeAdminName = $CMSConfigurator->existsDatabaseEntryValue('admin_template')
          ? $CMSConfigurator->getDatabaseEntryValue('admin_template')
          : 'default';
      }
      
      /** @var string Имя локализации, определенное куки "locale" */
      $CMSLocaleCookie = $_COOKIE['locale'] ?? null;
      /** @var string Имя локализации, определенное параметром адресной строки параметром "locale" */
      $CMSLocaleURLParam = $CMSURLP->getParam('locale') ?? null;

      // Проверка активности локации инсталлятора и статуса установки системы
      if ($this->isLocationInstallerActive() && !self::CMSIsInstall()) {
        /** @var string Наименование локализации */
        $CMSLocaleName = $installLocaleName;
        /** @var string Наименование категории шаблона системного ядра */
        $CMSCoreThemeCategoryName = 'install';
      } else {
        // Определяем приоритетную локализацию системного ядра
        /** @var string Наименование локализации */
        $checkedLocaleName = $CMSLocaleURLParam ?? $CMSLocaleCookie;
        // Если приоритетная локализация выбрана...
        if ($checkedLocaleName !== null) {
          // Проверяем наличие локализации в системе
          if (CMSLocale::exists($this, $checkedLocaleName)) {
            /** @var string Наименование локализации */
            $CMSLocaleName = $checkedLocaleName;
          }
        }

        /** @var string Наименование локализации сайта */
        $CMSBaseLocaleName = $CMSConfigurator->existsDatabaseEntryValue('base_locale') ? $CMSConfigurator->getDatabaseEntryValue('base_locale') : 'en_US';
        /** @var string Наименование локализации административной панели */
        $CMSAdminLocaleName = $CMSConfigurator->existsDatabaseEntryValue('base_admin_locale') ? $CMSConfigurator->getDatabaseEntryValue('base_admin_locale') : 'en_US';

        // Проверка активности административной панели
        if ($this->isLocationAdministrativePanelActive()) {
          /** @var string Наименование категории шаблона системного ядра */
          $CMSCoreThemeCategoryName = 'admin';
          /** @var string Наименование шаблона системного ядра */
          $CMSCoreThemeName = $themeAdminName;
          /** @var string Наименование локализации */
          $CMSLocaleName = $CMSLocaleName ?? $CMSAdminLocaleName;
        } else {
          /** @var string Наименование категории шаблона системного ядра */
          $CMSCoreThemeCategoryName = 'base';
          /** @var string Наименование шаблона системного ядра */
          $CMSCoreThemeName = $themeBaseName;
          /** @var string Наименование локализации */
          $CMSLocaleName = $CMSLocaleName ?? $CMSBaseLocaleName;
        }
      }

      // Если по какой-то причине имена категории шаблона и самого шаблона не определены,
      // то необходимо установить типовые значения.
      /** @var string Наименование категории шаблона системного ядра */
      $CMSCoreThemeCategoryName = $CMSCoreThemeCategoryName ?? 'base';
      /** @var string Наименование шаблона системного ядра */
      $CMSCoreThemeName = $CMSCoreThemeName ?? 'default';

      /** @var CMSLocale Объект локализации системного ядра */
      $this->locale = new CMSLocale($this, $CMSLocaleName);
      $this->locale->setTypeName($CMSCoreThemeCategoryName);
      $this->locale->initPathes();
      
      if ($CMSURLP->getPath(0) !== 'sql-execute-forced') {
        // Устанавливаем объект шаблона для системного ядра
        $this->setTheme(new Theme($this, $CMSCoreThemeName, $CMSCoreThemeCategoryName));

        /** @var Theme Объект шаблона системного ядра */
        $theme = $this->getTheme();
        // Инициализация шаблона системного ядра
        $theme->init();
      }
    } else {
      if ($CMSURLP->getPath(1) === 'install') {
        $localeName = $CMSURLP->getParam('locale') ?? 'en_US';
        $this->locale = new CMSLocale($this, $localeName);
        $this->locale->setTypeName('handler');
        $this->locale->initPathes();
      } else {
        if ($CMSURLP->getParam('localeMessage') === null) {
          $localeName = $CMSConfigurator->existsDatabaseEntryValue('base_locale') ? $CMSConfigurator->getDatabaseEntryValue('base_locale') : 'en_US';
        } else {
          $localeName = $CMSURLP->getParam('localeMessage');
        }
      }

      $this->locale = new CMSLocale($this, $localeName);
      $this->locale->setTypeName('handler');
      $this->locale->initPathes();
    }
    
    if ($theme !== null) {
      $theme->core->assembled = $theme->getCoreAssembled();
    }

    $modulesInstalled = Modules::getInstalledModulesArray();
    if (!empty($this->modules)) {
      foreach ($this->modules as $name => $moduleCore) {
        $module = new Module($this, $name);
        
        if ($module->isInstalled() && $module->isEnabled() && $theme !== null) {
          $theme->core->assembled = ThemeCollector::assemblyLocale(
            $theme->core->assembled,
            $module->locale
          );
        }

        unset($module);
      }
    }

    if ($theme !== null) {
      $document = new DOMDocument();
      @$document->loadHTML($theme->core->assembled);

      $scriptElements = $document->getElementsByTagName('script');
      foreach ($scriptElements as $scriptElement) {
        $scriptElement->setAttribute('nonce', $this->CSPScriptsHash);
      }

      $styleElements = $document->getElementsByTagName('style');
      foreach ($styleElements as $styleElement) {
        $styleElement->setAttribute('nonce', $this->CSPStylesHash);
      }

      $theme->core->source = $document;

      if ($CMSURLP->getPath(0) === 'admin') {
        if (method_exists($theme->core, 'initMainNavigation')) {
          $theme->core->initMainNavigation();
        }

        $CMSPage = $this->page;

        if ($CMSPage !== null) {
          if (method_exists($CMSPage, 'initSubnavigation')) {
            $CMSPage->initSubnavigation();
          }
        }
      }
    }

    if (!empty($this->modules)) {
      foreach ($this->modules as $name => $moduleCore) {
        $module = new Module($this, $name);
        
        if ($module->isInstalled() && $module->isEnabled()) {
          $moduleCore->init();
        }

        unset($module);
      }
    }

    if ($theme !== null) {
      $theme->core->assembled = $document->saveHTML();
    }
  }
  
  /**
   * Получить путь до корня ситемы
   *
   * @return string
   */
  public function getCMSPath() : string
  {
    return $_SERVER['DOCUMENT_ROOT'];
  }
  
  /**
   * Получить массив имен загруженных шаблонов
   *
   * @return array
   */
  public function getArrayUploadedTemplatesNames() : array
  {
    $path = $this->getCMSPath() . '/templates';
    return array_diff(scandir($path), ['..', '.']);
  }
  
  /**
   * Получить массив имен загруженных модулей
   *
   * @return array
   */
  public function getArrayUploadedModulesNames() : array
  {
    $path = $this->getCMSPath() . '/modules';
    return array_diff(scandir($path), ['..', '.']);
  }
  
  /**
   * Получить массив имен локализаций
   *
   * @return array
   */
  public function getArrayLocalesNames() : array
  {
    $path = $this->getCMSPath() . '/locales';
    return array_diff(scandir($path), ['..', '.']);
  }
  
  /**
   * Получить внешнюю ссылку до сайта
   *
   * @return string
   */
  public function geSiteURL() : string
  {
    $CMSConfigurator = $this->configurator;
    $CMSConfigSSLIsEnabled = $CMSConfigurator->get('SSLIsEnabled');
    $CMSConfigDomainProtocol = $CMSConfigSSLIsEnabled ? 'https://' : 'http://';

    return $CMSConfigDomainProtocol . $CMSConfigurator->get('domain');
  }

  /**
   * Проверка активности инсталлятора (по локации клиента)
   * 
   * @return bool
   */
  public function isLocationInstallerActive() : bool
  {
    return $this->urlp->getPath(0) === 'install';
  }

  /**
   * Проверка активности административной панели (по локации клиента)
   * 
   * @return bool
   */
  public function isLocationAdministrativePanelActive() : bool
  {
    return $this->urlp->getPath(0) === 'admin';
  }

  /**
   * Проверка активности обработчика (по локации клиента)
   * 
   * @return bool
   */
  public function isLocationHandlerActive() : bool
  {
    return $this->urlp->getPath(0) === 'handler';
  }

  /**
   * Проверка активности фида (по локации клиента)
   * 
   * @return bool
   */
  public function isLocationFeedActive() : bool
  {
    return $this->urlp->getPath(0) === 'feed';
  }
  
  /**
   * Инициализация URL-парсера
   *
   * @return void
   */
  private function initURLParser() : void
  {
    $this->urlp = new URLParser();
  }

  /**
   * Рекурсивно удалить файлы
   * 
   * @param string $path
   * 
   * @return bool
   */
  public static function recursiveFilesRemove(string $path) : bool
  {
    $filesArrayOnPath = array_diff(scandir($path), ['..', '.']);

    if (count($filesArrayOnPath) > 0) {
      foreach ($filesArrayOnPath as $file) {
        $path = $path . '/' . $file;
        
        if (is_dir($path)) {
          self::recursiveFilesRemove($path);
        } else {
          unlink($path);
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
   * @param  string $inputString
   * @param  string $contentType
   * 
   * @return array
   */
  public static function parseRawHTTPRequest(string $inputString, string $contentType) : array
  {
    // grab multipart boundary from content type header
    preg_match('/boundary=(.*)$/', $contentType, $matches);
    $boundary = $matches[1];
    
    // split content by boundary and get rid of last -- element
    $arrayBlocks = preg_split("/-+$boundary/", $inputString);
    array_pop($arrayBlocks);
    
    $dataArray = [];
    // loop data blocks
    foreach ($arrayBlocks as $index => $block) {
      if (empty($block)) continue;

      // parse uploaded files
      if (strpos($block, 'application/octet-stream') !== false) {
        // match "name", then everything after "stream" (optional) except for prepending newlines 
        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+(.*)?$/s', $block, $matches);
      } else {
        // match "name" and optional value in between newline sequences
        preg_match('/name=\"([^\"]*)\"[\n|\r]+(.*)?\r$/s', $block, $matches);
      }

      if (isset($matches[2])) {
        if (preg_match('/(.*)\[\]$/', $matches[1], $matchesName)) {
          $dataArray[$matchesName[1]][] = $matches[2];
        } else {
          $dataArray[$matches[1]] = $matches[2];
        }
      }
    }   
    
    return $dataArray;
  }

  /**
   * Получение куки REST API ядра
   *
   * @return int
   */
  public static function getCoreRESTCookie() : int
  {
    if (isset($_COOKIE['_grv_rest'])) {
      return (is_numeric($_COOKIE['_grv_rest'])) ? (int) $_COOKIE['_grv_rest'] : 0;
    }

    return 0;
  }

  /**
   * Проверка существования куки REST API ядра
   *
   * @return bool
   */
  public static function coreRESTCookieExists() : bool
  {
    return isset($_COOKIE['_grv_rest']);
  }

  /**
   * Проверка валидации куки REST API ядра
   *
   * @param  int $value
   * @param  string $ip
   * 
   * @return bool
   */
  public static function coreRESTCookieIsValid(int $value, string $ip) : bool
  {
    $ip = str_replace('.', '', $ip);

    if ($value === (int) (((int) $ip * (round(asin(1) * strlen($ip)) << 3)) . strtotime(date('Y/m/d 00:00:00.0')))) {
      return true;
    }

    return false;
  }

  /**
   * Аварийное завершение работы ядра
   * 
   * ВНИМАНИЕ! Вызов данного метода оборвет выполнение
   * последующего кода и выведет сообщение об ошибке.
   *
   * @param  int $reasonID
   * @param  int $statusCode
   * @param  bool $isJSON
   * 
   * @return void
   */
  public static function abnormalTerminationOfWork(int $reasonID, int $statusCode, bool $isJSON = false) : void
  {
    if ($reasonID === 1) {
      $message = 'An attempted hacker attack has been detected.';
    }

    $message = $message ?? 'The system core has terminated abnormally for an unknown reason.';

    $outputData = isset($outputData) ? $outputData : [];
    $outputData = is_array($outputData) ? $outputData : [];

    http_response_code($statusCode);

    if ($isJSON) {
      die(json_encode([
        'message' => $message,
        'statusCode' => $statusCode,
        'outputData' => $outputData
      ]));
    }

    die($message);
  }

  /**
   * Проверить статус установки системы
   * 
   * @return bool
   */
  public static function CMSIsInstall() : bool
  {
    /** @var string Абсолютный путь до файла-пустышки "INSTALLED" */
    $path = CMS_ROOT_DIRECTORY . '/INSTALLED';
    return file_exists($path);
  }

  /**
   * Автоподключение компонентов системы
   * 
   * @return void
   */
  private function autoloadComponents(CMSFileConnector $CMSFileConnector, string $pathToFiles, array $filesTypes = []) : void
  {
    $CMSFileConnector->setStartDirectory($pathToFiles);
    $CMSFileConnector->setCurrentDirectory($pathToFiles);

    foreach ($filesTypes as $type) {
      $CMSFileConnector->connectFilesRecursive('/^([a-zA-Z_0-9]+)\.' . $type . '\.php$/');
      $CMSFileConnector->resetCurrentDirectory();
    }
  }

  /**
   * Проверка наличия HTTPS-соединения
   * 
   * @return bool
   */
  public static function isHTTPS() : bool
  {
    $HTTPS = $_SERVER["HTTPS"] ?? '';
    
    // Основная проверка
    if ($HTTPS === 'on' || $HTTPS === '1') {
      return true;
    }

    // Проверка для облачных окружений и прокси
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
      return $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
    }

    return ($_SERVER['SERVER_PORT'] ?? null) === 443;
  }
}
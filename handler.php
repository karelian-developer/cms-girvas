<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (!isset($CMSCore)) {
  http_response_code(500);
  die('CMS system core not initialized.');
}

if (defined('IS_NOT_HACKED')) {
  $CMSURLP = $CMSCore->urlp;
  $CMSClient = $CMSCore->client;
  $CMSConfigurator = $CMSCore->configurator;

  header('Access-Control-Allow-Origin: ' . $CMSConfigurator->get('domain'));

  $handlerHeaders = apache_request_headers();
  $normalizedHeaders = array_change_key_case($handlerHeaders, CASE_LOWER);

  $PHPInputContent = file_get_contents('php://input');

  if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $CMSURLP->getPath(1) !== 'install') {
    $cookieToken = $_COOKIE['_grv_csrf'] ?? null;

    $headerToken = $normalizedHeaders['x-csrf-token'] ?? null;

    if ($cookieToken === null || $headerToken === null || !hash_equals($cookieToken, $headerToken)) {
      $handlerMessage = $handlerMessage ?? 'The request was rejected by the security system.';
      $handlerStatusCode = $handlerStatusCode ?? 0;

      echo json_encode([
        'message' => $handlerMessage,
        'statusCode' => $handlerStatusCode,
        'outputData' => []
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      exit;
    }
  }

  if (isset($_SERVER['REQUEST_METHOD'])) {
    switch ($_SERVER['REQUEST_METHOD']) {
      case 'PATCH': $_PATCH = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
      case 'PUT': $_PUT = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
      case 'DELETE': $_DELETE = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
    }
  } else {
    http_response_code(405);

    $handlerMessage = $handlerMessage ?? 'Request method is undefined.';
    $handlerStatusCode = $handlerStatusCode ?? 0;

    echo json_encode([
      'message' => $handlerMessage,
      'statusCode' => $handlerStatusCode,
      'outputData' => []
    // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  $handlerOutputData = [];

  /** ===================================================
   * Обработчик CMS GIRVAS
   * ==================================================== */

  $CMSURLPathes = [];
  $CMSURLPathes[] = $CMSURLP->getPath(0);
  $CMSURLPathes[] = $CMSURLP->getPath(1);
  $CMSURLPathes[] = $CMSURLP->getPath(2);
  $CMSURLPathes[] = $CMSURLP->getPath(3);

  // Client API
  if ($CMSURLPathes[1] === 'client') {
    $APIFilePath = CMS_ROOT_DIRECTORY . '/api/client.api.php';
    include_once $APIFilePath;

  // Installation API
  } else if ($CMSURLPathes[1] === 'install') {
    $APIFilePath = CMS_ROOT_DIRECTORY . '/api/installation.api.php';
    include_once $APIFilePath;
  
  // Metrics API
  } else if ($CMSURLPathes[1] === 'metrics' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/metrics.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'media' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/media.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'module' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/module.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'user' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/user.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'usersGroup' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/usersGroup.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'usersGroups' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/usersGroups.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'entry' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entry.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'entries' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'pageStatic' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/pageStatic.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'pages' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/pagesStatic.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'settings' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/settings.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'template' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/template.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'feed' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/feed.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'feeds' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/feeds.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'form' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/form.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'forms' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/forms.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'profile' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/profile.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSURLPathes[1] === 'utils' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSClient->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/utils.api.php';
      include_once $APIFilePath;
    }
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'dms-available') {
    $handlerOutputData['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение текущей кодировки
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'charset') {
    $charset = $CMSConfigurator->existsDatabaseEntryValue('base_site_charset') ? $CMSConfigurator->getDatabaseEntryValue('base_site_charset') : 'UTF-8';
    $handlerOutputData['charset'] = $charset;
  
  // Получение расширения, в которое производится конвертация
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'file-auto-convert-image-extension') {
    $extension = $CMSConfigurator->existsDatabaseEntryValue('files_auto_convert_file_image_extension') ? $CMSConfigurator->getDatabaseEntryValue('files_auto_convert_file_image_extension') : 'webp';
    $handlerOutputData['extension'] = $extension;

  // Получение перечня кодировок
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'charsets') {
    $handlerOutputData['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение статуса технических работ
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'ew-status') {
    $EWStatus = $CMSConfigurator->existsDatabaseEntryValue('base_engineering_works_status') ? $CMSConfigurator->getDatabaseEntryValue('base_engineering_works_status') : 'off';
    $handlerOutputData['status'] = $EWStatus === 'on' ? 'on' : 'off';
  
  // Получение текущего временной зоны
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'timezone') {
    $timezoneName = $CMSConfigurator->existsDatabaseEntryValue('base_timezone') ? $CMSConfigurator->getDatabaseEntryValue('base_timezone') : date_default_timezone_get();
    $timezoneUTC = new DateTimeImmutable('now', new DateTimeZone($timezoneName));

    $handlerOutputData['timezone'] = [
      'name' => $timezoneName,
      'utc' => $timezoneUTC->format('P')
    ];
  
  // Получение списка временных зон
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'timezones') {
    $timezones = [];
    $timezoneNames = DateTimeZone::listIdentifiers();
    foreach ($timezoneNames as $name) {
      $timezoneUTC = new DateTimeImmutable('now', new DateTimeZone($name));

      array_push($timezones, [
        'name' => $name,
        'utc' => $timezoneUTC->format('P')
      ]);
    }

    $handlerOutputData['timezones'] = $timezones;
  
  // Получение дополнительной информации по профилю пользователя
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'locale') {
    // Базовая локализация
    if ($CMSURLPathes[2] === 'base') {
      $CMSLocaleSetted = $CMSConfigurator->getDatabaseEntryValue('base_locale') ?? 'en_US';
      $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleSetted);
      $CMSLocale->setTypeName('handler');
      $CMSLocale->initPathes();

      $handlerOutputData['locale'] = [
        'title' => $CMSLocale->getTitle(),
        'iconURL' => $CMSLocale->getIconURL(),
        'name' => $CMSLocale->getName(),
        'iso639_1' => $CMSLocale->getISO639(1),
        'iso639_2' => $CMSLocale->getISO639(2),
      ];
    }

    // Локализация административной панели
    if ($CMSURLPathes[2] === 'admin') {
      $CMSLocaleSetted = $CMSConfigurator->getDatabaseEntryValue('base_admin_locale') ?? 'en_US';
      $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleSetted);
      $CMSLocale->setTypeName('handler');
      $CMSLocale->initPathes();

      $handlerOutputData['locale'] = [
        'title' => $CMSLocale->getTitle(),
        'iconURL' => $CMSLocale->getIconURL(),
        'name' => $CMSLocale->getName(),
        'iso639_1' => $CMSLocale->getISO639(1),
        'iso639_2' => $CMSLocale->getISO639(2),
      ];
    }
  
  // Получить перечень доступных локализаций
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSURLPathes[1] === 'locales') {
    $handlerOutputData['locales'] = [];
    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();

    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new CMSLocale($CMSCore, $name);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();

        if ($CMSLocale->existsFileMetadataJSON()) {
          array_push($handlerOutputData['locales'], [
            'title' => $CMSLocale->getTitle(),
            'iconURL' => $CMSLocale->getIconURL(),
            'name' => $CMSLocale->getName(),
            'iso639_1' => $CMSLocale->getISO639(1),
            'iso639_2' => $CMSLocale->getISO639(2),
          ]);
        }
      }

      $handlerMessage = $handlerMessage ?? 'Данные по локализациям успешно получены.';
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'Данные по локализациям не были получены, поскольку они не обнаружены в системе.';
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  
  } else {
    if ($CMSURLPathes[1] !== null) {
      /**
       * Рекурсивный поиск файла обработчика в директории API
       */
      $recursionHandlerConnect = function(CMSCore $CMSCore, array $pathes, int $level) use (&$recursionHandlerConnect) : string|null
      {
        $CMSURLP = $CMSCore->urlp;
        $handlersDirectoryPath = CMS_ROOT_DIRECTORY . '/api';

        $files = array_diff(scandir($handlersDirectoryPath), ['.', '..']);
        foreach ($files as $index => $name) {
          $path = isset($pathes[$level]) ? $pathes[$level] : null;
          error_log($path);
          if ($path !== null) {
            if (array_key_last($pathes) !== $level) {
              if ($name === $pathes[$level]) {
                $URLPathes = $CMSURLP->getPathes();
                return $recursionHandlerConnect($CMSCore, $URLPathes, $level + 1);
              }
            } else {
              $handlerFileName = $pathes[$level] . '.api.php';
              $handlerFilePath = $handlersDirectoryPath . '/' . implode('/', array_slice($pathes, 1, count($pathes) - 2)) . '/' .  $handlerFileName;
              
              if (file_exists($handlerFilePath)) {
                return $handlerFilePath;
              }

              break;
            }
          }
        }

        return null;
      };

      $URLPathes = $CMSURLP->getPathes();
      $handlerConnectionResult = $recursionHandlerConnect($CMSCore, $URLPathes, 1);
      
      if ($handlerConnectionResult !== null) {
        include_once $handlerConnectionResult;
      }
    }
  }

  /** @var string $handlerMessage Сообщение обработчика */
  $handlerMessage = $handlerMessage ?? 'The GIRVAS CMS handler was unable to process the request.';

  /** @var int $handlerStatusCode Статус обработчика */
  $handlerStatusCode = $handlerStatusCode ?? 0;

  /** @var array $handlerOutputData Выходные данные обработчика */
  $handlerOutputData = $handlerOutputData ?? [];
  $handlerOutputData['debug']['method'] = $_SERVER['REQUEST_METHOD'];
  $handlerOutputData['debug']['clientIP'] = $_SERVER['REMOTE_ADDR'];
  $handlerOutputData['debug']['postData'] = $_POST ?? null;
  $handlerOutputData['debug']['getData'] = $_GET ?? null;
  $handlerOutputData['debug']['patchData'] = $_PATCH ?? null;
  $handlerOutputData['debug']['putData'] = $_PUT ?? null;
  $handlerOutputData['debug']['deleteData'] = $_DELETE ?? null;

  $loadTime = microtime(true) - $startTime; // Конечное время
  header('X-Load-Time: ' . round($loadTime, 3) . 's');

  // Выводим результат работы обработчика в JSON-формате
  echo json_encode([
    'message' => $handlerMessage,
    'statusCode' => $handlerStatusCode,
    'outputData' => $handlerOutputData
  // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
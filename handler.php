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
  header('Access-Control-Allow-Origin: ' . $CMSCore->configurator->get('domain'));

  $handlerHeaders = apache_request_headers();
  $PHPInputContent = file_get_contents('php://input');

  switch ($_SERVER['REQUEST_METHOD']) {
    case 'PATCH': $_PATCH = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
    case 'PUT': $_PUT = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
    case 'DELETE': $_DELETE = $CMSCore::parseRawHTTPRequest($PHPInputContent, $_SERVER['CONTENT_TYPE']); break;
  }

  $handlerOutputData = [];

  /** ===================================================
   * Обработчик CMS GIRVAS
   * ==================================================== */

  // Client API
  if ($CMSCore->urlp->getPath(1) === 'client') {
    $APIFilePath = CMS_ROOT_DIRECTORY . '/api/client.api.php';
    include_once $APIFilePath;

  // Installation API
  } else if ($CMSCore->urlp->getPath(1) === 'install') {
    $APIFilePath = CMS_ROOT_DIRECTORY . '/api/installation.api.php';
    include_once $APIFilePath;
  
  // Metrics API
  } else if ($CMSCore->urlp->getPath(1) === 'metrics' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/metrics.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'media' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/media.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'module' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/module.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'user' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/user.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'usersGroup' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/usersGroup.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'usersGroups' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/usersGroups.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'entry' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entry.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'entries' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'pageStatic' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/pageStatic.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'settings' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/settings.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'template' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/template.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'feed' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/feed.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'feeds' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/feeds.api.php';
      include_once $APIFilePath;
    }
  } else if ($CMSCore->urlp->getPath(1) === 'utils' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      $APIFilePath = CMS_ROOT_DIRECTORY . '/api/utils.api.php';
      include_once $APIFilePath;
    }
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'dms-available') {
    $handlerOutputData['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение текущей кодировки
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'charset') {
    $charset = $CMSCore->configurator->existsDatabaseEntryValue('base_site_charset') ? $CMSCore->configurator->getDatabaseEntryValue('base_site_charset') : 'UTF-8';
    $handlerOutputData['charset'] = $charset;
  
  // Получение расширения, в которое производится конвертация
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'file-auto-convert-image-extension') {
    $extension = $CMSCore->configurator->existsDatabaseEntryValue('files_auto_convert_file_image_extension') ? $CMSCore->configurator->getDatabaseEntryValue('files_auto_convert_file_image_extension') : 'webp';
    $handlerOutputData['extension'] = $extension;

  // Получение перечня кодировок
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'charsets') {
    $handlerOutputData['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение статуса технических работ
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'ew-status') {
    $EWStatus = $CMSCore->configurator->existsDatabaseEntryValue('base_engineering_works_status') ? $CMSCore->configurator->getDatabaseEntryValue('base_engineering_works_status') : 'off';
    $handlerOutputData['status'] = $EWStatus === 'on' ? 'on' : 'off';
  
  // Получение текущего временной зоны
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'timezone') {
    $timezoneName = $CMSCore->configurator->existsDatabaseEntryValue('base_timezone') ? $CMSCore->configurator->getDatabaseEntryValue('base_timezone') : date_default_timezone_get();
    $timezoneUTC = new DateTimeImmutable('now', new DateTimeZone($timezoneName));

    $handlerOutputData['timezone'] = [
      'name' => $timezoneName,
      'utc' => $timezoneUTC->format('P')
    ];
  
  // Получение списка временных зон
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'timezones') {
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
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'profile' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      if ($CMSCore->urlp->getPath(2) === 'additional-fields') {
        $CMSLocaleSetted = $CMSCore->configurator->getDatabaseEntryValue('base_locale');
        $fieldsLocale = $CMSCore->urlp->getParam('locale') ?? $CMSLocaleSetted;

        $fieldsTypes = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_type') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_type'), true) : [];
        $fieldsTitles = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_title') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_title'), true) : [];
        $fieldsDescriptions = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_description') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_description'), true) : [];
        $fieldsNames = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_name') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_name'), true) : [];
        
        $fields = [];
        foreach ($fieldsTypes as $index => $type) {
          array_push($fields, [
            'type' => $type,
            'title' => isset($fieldsTitles[$fieldsLocale]) ? $fieldsTitles[$fieldsLocale][$index] : '',
            'description' => isset($fieldsDescriptions[$fieldsLocale]) ? $fieldsDescriptions[$fieldsLocale][$index] : '',
            'name' => $fieldsNames[$index]
          ]);
        }

        $handlerOutputData['additionalFields'] = $fields;
      }
    }
  // Получить текущую локализацию
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'locale') {
    // Базовая локализация
    if ($CMSCore->urlp->getPath(2) === 'base') {
      $CMSLocaleSetted = $CMSCore->configurator->getDatabaseEntryValue('base_locale') ?? 'en_US';
      $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleSetted);
      $handlerOutputData['locale'] = [
        'title' => $CMSLocale->getTitle(),
        'iconURL' => $CMSLocale->getIconURL(),
        'name' => $CMSLocale->getName(),
        'iso639_1' => $CMSLocale->getISO639(1),
        'iso639_2' => $CMSLocale->getISO639(2),
      ];
    }

    // Локализация административной панели
    if ($CMSCore->urlp->getPath(2) === 'admin') {
      $CMSLocaleSetted = $CMSCore->configurator->getDatabaseEntryValue('base_admin_locale') ?? 'en_US';
      $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleSetted);
      $handlerOutputData['locale'] = [
        'title' => $CMSLocale->getTitle(),
        'iconURL' => $CMSLocale->getIconURL(),
        'name' => $CMSLocale->getName(),
        'iso639_1' => $CMSLocale->getISO639(1),
        'iso639_2' => $CMSLocale->getISO639(2),
      ];
    }
  
  // Получить перечень доступных локализаций
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'locales') {
    $handlerOutputData['locales'] = [];
    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();

    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new CMSLocale($CMSCore, $name);
        $this->locale->initPathes();

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
  
  } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $CMSCore->urlp->getPath(1) === 'pages' && $CMSCore::coreRESTCookieExists()) {
    $CMSCoreRESTCookie = $CMSCore::getCoreRESTCookie();
    $clientIP = $CMSCore->client->getIPAddress();

    if ($CMSCore::coreRESTCookieIsValid($CMSCoreRESTCookie, $clientIP)) {
      if ($CMSCore->urlp->getPath(2) === 'additional-fields' && $CMSCore->urlp->getPath(3) !== null) {
        $CMSLocaleSetted = $CMSCore->configurator->getDatabaseEntryValue('base_locale');
        $fieldsLocale = $CMSCore->urlp->getParam('locale') ?? $CMSLocaleSetted;

        $fieldsTypes = $CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_type') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_type'), true) : [];
        $fieldsTitles = $CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_title') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_title'), true) : [];
        $fieldsDescriptions = $CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_description') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_description'), true) : [];
        $fieldsNames = $CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_name') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_name'), true) : [];
        
        $fields = [];
        foreach ($fieldsTypes as $index => $type) {
          array_push($fields, [
            'type' => $type,
            'title' => isset($fieldsTitles[$fieldsLocale]) ? $fieldsTitles[$fieldsLocale][$index] : '',
            'description' => isset($fieldsDescriptions[$fieldsLocale]) ? $fieldsDescriptions[$fieldsLocale][$index] : '',
            'name' => $fieldsNames[$index]
          ]);
        }

        $handlerOutputData['additionalFields'] = $fields;
      }
    }
  // Попытка инициализации персонализированного обработчика
  } else {
    if ($CMSCore->urlp->getPath(1) !== null) {

      /**
       * Рекурсивный поиск файла обработчика в директории API
       */
      $recursionHandlerConnect = function(CMSCore $CMSCore, array $pathes, int $index) use (&$recursionHandlerConnect) : string|null {
        $handlersDirectoryPath = CMS_ROOT_DIRECTORY . '/api';

        $files = array_diff(scandir($handlersDirectoryPath), ['.', '..']);
        foreach ($files as $index => $name) {
          if (array_key_last($pathes) != $index) {
            if ($name === $pathes[$index]) {

              $URLPathes = $CMSCore->urlp->getPathes();
              return $recursionHandlerConnect($CMSCore, $URLPathes, $index + 1);
            }
          } else {
            $handlerFileName = $pathes[$index] . '.api.php';
            $handlerFilePath = $handlersDirectoryPath . '/' . implode('/', array_slice($pathes, 1, count($pathes) - 2)) . '/' .  $handlerFileName;
            
            if (file_exists($handlerFilePath)) {
              return $handlerFilePath;
            }

            break;
          }
        }

        return null;
      };

      $URLPathes = $CMSCore->urlp->getPathes();
      $handlerConnectionResult = $recursionHandlerConnect($CMSCore, $URLPathes, 1);
      
      if ($handlerConnectionResult !== null) {
        include_once $handlerConnectionResult;
      }
    }
  }

  /** @var string $handlerMessage Сообщение обработчика */
  $handlerMessage = $handlerMessage ?? 'Обработчик CMS GIRVAS не смог обработать запрос.';
  /** @var int $handlerStatusCode Статус обработчика */
  $handlerStatusCode = $handlerStatusCode ?? 0;
  /** @var array $handlerOutputData Выходные данные обработчика */
  $handlerOutputData = $handlerOutputData ?? [];
  $handlerOutputData['debug']['method'] = $_SERVER['REQUEST_METHOD'];
  $handlerOutputData['debug']['client_ip'] = $_SERVER['REMOTE_ADDR'];
  $handlerOutputData['debug']['post_data'] = $_POST ?? null;
  $handlerOutputData['debug']['get_data'] = $_GET ?? null;
  $handlerOutputData['debug']['patch_data'] = $_PATCH ?? null;
  $handlerOutputData['debug']['put_data'] = $_PUT ?? null;
  $handlerOutputData['debug']['delete_data'] = $_DELETE ?? null;

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
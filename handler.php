<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (!isset($system_core)) {
  http_response_code(500);
  die('CMS system core not initialized.');
}

if (defined('IS_NOT_HACKED')) {
  header(sprintf('Access-Control-Allow-Origin: %s', $system_core->configurator->get('domain')));

  $handler_headers = apache_request_headers();

  switch ($_SERVER['REQUEST_METHOD']) {
    case 'PATCH': $_PATCH = $system_core::parse_raw_http_request(file_get_contents('php://input'), $_SERVER['CONTENT_TYPE']); break;
    case 'PUT': $_PUT = $system_core::parse_raw_http_request(file_get_contents('php://input'), $_SERVER['CONTENT_TYPE']); break;
    case 'DELETE': $_DELETE = $system_core::parse_raw_http_request(file_get_contents('php://input'), $_SERVER['CONTENT_TYPE']); break;
  }

  $handler_output_data = [];

  /** ===================================================
   * Обработчик CMS GIRVAS
   * ==================================================== */

  // Client API
  if ($system_core->urlp->get_path(1) == 'client') {
    $api_file_path = sprintf('%s/api/client.api.php', CMS_ROOT_DIRECTORY);
    include_once($api_file_path);

  // Installation API
  } else if ($system_core->urlp->get_path(1) == 'install') {
    $api_file_path = sprintf('%s/api/installation.api.php', CMS_ROOT_DIRECTORY);
    include_once($api_file_path);
  
  // Metrics API
  } else if ($system_core->urlp->get_path(1) == 'metrics' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/metrics.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'media' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/media.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'module' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/module.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'user' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/user.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'usersGroup' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/usersGroup.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'usersGroups' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/usersGroups.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'entry' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/entry.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'pageStatic' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/pageStatic.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'settings' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/settings.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'template' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/template.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'feed' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/feed.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'feeds' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/feeds.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($system_core->urlp->get_path(1) == 'utils' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      $api_file_path = sprintf('%s/api/utils.api.php', CMS_ROOT_DIRECTORY);
      include_once($api_file_path);
    }
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'dms-available') {
    $handler_output_data['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение текущей кодировки
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'charset') {
    $charset = ($system_core->configurator->exists_database_entry_value('base_site_charset')) ? $system_core->configurator->get_database_entry_value('base_site_charset') : 'UTF-8';
    $handler_output_data['charset'] = $charset;
  
  // Получение расширения, в которое производится конвертация
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'file-auto-convert-image-extension') {
    $extension = ($system_core->configurator->exists_database_entry_value('files_auto_convert_file_image_extension')) ? $system_core->configurator->get_database_entry_value('files_auto_convert_file_image_extension') : 'webp';
    $handler_output_data['extension'] = $extension;

  // Получение перечня кодировок
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'charsets') {
    $handler_output_data['charsets'] = ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859'];
  
  // Получение статуса технических работ
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'ew-status') {
    $ew_status = ($system_core->configurator->exists_database_entry_value('base_engineering_works_status')) ? $system_core->configurator->get_database_entry_value('base_engineering_works_status') : 'off';
    $handler_output_data['status'] = ($ew_status == 'on') ? 'on' : 'off';
  
  // Получение текущего временной зоны
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'timezone') {
    $timezone_name = ($system_core->configurator->exists_database_entry_value('base_timezone')) ? $system_core->configurator->get_database_entry_value('base_timezone') : date_default_timezone_get();
    $timezone_utc = new DateTimeImmutable('now', new DateTimeZone($timezone_name));

    $handler_output_data['timezone'] = [
      'name' => $timezone_name,
      'utc' => $timezone_utc->format('P')
    ];
  
  // Получение списка временных зон
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'timezones') {
    $timezones = [];
    $timezone_names_array = DateTimeZone::listIdentifiers();
    foreach ($timezone_names_array as $timezone_name) {
      $timezone_utc = new DateTimeImmutable('now', new DateTimeZone($timezone_name));

      array_push($timezones, [
        'name' => $timezone_name,
        'utc' => $timezone_utc->format('P')
      ]);
    }

    $handler_output_data['timezones'] = $timezones;
  
  // Получение дополнительной информации по профилю пользователя
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'profile' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      if ($system_core->urlp->get_path(2) == 'additional-fields') {
        $cms_locale_setted = $system_core->configurator->get_database_entry_value('base_locale');
        $fields_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $cms_locale_setted;

        $fields_types = ($system_core->configurator->exists_database_entry_value('users_additional_field_type')) ? json_decode($system_core->configurator->get_database_entry_value('users_additional_field_type'), true) : [];
        $fields_titles = ($system_core->configurator->exists_database_entry_value('users_additional_field_title')) ? json_decode($system_core->configurator->get_database_entry_value('users_additional_field_title'), true) : [];
        $fields_descriptions = ($system_core->configurator->exists_database_entry_value('users_additional_field_description')) ? json_decode($system_core->configurator->get_database_entry_value('users_additional_field_description'), true) : [];
        $fields_names = ($system_core->configurator->exists_database_entry_value('users_additional_field_name')) ? json_decode($system_core->configurator->get_database_entry_value('users_additional_field_name'), true) : [];
        
        $fields = [];
        foreach ($fields_types as $field_index => $field_type) {
          array_push($fields, [
            'type' => $field_type,
            'title' => isset($fields_titles[$fields_locale]) ? $fields_titles[$fields_locale][$field_index] : '',
            'description' => isset($fields_descriptions[$fields_locale]) ? $fields_descriptions[$fields_locale][$field_index] : '',
            'name' => $fields_names[$field_index]
          ]);
        }

        $handler_output_data['additionalFields'] = $fields;
      }
    }
  // Получить текущую локализацию
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'locale') {
    // Базовая локализация
    if ($system_core->urlp->get_path(2) == 'base') {
      $cms_locale_setted = (!is_null($system_core->configurator->get_database_entry_value('base_locale'))) ? $system_core->configurator->get_database_entry_value('base_locale') : 'en_US';
      $cms_locale = new \core\PHPLibrary\SystemCore\Locale($system_core, $cms_locale_setted);
      $handler_output_data['locale'] = [
        'title' => $cms_locale->get_title(),
        'iconURL' => $cms_locale->get_icon_url(),
        'name' => $cms_locale->get_name(),
        'iso639_1' => $cms_locale->get_iso_639_1(),
        'iso639_2' => $cms_locale->get_iso_639_2(),
      ];
    }

    // Локализация административной панели
    if ($system_core->urlp->get_path(2) == 'admin') {
      $cms_locale_setted = (!is_null($system_core->configurator->get_database_entry_value('base_admin_locale'))) ? $system_core->configurator->get_database_entry_value('base_admin_locale') : 'en_US';
      $cms_locale = new \core\PHPLibrary\SystemCore\Locale($system_core, $cms_locale_setted);
      $handler_output_data['locale'] = [
        'title' => $cms_locale->get_title(),
        'iconURL' => $cms_locale->get_icon_url(),
        'name' => $cms_locale->get_name(),
        'iso639_1' => $cms_locale->get_iso_639_1(),
        'iso639_2' => $cms_locale->get_iso_639_2(),
      ];
    }
  
  // Получить перечень доступных локализаций
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'locales') {
    $handler_output_data['locales'] = [];
    $cms_locales_names = $system_core->get_array_locales_names();
    if (count($cms_locales_names) > 0) {
      foreach ($cms_locales_names as $index => $cms_locale_name) {
        $cms_locale = new \core\PHPLibrary\SystemCore\Locale($system_core, $cms_locale_name);

        if ($cms_locale->exists_file_metadata_json()) {
          array_push($handler_output_data['locales'], [
            'title' => $cms_locale->get_title(),
            'iconURL' => $cms_locale->get_icon_url(),
            'name' => $cms_locale->get_name(),
            'iso639_1' => $cms_locale->get_iso_639_1(),
            'iso639_2' => $cms_locale->get_iso_639_2(),
          ]);
        }
      }

      $handler_message = 'Данные по локализациям успешно получены.';
      $handler_status_code = 1;
    } else {
      $handler_message = 'Данные по локализациям не были получены, поскольку они не обнаружены в системе.';
      $handler_status_code = 0;
    }
  
  // Получить перечень записей
  } else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $system_core->urlp->get_path(1) == 'entries' && $system_core::core_rest_cookie_exists()) {
    $system_core_rest_cookie = $system_core::get_core_rest_cookie();
    $client_ip = $system_core->client->get_ip_address();

    if ($system_core::core_rest_cookie_is_valid($system_core_rest_cookie, $client_ip)) {
      if ($system_core->urlp->get_path(2) == 'categories' && is_null($system_core->urlp->get_path(3))) {
        $entries_categories_object = new \core\PHPLibrary\EntriesCategories($system_core);
        $entries_categories_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

        $handler_output_data['entriesCategories'] = [];
        foreach ($entries_categories_object->get_all() as $entry_category) {
          $entry_category->init_data(['name', 'parent_id', 'texts']);
          array_push($handler_output_data['entriesCategories'], [
            'id' => $entry_category->get_id(),
            'parent_id' => $entry_category->get_parent_id(),
            'name' => $entry_category->get_name(),
            'title' => $entry_category->get_title($entries_categories_locale),
            'description' => $entry_category->get_description($entries_categories_locale),
          ]);
        }
      }
    }
  // Попытка инициализации персонализированного обработчика
  } else {
    if ($system_core->urlp->get_path(1) != null) {

      /**
       * Рекурсивный поиск файла обработчика в директории API
       */
      $recursion_handler_connect = function(\core\PHPLibrary\SystemCore $system_core, array $pathes, int $index) use (&$recursion_handler_connect) : string|null {
        $handlers_root_directory_path = sprintf('%s/api', CMS_ROOT_DIRECTORY);

        $files_array = array_diff(scandir($handlers_root_directory_path), ['.', '..']);
        foreach ($files_array as $file_index => $file_name) {
          if (array_key_last($pathes) != $index) {
            if ($file_name == $pathes[$index]) {

              $url_pathes = $system_core->urlp->get_pathes();
              return $recursion_handler_connect($system_core, $url_pathes, $index + 1);
            }
          } else {
            $handler_file_name = sprintf('%s.api.php', $pathes[$index]);
            $handler_file_path = sprintf('%s/%s/%s', $handlers_root_directory_path, implode('/', array_slice($pathes, 1, count($pathes) - 2)), $handler_file_name);
            
            if (file_exists($handler_file_path)) {
              return $handler_file_path;
            }

            break;
          }
        }

        return null;
      };

      $url_pathes = $system_core->urlp->get_pathes();
      $handler_connection_result = $recursion_handler_connect($system_core, $url_pathes, 1);
      
      if ($handler_connection_result != null) {
        include_once($handler_connection_result);
      }
    }
  }

  /** @var string $handler_message Сообщение обработчика */
  $handler_message = (isset($handler_message)) ? $handler_message : 'Обработчик CMS GIRVAS не смог обработать запрос.';
  /** @var int $handler_status_code Статус обработчика */
  $handler_status_code = (isset($handler_status_code)) ? $handler_status_code : 0;
  /** @var array $handler_output_data Выходные данные обработчика */
  $handler_output_data = (isset($handler_output_data)) ? $handler_output_data : [];
  $handler_output_data['debug']['method'] = $_SERVER['REQUEST_METHOD'];
  $handler_output_data['debug']['client_ip'] = $_SERVER['REMOTE_ADDR'];
  $handler_output_data['debug']['post_data'] = (isset($_POST)) ? $_POST : null;
  $handler_output_data['debug']['get_data'] = (isset($_GET)) ? $_GET : null;
  $handler_output_data['debug']['patch_data'] = (isset($_PATCH)) ? $_PATCH : null;
  $handler_output_data['debug']['put_data'] = (isset($_PUT)) ? $_PUT : null;
  $handler_output_data['debug']['delete_data'] = (isset($_DELETE)) ? $_DELETE : null;

  // Выводим результат работы обработчика в JSON-формате
  echo json_encode([
    'message' => $handler_message,
    'statusCode' => $handler_status_code,
    'outputData' => $handler_output_data
  // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Client\Session as ClientSession;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

// Проверка авторизации клиента
if ($system_core->urlp->get_path(2) == 'is-logged') {
  $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;

  $handler_output_data['result'] = $system_core->client->is_logged(1);
}

// Получение IP-адреса клиента
if ($system_core->urlp->get_path(2) == 'ip-address') {
  $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;

  $handler_output_data['result'] = $system_core->client->get_ip_address();
}

?>
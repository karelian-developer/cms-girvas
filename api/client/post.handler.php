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

if ($system_core->urlp->get_path(2) == 'session-end') {
  $session_level = $system_core->urlp->get_param('level');
  $session_level = (is_numeric($session_level)) ? (int)$session_level : 0;
  $session = $system_core->client->get_session($session_level, ['user_id']);
  $session_user_id = $session->get_user_id();

  if (!is_null($session) && $session_level != 0) {
    $session->delete();

    if (!ClientSession::exists_by_ip_and_user_id($system_core, $system_core->client->get_ip_address(), $session_user_id, $session_level)) {
      $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_POST_DATA_SUCCESS') : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;

      $handler_output_data['result'] = true;
    } else {
      $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_ERROR_SESSION_NOT_DELETED') : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;

      $handler_output_data['result'] = false;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_ERROR_SESSION_UNKNOWN') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;

    $handler_output_data['result'] = false;
  }
}

?>
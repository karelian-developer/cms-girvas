<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if ($system_core->client->is_logged(2) && $system_core->urlp->get_path(2) == 'secret-codes') {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) {
    $chars = 'qwertyuiopasdfghjklzxcvbnm123456789';

    for ($code_index = 0; $code_index < 4; $code_index++) {
      $code_chars_array = [];
      for ($char_index = 0; $char_index < 4; $char_index++) {
        array_push($code_chars_array, $chars[rand(0, strlen($chars) - 1)]);
      }

      switch ($code_index) {
        case 0: $code_char = 'a'; break;
        case 1: $code_char = 'b'; break;
        case 2: $code_char = 'c'; break;
        case 3: $code_char = 'd'; break;
      }

      $system_core->configurator->update_database_entry_value(
        sprintf('security_admin_code_%s', $code_char),
        password_hash(implode($code_chars_array), PASSWORD_ARGON2ID)
      );

      unset($code_chars_array);
    }

    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_UTILS_SECRET_CODES_GENERATED_SUCCESS') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
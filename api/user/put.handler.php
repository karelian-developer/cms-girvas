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

use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
    $user_creation_allowed = true;

    if ($system_core->configurator->get_users_login_special_symbols_status(true)) {
      $login_regular = '[a-zA-Z0-9\!\@\#\$\%\&]+';
    } else {
      $login_regular = '[a-zA-Z0-9]+';
    }

    if ($system_core->configurator->get_users_password_special_symbols_status(true)) {
      $password_regular = '[a-zA-Z0-9\!\@\#\$\%\&]+';
    } else {
      $password_regular = '[a-zA-Z0-9]+';
    }

    $user_login = isset($_PUT['user_login']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_login'])) : '';
    $user_email = isset($_PUT['user_email']) ? str_replace('\'', '"', $_PUT['user_email']) : '';
    $user_name = isset($_PUT['user_name']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_name'])) : '';
    $user_surname = isset($_PUT['user_surname']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_surname'])) : '';
    $user_patronymic = isset($_PUT['user_patronymic']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_patronymic'])) : '';
    $user_birthdate = isset($_PUT['user_birthdate']) ? $_PUT['user_birthdate'] : 0;
    $user_group_id = isset($_PUT['user_group_id']) ? (int)$_PUT['user_group_id'] : 4;
    $user_password = isset($_PUT['user_password']) ? str_replace('\'', '"', $_PUT['user_password']) : '';
    $user_password_repeat = isset($_PUT['user_password_repeat']) ? str_replace('\'', '"', $_PUT['user_password_repeat']) : '';
    
    if (isset($_PUT['user_login'])) {
      if ($system_core->configurator->get_users_logins_blacklist_status(true)) {
        $logins_blacklist_array = $system_core->configurator->get_users_logins_blacklist(true);

        foreach ($logins_blacklist_array as $login) {
          if ($system_core->configurator->get_users_login_register_accounting_status(true)) {
            if (preg_match(sprintf('/^%s$/', $user_login), $login)) {
              $user_creation_allowed = false;

              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          } else {
            if (preg_match(sprintf('/^%s$/i', $user_login), $login)) {
              $user_creation_allowed = false;

              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          }
        }
      }

      if ($user_creation_allowed) {
        if ($system_core->configurator->get_users_login_register_accounting_status(true)) {
          if (!preg_match(sprintf('/^%s$/i', $login_regular), $user_login)) {
            $user_creation_allowed = false;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          if (!preg_match(sprintf('/^%s$/', $login_regular), $user_login)) {
            $user_creation_allowed = false;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }

      if ($user_creation_allowed) {
        if ($system_core->configurator->get_users_login_length_max() > 0) {
          if (strlen($user_login) > $system_core->configurator->get_users_login_length_max()) {
            $user_creation_allowed = false;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE'), $system_core->configurator->get_users_login_length_max())) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }

      if ($user_creation_allowed) {
        if (strlen($user_login) < $system_core->configurator->get_users_login_length_min()) {
          $user_creation_allowed = false;

          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL'), $system_core->configurator->get_users_login_length_min())) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      }
    }

    if (isset($_PUT['user_password'])) {
      if ($user_creation_allowed) {
        if ($system_core->configurator->get_users_password_length_max() > 0) {
          if (strlen($user_password) > $system_core->configurator->get_users_password_length_max()) {
            $user_creation_allowed = false;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE'), $system_core->configurator->get_users_password_length_max())) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }

      if ($user_creation_allowed) {
        if (strlen($user_password) < $system_core->configurator->get_users_password_length_min()) {
          $user_creation_allowed = false;

          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL'), $system_core->configurator->get_users_password_length_min())) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      }

      if ($user_creation_allowed) {
        if (!preg_match(sprintf('/^%s$/i', $password_regular), $user_password)) {
          $user_creation_allowed = false;

          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      }
    }

    if (User::exists_by_login($system_core, $user_login, $system_core->configurator->get_users_login_register_accounting_status(true))) {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_ALREADY_EXISTS')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      $user_creation_allowed = false;
    }

    if (User::exists_by_email($system_core, $user_email)) {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_EMAIL_ALREADY_EXISTS')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      $user_creation_allowed = false;
    }

    if (isset($user_password) && isset($user_password_repeat)) {
      if (!empty($user_password) || !empty($user_password_repeat)) {
        if ($user_password != $user_password_repeat) {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_REPEAT_PASSWORD')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          $user_creation_allowed = false;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        $user_creation_allowed = false;
      }
    }

    if (isset($user_email)) {
      if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_EMAIL')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        $user_creation_allowed = false;
      }
    }

    if (isset($user_birthdate)) {
      $user_birthdate = (is_numeric($user_birthdate)) ? $user_birthdate : strtotime($user_birthdate);
      
      if ($user_birthdate <= time()) {
        $user_data['metadata']['birthdateUnixTimestamp'] = $user_birthdate;
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_BIRTHDATE_FUTURE')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        $user_is_updated = false;
      }
    }

    if (isset($user_name)) {
      $user_data['metadata']['name'] = $user_name;
    }

    if (isset($user_surname)) {
      $user_data['metadata']['surname'] = $user_surname;
    }

    if (isset($user_patronymic)) {
      $user_data['metadata']['patronymic'] = $user_patronymic;
    }

    if (isset($user_group_id)) {
      if (!isset($user_data)) $user_data = [];
      if (!isset($user_data['metadata'])) $user_data['metadata'] = [];

      $user_data['metadata']['group_id'] = $user_group_id;
    }

    foreach ($_PUT as $key => $value) {
      if (preg_match('/^user\_additional\_field\_([a-z0-9\_]+)$/i', $key, $key_matches, PREG_OFFSET_CAPTURE) && !empty($value)) {
        if (!isset($user_data)) $user_data = [];
        if (!isset($user_data['metadata'])) $user_data['metadata'] = [];
        if (!isset($user_data['metadata']['additionalFields'])) $user_data['metadata']['additionalFields'] = [];
        
        $value_name_parts = explode('_', $key_matches[1][0]);
        foreach ($value_name_parts as $part_index => $part) {
          if ($part_index > 0) {
            $value_name_parts[$part_index] = ucfirst($part);
          }
        }

        if (is_bool($value)) $value = (int)$value;

        $user_data['metadata']['additionalFields'][implode($value_name_parts)] = htmlspecialchars(str_replace('\'', '"', $value));
      }
    }

    if ($user_creation_allowed) {
      $user = User::create($system_core, $user_login, $user_email, $user_password);
      
      if (!is_null($user)) {
        $user->init_data(['*']);
        // Подтверждение E-Mail у пользователя
        $user_data['email_is_submitted'] = true;

        if (isset($user_data)) {
          $user->update($user_data);
        }

        $handler_output_data['user'] = [];
        $handler_output_data['user']['id'] = $user->get_id();

        $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS') : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
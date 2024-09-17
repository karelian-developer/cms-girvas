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

if ($system_core->client->is_logged(1) || $system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(1);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if (isset($_PATCH['user_id'])) {
    if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_MANAGEMENT) || $client_user->get_id() == (int)$_PATCH['user_id']) {
      $user_id = (is_numeric($_PATCH['user_id'])) ? (int)$_PATCH['user_id'] : 0;

      if (User::exists_by_id($system_core, $user_id)) {
        $user = new User($system_core, $user_id);
        $user->init_data(['login', 'email', 'security_hash', 'password_hash']);

        $user_data = [];

        if (isset($_PATCH['user_is_block'])) {
          if (!isset($user_data['metadata'])) $user_data['metadata'] = [];
          $user_data['metadata']['isBlocked'] = (int)$_PATCH['user_is_block'];
        }

        if (isset($_PATCH['user_login'])) $user_login = htmlspecialchars(str_replace('\'', '"', $_PATCH['user_login']));
        if (isset($_PATCH['user_email'])) $user_email = str_replace('\'', '"', $_PATCH['user_email']);
        if (isset($_PATCH['user_name'])) $user_name = htmlspecialchars(str_replace('\'', '"', $_PATCH['user_name']));
        if (isset($_PATCH['user_surname'])) $user_surname = htmlspecialchars(str_replace('\'', '"', $_PATCH['user_surname']));
        if (isset($_PATCH['user_patronymic'])) $user_patronymic = htmlspecialchars(str_replace('\'', '"', $_PATCH['user_patronymic']));
        if (isset($_PATCH['user_birthdate'])) $user_birthdate = strtotime($_PATCH['user_birthdate']);
        if (isset($_PATCH['user_group_id'])) $user_group_id = (int)$_PATCH['user_group_id'];
        if (isset($_PATCH['user_password'])) $user_password = str_replace('\'', '"', $_PATCH['user_password']);
        if (isset($_PATCH['user_password_repeat'])) $user_password_repeat = str_replace('\'', '"', $_PATCH['user_password_repeat']);
        if (isset($_PATCH['user_password_old'])) $user_password_old = str_replace('\'', '"', $_PATCH['user_password_old']);

        if (isset($user_password) && isset($user_password_repeat)) {
          if (!empty($user_password) || !empty($user_password_repeat)) {
            if ($user_password == $user_password_repeat) {
              $user_data['password_hash'] = User::password_hash($system_core, $user->get_security_hash(), $user_password);
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_REPEAT_PASSWORD')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              $user_is_updated = false;
            }

            if (!$client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_MANAGEMENT) || $user_id == $client_user->get_id()) {
              if (isset($user_password_old)) {
                if (!empty($user_password_old)) {
                  if (!$user->password_verify($user_password_old)) {
                    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_OLD_PASSWORD')) : $handler_message;
                    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                    $user_is_updated = false;
                  }
                } else {
                  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_EMPTY_OLD_PASSWORD')) : $handler_message;
                  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                  $user_is_updated = false;
                }
              } else {
                $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                $user_is_updated = false;
              }
            }
          }
        }

        if (isset($user_login) && $client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
          if ($user_login != $user->get_login()) {
            if (User::login_is_valid($system_core, $user_login)) {
              if (!User::exists_by_login($system_core, $user_login)) {
                $user_data['login'] = $user_login;
              } else {
                $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_ALREADY_EXISTS')) : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                $user_is_updated = false;
              }
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              $user_is_updated = false;
            }
          }
        }

        if (isset($user_email)) {
          if ($user_email != $user->get_email()) {
            if (User::email_is_valid($system_core, $user_email)) {
              if (!User::exists_by_email($system_core, $user_email)) {
                $user_data['email'] = $user_email;
              } else {
                $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_EMAIL_ALREADY_EXISTS')) : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                $user_is_updated = false;
              }
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_INVALID_EMAIL')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              $user_is_updated = false;
            }
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

        /**
         * Обновление данных в дополнительных полях
         * Обратите внимание, что наименование поля будет преобразовано - система будет
         * отбрасывать символ "_", а последующий регистр последующего символа будет изменять.
         * Например, если наименование поля "user_home_address",
         * то оно примет следующий вид: userHomeAddress.
         */
        foreach ($_PATCH as $name => $value) {
          if (preg_match('/^user_additional_field_([a-z0-9_]+)$/', $name, $matches, PREG_OFFSET_CAPTURE)) {
            if (!isset($user_data['metadata']['additionalFields'])) $user_data['metadata']['additionalFields'] = [];
            
            $field_name = $matches[1][0];
            $field_name_transformed = '';

            $field_name_parts = explode('_', $field_name);
            for ($i = 0; $i < count($field_name_parts); $i++) {
              $field_name_transformed .= ($i > 0) ? ucfirst($field_name_parts[$i]) : $field_name_parts[$i];
            }

            $user_data['metadata']['additionalFields'][$field_name_transformed] = htmlspecialchars(str_replace('\'', '"', $value));
          }
        }

        if (isset($user_group_id)) {
          if (!isset($user_data)) $user_data = [];
          if (!isset($user_data['metadata'])) $user_data['metadata'] = [];
    
          $user_data['metadata']['group_id'] = $user_group_id;
        }

        $user_is_updated = (!isset($user_is_updated)) ? $user->update($user_data) : $user_is_updated;

        if ($user_is_updated) {
          $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS') : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
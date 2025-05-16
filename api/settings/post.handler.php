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

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) {
    if (!empty($_POST)) {

      /** @var int Количество пользовательских полей для пользователей */
      $users_additional_fields_count = 0;
      /** @var int Количество пользовательских полей для записей */
      $entries_additional_fields_count = 0;
      /** @var int Количество пользовательских полей для статических страниц */
      $static_pages_additional_fields_count = 0;
      /** @var bool Статус обнаружения ошибок */
      $error_is_detected = false;

      if (!$error_is_detected) {
        if (isset($_POST['setting_users_login_length_min']) && isset($_POST['setting_users_login_length_max'])) {
          $setting_users_login_length_min = (is_numeric($_POST['setting_users_login_length_min'])) ? (int)$_POST['setting_users_login_length_min'] : 0;
          $setting_users_login_length_max = (is_numeric($_POST['setting_users_login_length_max'])) ? (int)$_POST['setting_users_login_length_max'] : 0;

          if ($setting_users_login_length_max < 0 && !$error_is_detected) {
            $error_is_detected = true;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_LOGIN_LENGTH_MAX_TOO_SMALL')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }

          if ($setting_users_login_length_max > 0 && !$error_is_detected) {
            if ($setting_users_login_length_max < $setting_users_login_length_min) {
              $error_is_detected = true;

              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_LOGIN_LENGTH_MIN_LARGE_MAX')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          }

          if ($setting_users_login_length_min < 4 && !$error_is_detected) {
            $error_is_detected = true;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_LOGIN_LENGTH_MIN_TOO_SMALL')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }

      if (!$error_is_detected) {
        if (isset($_POST['setting_users_password_length_min']) && isset($_POST['setting_users_password_length_max'])) {
          $setting_users_password_length_min = (is_numeric($_POST['setting_users_password_length_min'])) ? (int)$_POST['setting_users_password_length_min'] : 0;
          $setting_users_password_length_max = (is_numeric($_POST['setting_users_password_length_max'])) ? (int)$_POST['setting_users_password_length_max'] : 0;

          if ($setting_users_password_length_max < 0 && !$error_is_detected) {
            $error_is_detected = true;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_PASSWORD_LENGTH_MAX_TOO_SMALL')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }

          if ($setting_users_password_length_max > 0 && !$error_is_detected) {
            if ($setting_users_password_length_max < $setting_users_password_length_min) {
              $error_is_detected = true;

              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_PASSWORD_LENGTH_MIN_LARGE_MAX')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          }

          if ($setting_users_password_length_min < 6 && !$error_is_detected) {
            $error_is_detected = true;

            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_USERS_PASSWORD_LENGTH_MIN_TOO_SMALL')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }
      
      if (!$error_is_detected) {
        foreach ($_POST as $setting_name => $setting_value) {
          if (preg_match('/^setting_([a-z0-9_]+)$/', $setting_name, $matches, PREG_OFFSET_CAPTURE)) {
            $setting_name = $matches[1][0];

            if ($setting_name == 'seo_robots_txt') {
              $file_robots_txt_path = sprintf('%s/robots.txt', CMS_ROOT_DIRECTORY);

              try {
                $file_robots_txt = @fopen($file_robots_txt_path, 'w+');
                if ($file_robots_txt === false) {
                  $exception_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_ROBOTS_TXT_PERMISSION_DENIED'));
                  throw new Exception($exception_message);
                }

                fwrite($file_robots_txt, $setting_value);
                fclose($file_robots_txt);
                chmod($file_robots_txt_path, 0664);
              } catch (Exception $exception) {
                $exception_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_SETTINGS_ROBOTS_TXT_PERMISSION_DENIED'));
                $handler_message = (!isset($handler_message)) ? $exception_message : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              }

              continue;
            }

            if ($setting_name == 'users_additional_field_title' && isset($_POST['_users_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_titles = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              } else {
                $fields_titles = [];
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              }

              $users_additional_fields_count += 1;
            }

            if ($setting_name == 'users_additional_field_description' && isset($_POST['_users_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_descriptions = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_descriptions;
              } else {
                $fields_descriptions = [];
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $setting_value = $fields_descriptions;
              }

              $users_additional_fields_count += 1;
            }

            if ($setting_name == 'users_additional_field_type' || $setting_name == 'users_additional_field_name') {
              foreach ($setting_value as $key => $value) {
                $setting_value[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if ($setting_name == 'entries_additional_field_title' && isset($_POST['_entries_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_titles = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              } else {
                $fields_titles = [];
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              }

              $entries_additional_fields_count += 1;
            }

            if ($setting_name == 'entries_additional_field_description' && isset($_POST['_entries_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_descriptions = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_descriptions;
              } else {
                $fields_descriptions = [];
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $setting_value = $fields_descriptions;
              }

              $entries_additional_fields_count += 1;
            }

            if ($setting_name == 'entries_additional_field_type' || $setting_name == 'entries_additional_field_name') {
              foreach ($setting_value as $key => $value) {
                $setting_value[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if ($setting_name == 'static_pages_additional_field_title' && isset($_POST['_static_pages_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_titles = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              } else {
                $fields_titles = [];
                foreach ($setting_value as $key => $value) {
                  $fields_titles[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_titles;
              }

              $static_pages_additional_fields_count += 1;
            }

            if ($setting_name == 'static_pages_additional_field_description' && isset($_POST['_static_pages_additional_fields_locale'])) {
              if ($system_core->configurator->exists_database_entry_value($setting_name)) {
                $fields_descriptions = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
                
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $setting_value = $fields_descriptions;
              } else {
                $fields_descriptions = [];
                foreach ($setting_value as $key => $value) {
                  $fields_descriptions[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $setting_value = $fields_descriptions;
              }

              $static_pages_additional_fields_count += 1;
            }

            if ($setting_name == 'static_pages_additional_field_type' || $setting_name == 'static_pages_additional_field_name') {
              foreach ($setting_value as $key => $value) {
                $setting_value[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if (is_array($setting_value)) $setting_value = json_encode($setting_value);

            switch ($setting_name) {
              case 'security_allowed_admin_ip': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
              case 'security_allowed_emails': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
              case 'seo_site_keywords': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
              case 'security_premoderation_words_filter_list': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
              case 'users_logins_blacklist': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
              case 'users_additional_field_title': $setting_value = $setting_value; break;
              case 'users_additional_field_description': $setting_value = $setting_value; break;
              case 'users_additional_field_type': $setting_value = $setting_value; break;
              case 'users_additional_field_name': $setting_value = $setting_value; break;
              case 'entries_additional_field_title': $setting_value = $setting_value; break;
              case 'entries_additional_field_description': $setting_value = $setting_value; break;
              case 'entries_additional_field_type': $setting_value = $setting_value; break;
              case 'entries_additional_field_name': $setting_value = $setting_value; break;
              case 'static_pages_additional_field_title': $setting_value = $setting_value; break;
              case 'static_pages_additional_field_description': $setting_value = $setting_value; break;
              case 'static_pages_additional_field_type': $setting_value = $setting_value; break;
              case 'static_pages_additional_field_name': $setting_value = $setting_value; break;
              default: $setting_value = htmlspecialchars(str_replace('\'', '"', $setting_value));
            }

            if ($system_core->configurator->exists_database_entry_value($setting_name)) {
              $system_core->configurator->update_database_entry_value($setting_name, $setting_value);
            } else {
              $system_core->configurator->insert_database_entry_value($setting_name, $setting_value);
            }
          }
        }

        if ($users_additional_fields_count == 0 && isset($_POST['_users_additional_fields_locale'])) {
          foreach (['users_additional_field_title', 'users_additional_field_description', 'users_additional_field_name', 'users_additional_field_type'] as $index => $name) {
            if ($system_core->configurator->exists_database_entry_value('users_additional_field_title')) {
              $system_core->configurator->update_database_entry_value($name, json_encode([]));
            }
          }
        }

        if ($entries_additional_fields_count == 0 && isset($_POST['_entries_additional_fields_locale'])) {
          foreach (['entries_additional_field_title', 'entries_additional_field_description', 'entries_additional_field_name', 'entries_additional_field_type'] as $index => $name) {
            if ($system_core->configurator->exists_database_entry_value('entries_additional_field_title')) {
              $system_core->configurator->update_database_entry_value($name, json_encode([]));
            }
          }
        }

        if ($static_pages_additional_fields_count == 0 && isset($_POST['_static_pages_additional_fields_locale'])) {
          foreach (['static_pages_additional_field_title', 'static_pages_additional_field_description', 'static_pages_additional_field_name', 'static_pages_additional_field_type'] as $index => $name) {
            if ($system_core->configurator->exists_database_entry_value('static_pages_additional_field_title')) {
              $system_core->configurator->update_database_entry_value($name, json_encode([]));
            }
          }
        }

        $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS') : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  http_response_code(401);
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
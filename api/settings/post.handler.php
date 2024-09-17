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

      $users_additional_fields_count = 0;

      foreach ($_POST as $setting_name => $setting_value) {
        if (preg_match('/^setting_([a-z0-9_]+)$/', $setting_name, $matches, PREG_OFFSET_CAPTURE)) {
          $setting_name = $matches[1][0];

          if ($setting_name == 'users_additional_field_title' && isset($_POST['_users_additional_fields_locale'])) {
            if ($system_core->configurator->exists_database_entry_value($setting_name)) {
              $fields_titles = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
              $fields_titles[$_POST['_users_additional_fields_locale']] = $setting_value;
              $setting_value = $fields_titles;
            } else {
              $setting_value = [$_POST['_users_additional_fields_locale'] => $setting_value];
            }

            $users_additional_fields_count += 1;
          }

          if ($setting_name == 'users_additional_field_description' && isset($_POST['_users_additional_fields_locale'])) {
            if ($system_core->configurator->exists_database_entry_value($setting_name)) {
              $fields_descriptions = json_decode($system_core->configurator->get_database_entry_value($setting_name), true);
              $fields_descriptions[$_POST['_users_additional_fields_locale']] = $setting_value;
              $setting_value = $fields_descriptions;
            } else {
              $setting_value = [$_POST['_users_additional_fields_locale'] => $setting_value];
            }

            $users_additional_fields_count += 1;
          }

          if (is_array($setting_value)) $setting_value = json_encode($setting_value);

          switch ($setting_name) {
            case 'security_allowed_admin_ip': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
            case 'security_allowed_emails': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
            case 'seo_site_keywords': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
            case 'security_premoderation_words_filter_list': $setting_value = (!empty($setting_value)) ? json_encode(preg_split('/\s*\,\s*/', $setting_value)) : json_encode([]); break;
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

      $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS') : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
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
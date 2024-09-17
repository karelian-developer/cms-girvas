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
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) {
    if (isset($_PATCH['user_group_id'])) {
      $users_group_id = (is_numeric($_PATCH['user_group_id'])) ? (int)$_PATCH['user_group_id'] : 0;

      if (UserGroup::exists_by_id($system_core, $users_group_id)) {
        $users_group = new UserGroup($system_core, $users_group_id);
        $users_group_data = [];

        $users_group_permissions = 0x0000000000000000;
        $users_group_permissions_array = isset($_PATCH['user_group_permissions']) ? $_PATCH['user_group_permissions'] : [];
        
        if (!empty($users_group_permissions_array)) {
          foreach ($users_group_permissions_array as $users_group_permission) {
            switch ($users_group_permission) {
              case 'admin_panel_auth': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_PANEL_AUTH; break;
              case 'admin_users_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT; break;
              case 'admin_users_groups_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT; break;
              case 'admin_modules_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT; break;
              case 'admin_templates_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT; break;
              case 'admin_settings_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT; break;
              case 'admin_feeds_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT; break;
              case 'admin_viewing_logs': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_ADMIN_VIEWING_LOGS; break;
              case 'moder_users_ban': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_MODER_USERS_BAN; break;
              case 'moder_entries_comments_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT; break;
              case 'moder_users_warns': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_MODER_USERS_WARNS; break;
              case 'editor_media_files_management': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT; break;
              case 'editor_entries_edit': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT; break;
              case 'editor_entries_categories_edit': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT; break;
              case 'editor_pages_static_edit': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT; break;
              case 'base_entry_comment_create': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE; break;
              case 'base_entry_comment_change': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE; break;
              case 'base_entry_comment_rate': $users_group_permissions = $users_group_permissions | \core\PHPLibrary\UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE; break;
            }
          }
        }

        $users_group_data['permissions'] = $users_group_permissions;

        $cms_locales_names = $system_core->get_array_locales_names();
        if (count($cms_locales_names) > 0) {
          foreach ($cms_locales_names as $index => $cms_locale_name) {
            $cms_locale = new Locale($system_core, $cms_locale_name);

            $users_groups_title_input_name = sprintf('user_group_title_%s', $cms_locale->get_iso_639_2());

            if (!array_key_exists('metadata', $users_group_data)) $users_group_data['metadata'] = [];

            if (array_key_exists($users_groups_title_input_name, $_PATCH)) {
              if (!array_key_exists('texts', $users_group_data)) $users_group_data['texts'] = [];
              if (!array_key_exists($cms_locale->get_name(), $users_group_data['texts'])) $users_group_data['texts'][$cms_locale->get_name()] = [];

              if (array_key_exists($users_groups_title_input_name, $_PATCH)) $users_group_data['texts'][$cms_locale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$users_groups_title_input_name]));
            }
          }
        }

        if (isset($_PATCH['user_group_name'])) $users_group_data['name'] = urlencode(htmlentities($_PATCH['user_group_name']));

        $users_group_is_updated = $users_group->update($users_group_data);

        if ($users_group_is_updated) {
          $handler_message = $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
          $handler_status_code = 1;
        } else {
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
          $handler_status_code = 0;
        }
      } else {
        $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND'));
        $handler_status_code = 0;
      }
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
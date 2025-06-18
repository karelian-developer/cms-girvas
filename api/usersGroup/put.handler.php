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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) {
    if (is_null($CMSCore->urlp->get_path(2))) {
      $userGroupName = isset($_PUT['user_group_name']) ? urlencode(htmlentities($_PUT['user_group_name'])) : '';

      if (!empty($userGroupName)) {
        if (!UserGroup::exists_by_name($CMSCore, $userGroupName)) {
          if (preg_match('/[a-z\_]+/i', $userGroupName)) {
            $userGroupPermissions = 0x0000000000000000;
            $userGroupPermissionsArray = $_PUT['user_group_permissions'] ?? [];

            if (!empty($userGroupPermissionsArray)) {
              foreach ($userGroupPermissionsArray as $permission) {
                $usersGroupPermissions = match ($permission) {
                  'admin_panel_auth' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_PANEL_AUTH,
                  'admin_users_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT,
                  'admin_users_groups_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT,
                  'admin_modules_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT,
                  'admin_templates_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT,
                  'admin_settings_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT,
                  'admin_feeds_management' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT,
                  'admin_viewing_logs' => $usersGroupPermissions | UserGroup::PERMISSION_ADMIN_VIEWING_LOGS,
                  'moder_users_ban' => $usersGroupPermissions | UserGroup::PERMISSION_MODER_USERS_BAN,
                  'moder_entries_comments_management' => $usersGroupPermissions | UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT,
                  'moder_users_warns' => $usersGroupPermissions | UserGroup::PERMISSION_MODER_USERS_WARNS,
                  'editor_media_files_management' => $usersGroupPermissions | UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT,
                  'editor_entries_edit' => $usersGroupPermissions | UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT,
                  'editor_entries_categories_edit' => $usersGroupPermissions | UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT,
                  'editor_pages_static_edit' => $usersGroupPermissions | UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT,
                  'base_entry_comment_create' => $usersGroupPermissions | UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE,
                  'base_entry_comment_change' => $usersGroupPermissions | UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE,
                  'base_entry_comment_rate' => $usersGroupPermissions | UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE,
                };
              }
            }

            $texts = [];

            $CMSLocalesNames = $CMSCore->get_array_locales_names();
            if (count($CMSLocalesNames) > 0) {
              foreach ($CMSLocalesNames as $index => $name) {
                $CMSLocale = new Locale($CMSCore, $name);
        
                $usersGroupTitleInputName = 'user_group_title_' . $CMSLocale->get_iso_639_2();
        
                if (array_key_exists($usersGroupTitleInputName, $_PUT)) {
                  if (!array_key_exists($CMSLocale->get_name(), $texts)) $texts[$CMSLocale->get_name()] = [];
        
                  if (array_key_exists($usersGroupTitleInputName, $_PUT)) $texts[$CMSLocale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$usersGroupTitleInputName]));
                }
              }
            }

            $userGroup = UserGroup::create($CMSCore, $userGroupName, $texts, $userGroupPermissions);
            if (!is_null($userGroup)) {
              $handlerOutputData['usersGroup'] = [];
              $handlerOutputData['usersGroup']['id'] = $userGroup->get_id();

              $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_INVALID_NAME');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NAME_ALREADY_EXISTS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
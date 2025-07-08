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
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['name', 'permissions']);
  
  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) {
    if (isset($_PATCH['user_group_id'])) {
      $usersGroupID = is_numeric($_PATCH['user_group_id']) ? (int) $_PATCH['user_group_id'] : 0;
      $userGroupName = isset($_PATCH['user_group_name']) ? urlencode(htmlentities($_PATCH['user_group_name'])) : '';
      
      if (UserGroup::existsByID($CMSCore, $usersGroupID)) {
        $usersGroup = new UserGroup($CMSCore, $usersGroupID);
        $usersGroup->initData(['name']);
        $usersGroupnameCurrent = $usersGroup->getName();

        if (!UserGroup::existsByName($CMSCore, $userGroupName) || $userGroupName === $usersGroupnameCurrent) {
          $usersGroupData = [];

          $usersGroupPermissions = 0x0000000000000000;
          $usersGroupPermissionsArray = $_PATCH['user_group_permissions'] ?? [];
          
          if (!empty($usersGroupPermissionsArray)) {
            foreach ($usersGroupPermissionsArray as $permission) {
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

          $usersGroupData['permissions'] = $usersGroupPermissions;

          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $name) {
              $CMSLocale = new CMSLocale($CMSCore, $name);
              $CMSLocale->setTypeName('handler');
              $CMSLocale->initPathes();
              
              $CMSLocaleName = $CMSLocale->getName();

              $usersGroupTitleInputName = 'user_group_title_' . $CMSLocale->getISO639(2);

              if (!array_key_exists('metadata', $usersGroupData)) $usersGroupData['metadata'] = [];

              if (array_key_exists($usersGroupTitleInputName, $_PATCH)) {
                if (!array_key_exists('texts', $usersGroupData)) $usersGroupData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $usersGroupData['texts'])) $usersGroupData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($usersGroupTitleInputName, $_PATCH)) $usersGroupData['texts'][$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$usersGroupTitleInputName]));
              }
            }
          }

          if (isset($_PATCH['user_group_name'])) $usersGroupData['name'] = urlencode(htmlentities($_PATCH['user_group_name']));

          $usersGroupIsUpdated = $usersGroup->update($usersGroupData);

          if ($usersGroupIsUpdated) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_USERS_GROUP_ERROR_NAME_ALREADY_EXISTS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USERS_GROUP_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
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

use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\UsersGroups as UsersGroups;

if ($CMSCore->urlp->get_path(3) === 'permissions') {
  $user = null;
  if ($CMSCore->urlp->get_path(2) === '@me') {
    if ($CMSCore->client->is_logged(1)) {
      $user = $CMSCore->client->get_user(1);
    }
  } else {
    $user = is_numeric($CMSCore->urlp->get_path(2)) ? new User($CMSCore, $CMSCore->urlp->get_path(2)) : User::get_by_login($CMSCore, $CMSCore->urlp->get_path(2));
  }

  if ($CMSCore->client->is_logged(1)) {
    if (!is_null($user)) {
      $user->init_data(['metadata']);
      $userGroup = $user->get_group();
      
      if (!is_null($userGroup)) {
        $userGroup->init_data(['permissions']);

        $handlerOutputData['user'] = [];
        $handlerOutputData['user']['permissions'] = [];
        $handlerOutputData['user']['permissions']['admin_panel_auth'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_PANEL_AUTH);
        $handlerOutputData['user']['permissions']['admin_users_management'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_users_groups_management'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_modules_management'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_templates_management'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_settings_management'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_viewing_logs'] = $userGroup->permission_check(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS);
        $handlerOutputData['user']['permissions']['moder_users_ban'] = $userGroup->permission_check(UserGroup::PERMISSION_MODER_USERS_BAN);
        $handlerOutputData['user']['permissions']['moder_entries_comments_management'] = $userGroup->permission_check(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['moder_users_warns'] = $userGroup->permission_check(UserGroup::PERMISSION_MODER_USERS_WARNS);
        $handlerOutputData['user']['permissions']['editor_media_files_management'] = $userGroup->permission_check(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['editor_entries_edit'] = $userGroup->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT);
        $handlerOutputData['user']['permissions']['editor_entries_categories_edit'] = $userGroup->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
        $handlerOutputData['user']['permissions']['editor_pages_static_edit'] = $userGroup->permission_check(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
        $handlerOutputData['user']['permissions']['base_entry_comment_create'] = $userGroup->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
        $handlerOutputData['user']['permissions']['base_entry_comment_change'] = $userGroup->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
        $handlerOutputData['user']['permissions']['base_entry_comment_rate'] = $userGroup->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE);

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else if (is_null($CMSCore->urlp->get_path(3))) {
  $user = ($CMSCore->urlp->get_path(2) === '@me') ? $CMSCore->client->get_user(1) : (is_numeric($CMSCore->urlp->get_path(2)) ? new User($CMSCore, $CMSCore->urlp->get_path(2)) : User::get_by_login($CMSCore, $CMSCore->urlp->get_path(2)));
  $locale = ($CMSCore->urlp->get_param('locale') !== null) ? $CMSCore->urlp->get_param('locale') : $CMSCore->configurator->get_database_entry_value('base_locale');
  
  if (!is_null($user)) {
    $user->init_data(['login', 'metadata']);

    $userGroup = $user->get_group();
    $userGroup->init_data(['texts']);
    
    $themeName = ($CMSCore->configurator->exists_database_entry_value('base_template')) ? $CMSCore->configurator->get_database_entry_value('base_template') : 'default';
    $theme = new Theme($CMSCore, $themeName);
    $CMSCore->set_template($theme);

    $handlerOutputData['user'] = [];
    $handlerOutputData['user']['id'] = $user->get_id();
    $handlerOutputData['user']['login'] = $user->get_login();
    $handlerOutputData['user']['avatarURL'] = $user->get_avatar_url(64);
    $handlerOutputData['user']['isBlocked'] = $user->is_blocked();
    $handlerOutputData['user']['groupID'] = $user->get_group_id();
    $handlerOutputData['user']['group'] = [
      'id' => $userGroup->get_id(),
      'title' => $userGroup->get_title($locale)
    ];

    if ($CMSCore->urlp->get_path(2) == '@me') {
      $handlerOutputData['user']['isLogged'] = ($CMSCore->client->is_logged(1)) ? true : false;
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

?>
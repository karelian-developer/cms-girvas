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
use \core\PHPLibrary\UsersGroups as UsersGroups;

if ($system_core->urlp->get_path(3) == 'permissions') {
  $user = null;
  if ($system_core->urlp->get_path(2) == '@me') {
    if ($system_core->client->is_logged(1)) {
      $user = $system_core->client->get_user(1);
    }
  } else {
    $user = is_numeric($system_core->urlp->get_path(2)) ? new User($system_core, $system_core->urlp->get_path(2)) : User::get_by_login($system_core, $system_core->urlp->get_path(2));
  }

  if ($system_core->client->is_logged(1)) {
    if (!is_null($user)) {
      $user->init_data(['metadata']);
      $user_group = $user->get_group();
      
      if (!is_null($user_group)) {
        $user_group->init_data(['permissions']);

        $handler_output_data['user'] = [];
        $handler_output_data['user']['permissions'] = [];
        $handler_output_data['user']['permissions']['admin_panel_auth'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_PANEL_AUTH);
        $handler_output_data['user']['permissions']['admin_users_management'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT);
        $handler_output_data['user']['permissions']['admin_users_groups_management'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
        $handler_output_data['user']['permissions']['admin_modules_management'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT);
        $handler_output_data['user']['permissions']['admin_templates_management'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
        $handler_output_data['user']['permissions']['admin_settings_management'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
        $handler_output_data['user']['permissions']['admin_viewing_logs'] = $user_group->permission_check(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS);
        $handler_output_data['user']['permissions']['moder_users_ban'] = $user_group->permission_check(UserGroup::PERMISSION_MODER_USERS_BAN);
        $handler_output_data['user']['permissions']['moder_entries_comments_management'] = $user_group->permission_check(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
        $handler_output_data['user']['permissions']['moder_users_warns'] = $user_group->permission_check(UserGroup::PERMISSION_MODER_USERS_WARNS);
        $handler_output_data['user']['permissions']['editor_media_files_management'] = $user_group->permission_check(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
        $handler_output_data['user']['permissions']['editor_entries_edit'] = $user_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT);
        $handler_output_data['user']['permissions']['editor_entries_categories_edit'] = $user_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
        $handler_output_data['user']['permissions']['editor_pages_static_edit'] = $user_group->permission_check(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
        $handler_output_data['user']['permissions']['base_entry_comment_create'] = $user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
        $handler_output_data['user']['permissions']['base_entry_comment_change'] = $user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
        $handler_output_data['user']['permissions']['base_entry_comment_rate'] = $user_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE);

        $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
        $handler_status_code = 1;
      } else {
        $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND'));
        $handler_status_code = 0;
      }
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND'));
      $handler_status_code = 0;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
    $handler_status_code = 0;
  }
} else if (is_null($system_core->urlp->get_path(3))) {
  $user = ($system_core->urlp->get_path(2) == '@me') ? $system_core->client->get_user(1) : (is_numeric($system_core->urlp->get_path(2)) ? new User($system_core, $system_core->urlp->get_path(2)) : User::get_by_login($system_core, $system_core->urlp->get_path(2)));
  $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');
  
  if (!is_null($user)) {
    $user->init_data(['login', 'metadata']);

    $user_group = $user->get_group();
    $user_group->init_data(['texts']);
    
    $template_name = ($system_core->configurator->exists_database_entry_value('base_template')) ? $system_core->configurator->get_database_entry_value('base_template') : 'default';
    $template = new \core\PHPLibrary\Template($system_core, $template_name);
    $system_core->set_template($template);

    $handler_output_data['user'] = [];
    $handler_output_data['user']['id'] = $user->get_id();
    $handler_output_data['user']['login'] = $user->get_login();
    $handler_output_data['user']['avatarURL'] = $user->get_avatar_url(64);
    $handler_output_data['user']['isBlocked'] = $user->is_blocked();
    $handler_output_data['user']['groupID'] = $user->get_group_id();
    $handler_output_data['user']['group'] = [
      'id' => $user_group->get_id(),
      'title' => $user_group->get_title($locale)
    ];

    if ($system_core->urlp->get_path(2) == '@me') {
      $handler_output_data['user']['isLogged'] = ($system_core->client->is_logged(1)) ? true : false;
    }

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
}

?>
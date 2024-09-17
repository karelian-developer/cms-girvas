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

if ($system_core->urlp->get_path(3) == 'permissions') {
  $users_group = (is_numeric($system_core->urlp->get_path(2)) ? new UserGroup($system_core, $system_core->urlp->get_path(2)) : UserGroup::get_by_name($system_core, $system_core->urlp->get_path(2)));

  if (!is_null($users_group)) {
    $users_group->init_data(['metadata', 'permissions']);

    $handler_output_data['usersGroup'] = [];
    $handler_output_data['usersGroup']['permissions'] = [];
    $handler_output_data['usersGroup']['permissions']['admin_panel_auth'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_PANEL_AUTH);
    $handler_output_data['usersGroup']['permissions']['admin_users_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_users_groups_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_modules_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_templates_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_feeds_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_settings_management'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['admin_viewing_logs'] = $users_group->permission_check(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS);
    $handler_output_data['usersGroup']['permissions']['moder_users_ban'] = $users_group->permission_check(UserGroup::PERMISSION_MODER_USERS_BAN);
    $handler_output_data['usersGroup']['permissions']['moder_entries_comments_management'] = $users_group->permission_check(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['moder_users_warns'] = $users_group->permission_check(UserGroup::PERMISSION_MODER_USERS_WARNS);
    $handler_output_data['usersGroup']['permissions']['editor_media_files_management'] = $users_group->permission_check(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
    $handler_output_data['usersGroup']['permissions']['editor_entries_edit'] = $users_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT);
    $handler_output_data['usersGroup']['permissions']['editor_entries_categories_edit'] = $users_group->permission_check(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
    $handler_output_data['usersGroup']['permissions']['editor_pages_static_edit'] = $users_group->permission_check(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
    $handler_output_data['usersGroup']['permissions']['base_entry_comment_create'] = $users_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
    $handler_output_data['usersGroup']['permissions']['base_entry_comment_change'] = $users_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
    $handler_output_data['usersGroup']['permissions']['base_entry_comment_rate'] = $users_group->permission_check(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE);

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
} else if (is_null($system_core->urlp->get_path(3))) {
  $users_group = (is_numeric($system_core->urlp->get_path(2)) ? new UserGroup($system_core, $system_core->urlp->get_path(2)) : UserGroup::get_by_name($system_core, $system_core->urlp->get_path(2)));

  if (!is_null($users_group)) {
    $users_group->init_data(['name', 'texts', 'metadata']);

    $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');
    
    $handler_output_data['usersGroup'] = [];
    $handler_output_data['usersGroup']['id'] = $users_group->get_id();
    $handler_output_data['usersGroup']['name'] = $users_group->get_name();
    $handler_output_data['usersGroup']['title'] = $users_group->get_title($locale);

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
}

?>
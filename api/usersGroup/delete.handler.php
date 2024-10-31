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

use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Users as Users;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) {
    if (isset($_DELETE['user_group_id'])) {
      $user_group_id = (is_numeric($_DELETE['user_group_id'])) ? (int)$_DELETE['user_group_id'] : 0;

      if (UserGroup::exists_by_id($system_core, $user_group_id)) {
        $user_group = new UserGroup($system_core, $user_group_id);
        $users = new Users($system_core);

        if ($users->get_count_by_group_id($user_group_id) > 0) {
          if ($user_group_id > 4) {
            $user_group_is_deleted = $user_group->delete();
            if ($user_group_is_deleted) {
              $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_DELETE_DATA_SUCCESS') : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_DELETION_EXISTS_USERS')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_DELETION_PROHIBITED')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
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
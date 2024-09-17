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

$users_groups = (new UsersGroups($system_core))->get_all();
$users_groups_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

$handler_output_data['usersGroups'] = [];
if (count($users_groups) > 0) {
  foreach ($users_groups as $users_group) {
    $users_group->init_data(['id', 'texts', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);

    array_push($handler_output_data['usersGroups'], [
      'id' => $users_group->get_id(),
      'name' => $users_group->get_name(),
      'title' => $users_group->get_title($users_groups_locale),
      'createdUnixTimestamp' => $users_group->get_created_unix_timestamp(),
      'updatedUnixTimestamp' => $users_group->get_updated_unix_timestamp()
    ]);
  }

  $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USERS_GROUPS_ERROR_NOT_FOUND')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
}

?>
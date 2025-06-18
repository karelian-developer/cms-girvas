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

$usersGroups = (new UsersGroups($CMSCore))->get_all();
$usersGroupsLocale = $CMSCore->urlp->get_param('locale') !== null ? $CMSCore->urlp->get_param('locale') : $CMSCore->configurator->get_database_entry_value('base_locale');

$handlerOutputData['usersGroups'] = [];
if (count($usersGroups) > 0) {
  foreach ($usersGroups as $usersGroup) {
    $usersGroup->init_data(['id', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

    array_push($handlerOutputData['usersGroups'], [
      'id' => $usersGroup->get_id(),
      'name' => $usersGroup->get_name(),
      'title' => $usersGroup->get_title($usersGroupsLocale),
      'createdUnixTimestamp' => $usersGroup->get_created_unix_timestamp(),
      'updatedUnixTimestamp' => $usersGroup->get_updated_unix_timestamp()
    ]);
  }

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USERS_GROUPS_ERROR_NOT_FOUND');
  $handlerStatusCode = $handlerStatusCode ?? 1;
}

?>
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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) {
    if (isset($_DELETE['user_group_id'])) {
      $userGroupID = filter_var($_DELETE['user_group_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

      if (UserGroup::exists_by_id($CMSCore, $userGroupID)) {
        $userGroup = new UserGroup($CMSCore, $userGroupID);
        $users = new Users($CMSCore);

        if ($users->get_count_by_group_id($userGroupID) === 0) {
          if ($userGroupID > 4) {
            $userGroupIsDeleted = $userGroup->delete();
            if ($userGroupIsDeleted) {
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_DELETE_DATA_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_DELETION_EXISTS_USERS');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_DELETION_PROHIBITED');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USERS_GROUP_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
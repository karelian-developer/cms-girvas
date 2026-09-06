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
use \core\PHPLibrary\SystemCore\Report as Report;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
    if (isset($_DELETE['user_id'])) {
      $userID = is_numeric($_DELETE['user_id']) ? (int) $_DELETE['user_id'] : 0;

      if ($userID != 1) {
        if (User::existsByID($CMSCore, $userID)) {
          $user = new User($CMSCore, $userID);
          $user->initData(['login', 'metadata']);

          // Сохраняем данные для лога перед удалением
          $userLogin = $user->getLogin();

          Report::create(
            $CMSCore,
            Report::REPORT_TYPE_ID_AP_USER_DELETED,
            [
              'userID' => $userID,
              'deletedByID' => $clientUser->getID(),
              'ip' => $CMSCore->client->getIPAddress()
            ]
          );

          $userIsDeleted = $user->delete();
          if ($userIsDeleted) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_CANNOT_REMOVE_SUPERUSER');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = sprintf('API ERROR: %s', $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handlerStatusCode = 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
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

$usersGroups = (new UsersGroups($CMSCore))->getAll();
$usersGroupsLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

$handlerOutputData['usersGroups'] = [];
if (count($usersGroups) > 0) {
  foreach ($usersGroups as $usersGroup) {
    $usersGroup->initData(['id', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

    array_push($handlerOutputData['usersGroups'], [
      'id' => $usersGroup->getID(),
      'name' => $usersGroup->getName(),
      'title' => $usersGroup->getTitle($usersGroupsLocale),
      'createdUnixTimestamp' => $usersGroup->getCreatedUnixTimestamp(),
      'updatedUnixTimestamp' => $usersGroup->getUpdatedUnixTimestamp()
    ]);
  }

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USERS_GROUPS_ERROR_NOT_FOUND');
  $handlerStatusCode = $handlerStatusCode ?? 1;
}
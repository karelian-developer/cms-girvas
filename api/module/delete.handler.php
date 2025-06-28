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

use \core\PHPLibrary\Module as Module;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT)) {
    if (isset($_DELETE['module_name'])) {
      $moduleName = trim($_DELETE['module_name']);
      $module = new Module($CMSCore, $moduleName);
      $modulePath = $module->getPath();

      if ($module->existsCoreFile()) {
        $CMSCore::recursiveFilesRemove($modulePath);

        http_response_code(200);
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_MODULE_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        if (file_exists($modulePath)) {
          $CMSCore::recursiveFilesRemove($modulePath);

          http_response_code(200);
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_MODULE_DIRECTORY_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          http_response_code(500);
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_MODULE_ERROR_NOT_FOUND');
          $handlerStatusCode = 0;
        }
      }
    } else {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_MODULE_ERROR_NOT_FOUND');
      $handlerStatusCode = 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = 0;
}
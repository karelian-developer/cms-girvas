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
    if (isset($_PATCH['module_name'])) {
      $moduleName = trim($_PATCH['module_name']);
      $module = new Module($CMSCore, $moduleName);

      if (isset($_PATCH['module_event'])) {
        $moduleDirectoryPath = CMS_ROOT_DIRECTORY . '/modules/' . $moduleName;
        $moduleCorePath = $moduleDirectoryPath . '/core.class.php';
        $moduleCoreNamespace = '\\modules\\' . $moduleName . '\\Core';
        $moduleEvent = $_PATCH['module_event'];

        if (file_exists($moduleCorePath)) {
          require_once($moduleCorePath);
          
          $moduleCore = new $moduleCoreNamespace($CMSCore, $module);

          if ($moduleEvent === 'enable') {
            if (!$module->isEnabled()) {
              if (method_exists($moduleCore, 'enable')) {
                $moduleCore->enable();
              } else {
                $module->enable();
              }

              if ($module->isEnabled()) {
                http_response_code(200);
                $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_MODULE_ENABLED');
                $handlerStatusCode = $handlerStatusCode ?? 1;
              } else {
                http_response_code(500);
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              http_response_code(500);
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_MODULE_ALREADY_ENABLED');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if ($moduleEvent === 'disable') {
            if ($module->isEnabled()) {
              if (method_exists($moduleCore, 'disable')) {
                $moduleCore->disable();
              } else {
                $module->disable();
              }

              if (!$module->isEnabled()) {
                http_response_code(200);
                $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_MODULE_DISABLED');
                $handlerStatusCode = $handlerStatusCode ?? 1;
              } else {
                http_response_code(500);
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              http_response_code(500);
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_MODULE_ALREADY_DISABLED');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }
        } else {
          http_response_code(500);
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_MODULE_ERROR_CORE_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
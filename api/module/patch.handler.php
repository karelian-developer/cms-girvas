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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT)) {
    if (isset($_PATCH['module_name'])) {
      $moduleName = trim($_PATCH['module_name']);
      $module = new Module($CMSCore, $moduleName);

      if (isset($_PATCH['module_event'])) {
        $moduleEvent = $_PATCH['module_event'];

        if ($moduleEvent === 'enable') {
          if (!$module->is_enabled()) {
            $module->enable();

            if ($module->is_enabled()) {
              http_response_code(200);
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_MODULE_ENABLED');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              http_response_code(500);
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            http_response_code(500);
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_MODULE_ALREADY_ENABLED');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }

        if ($moduleEvent === 'disable') {
          if ($module->is_enabled()) {
            $module->disable();

            if (!$module->is_enabled()) {
              http_response_code(200);
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_MODULE_DISABLED');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              http_response_code(500);
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            http_response_code(500);
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_MODULE_ALREADY_DISABLED');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
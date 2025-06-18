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
    if (isset($_DELETE['module_name'])) {
      $moduleName = trim($_DELETE['module_name']);
      $module = new Module($CMSCore, $moduleName);

      if ($module->exists_core_file()) {
        $CMSCore::recursive_files_remove($module->get_path());

        http_response_code(200);
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_DELETE_MODULE_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        if (file_exists($module->get_path())) {
          $CMSCore::recursive_files_remove($module->get_path());

          http_response_code(200);
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_DELETE_MODULE_DIRECTORY_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          http_response_code(500);
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ERROR_NOT_FOUND');
          $handlerStatusCode = 0;
        }
      }
    } else {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ERROR_NOT_FOUND');
      $handlerStatusCode = 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = 0;
}


?>
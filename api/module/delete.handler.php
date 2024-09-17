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

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_MODULES_MANAGEMENT)) {
    if (isset($_DELETE['module_name'])) {
      $module_name = $_DELETE['module_name'];
      $module = new Module($system_core, $module_name);

      if ($module->exists_core_file()) {
        $system_core::recursive_files_remove($module->get_path());

        http_response_code(200);
        $handler_message = $system_core->locale->get_single_value_by_key('API_DELETE_MODULE_SUCCESS');
        $handler_status_code = 1;
      } else {
        if (file_exists($module->get_path())) {
          $system_core::recursive_files_remove($module->get_path());

          http_response_code(200);
          $handler_message = $system_core->locale->get_single_value_by_key('API_DELETE_MODULE_DIRECTORY_SUCCESS');
          $handler_status_code = 1;
        } else {
          http_response_code(500);
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_MODULE_ERROR_NOT_FOUND'));
          $handler_status_code = 0;
        }
      }
    } else {
      http_response_code(400);
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_MODULE_ERROR_NOT_FOUND'));
      $handler_status_code = 0;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  http_response_code(401);
  $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
  $handler_status_code = 0;
}


?>
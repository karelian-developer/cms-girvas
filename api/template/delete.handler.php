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

use \core\PHPLibrary\Template as Template;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) {
    $themeName = $_DELETE['template_name'];
    $themeCategory = $_DELETE['template_category'];
    $theme = new Template($CMSCore, $themeName, $themeCategory);

    if ($theme->exists_core_file()) {
      $CMSCore::recursive_files_remove($theme->get_path());

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      if (file_exists($theme->get_path())) {
        if ($themeName === $theme->get_name() && $themeCategory === $theme->get_category_name()) {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_TEMPLATE_ERROR_FORBIDDEN_DELETE_INSTALLED_TEMPLATE');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        } else {
          $CMSCore::recursive_files_remove($theme->get_path());

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_TEMPLATE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
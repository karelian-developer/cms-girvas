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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    $fileFullname = $_DELETE['media_file_fullname'];

    if (isset($_DELETE['media_file_fullname'])) {
      $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
      $filePath =  $fileDirectoryPath . '/' . $fileFullname;

      if (file_exists($filePath)) {
        unlink($filePath);

        if (!file_exists($filePath)) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_DELETE_FILE_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}


?>
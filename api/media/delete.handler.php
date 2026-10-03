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

use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    $fileFullname = $_DELETE['file_fullname'] ?? '';

    if (isset($_DELETE['file_fullname']) && !empty($fileFullname)) {
      $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
      $filePath = $fileDirectoryPath . '/' . $fileFullname;

      if (file_exists($filePath)) {
        // ============================================================
        // ЛОГИРОВАНИЕ УДАЛЕНИЯ МЕДИА-ФАЙЛА (152-ФЗ)
        // ============================================================
        // Сохраняем данные о файле перед удалением
        $fileSize = filesize($filePath);
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileMimeType = mime_content_type($filePath);
        
        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED,
          [
            'fileName' => $fileFullname,
            'fileSize' => $fileSize,
            'fileExtension' => $fileExtension,
            'fileMimeType' => $fileMimeType,
            'deletedByID' => $clientUser->getID(),
            'deletedByLogin' => $clientUser->getLogin(),
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );
        
        // Выполняем удаление
        unlink($filePath);

        if (!file_exists($filePath)) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_FILE_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
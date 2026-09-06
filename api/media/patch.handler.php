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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    $fileFullname = trim($_PATCH['file_fullname']);
    $description = trim($_PATCH['file_description']) ?? '';
    $additionalDescription = trim($_PATCH['file_additional_description']) ?? '';
    $fileExtension = trim($_PATCH['file_extension']) ?? '';
    $fileLicense = trim($_PATCH['file_license']) ?? '';
    $fileGEOLocation = trim($_PATCH['file_geo_location']) ?? '';

    if (isset($_PATCH['file_fullname'])) {
      $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
      $filePath =  $fileDirectoryPath . '/' . $fileFullname;

      if (file_exists($filePath)) {
        $jsonFilePath = $fileDirectoryPath . '/metadata.json';
        $imagesData = [];

        if (file_exists($jsonFilePath)) {
          $jsonContent = file_get_contents($jsonFilePath);
          $imagesData = json_decode($jsonContent, true) ?? [];
        }

        $imagesData[$fileFullname] = [
          'filename' => $fileFullname,
          'extension' => $fileExtension,
          'description' => $description,
          'additionalDescription' => $additionalDescription,
          'license' => $fileLicense,
          'GEOLocation' => $fileGEOLocation,
          'updatedAt' => date('Y-m-d H:i:s')
        ];

        $allFiles = scandir($fileDirectoryPath);
        foreach ($allFiles as $file) {
          if ($file === '.' || $file === '..' || $file === 'metadata.json') {
            continue;
          }
          
          $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp', 'bmp'];
          
          if (in_array($extension, $imageExtensions)) {
            if (!isset($imagesData[$file])) {
              $imagesData[$file] = [
                'filename' => $file,
                'extension' => $extension,
                'description' => '',
                'additionalDescription' => '',
                'license' => '',
                'GEOLocation' => '',
                'createdAt' => date('Y-m-d H:i:s', filemtime($fileDirectoryPath . '/' . $file))
              ];
            }
          }
        }

        $jsonResult = json_encode($imagesData, JSON_UNESCAPED_UNICODE);
        file_put_contents($jsonFilePath, $jsonResult);

        if (file_exists($jsonFilePath)) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
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
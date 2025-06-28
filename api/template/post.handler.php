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

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) {
    $themeName = $_POST['template_name'];
    $themeURL = 'https://repository.cms-girvas.ru/templates/' . $themeName;

    $ch = curl_init($themeURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $CURLExucuteResult = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($CURLExucuteResult['outputData'])) {
      $themeDirectoryPath = CMS_ROOT_DIRECTORY . '/templates/' . $themeName;
      $themeArchivePath = CMS_ROOT_DIRECTORY . '/templates/' . $themeName . '.zip';

      $chArchive = curl_init();
      curl_setopt($chArchive, CURLOPT_URL, $CURLExucuteResult['outputData']['download_url']);
      curl_setopt($chArchive, CURLOPT_RETURNTRANSFER, 1);
      $CURLArchiveExucuteResult = curl_exec($chArchive);
      curl_close($chArchive);
      
      $file = fopen($themeArchivePath, "w+");
      fputs($file, $CURLArchiveExucuteResult);
      fclose($file);

      if (file_exists($themeArchivePath)) {
        $zip = new ZipArchive();

        if ($zip->open($themeArchivePath) === true) {
          mkdir($themeDirectoryPath);

          $zip->extractTo($themeDirectoryPath);
          $zip->close();

          unlink($themeArchivePath);

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_TEMPLATE_UPLOADED');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNZIPPING_NOT_POSSIBLE');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_TEMPLATE_ERROR_REPOSITORY_DATA_NOT_GETTED');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
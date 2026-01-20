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
  $handlerOutputData['dom'] = [];

  $filesDirectoryPathParam = $CMSCore->urlp->getParam('directory');
  $filesDirectoryPath = $filesDirectoryPathParam === '0'
    ? CMS_ROOT_DIRECTORY . '/uploads/media'
    : CMS_ROOT_DIRECTORY . $filesDirectoryPathParam;
  
  $files = array_diff(scandir($filesDirectoryPath), ['.', '..']);
  $filesData = [];

  foreach ($files as $file) {
    /** @var string */
    $filePath = $filesDirectoryPath . '/' . $file;
    /** @var string */
    $URL = $file;
    
    $filesData[] = [
      'fileURL' => $URL,
      'isDirectory' => is_dir($filePath),
      'createdUnixTimestamp' => filemtime($filePath)
    ];
  }

  usort($filesData, function($a, $b) {
    if ($a['createdUnixTimestamp'] === $b['createdUnixTimestamp']) {
      return 0;
    }

    return ($a['createdUnixTimestamp'] > $b['createdUnixTimestamp']) ? -1 : 1;
  });

  $filesSorted = [];
  foreach ($filesData as $data) {
    $filesSorted[] = [
      'URL' => $data['fileURL'],
      'isDirectory' => $data['isDirectory'],
      'createdUnixTimestamp' => $data['createdUnixTimestamp']
    ];
  }

  $filesTransformed = [];
  foreach ($filesSorted as $fileData) {
    $filesTransformed[] = [
      'URL' => '/uploads/media/' . $fileData['URL'],
      'isDirectory' => (bool) $fileData['isDirectory'],
      'createdUnixTimestamp' => (int) $fileData['createdUnixTimestamp']
    ];
  }

  $handlerOutputData['items'] = $filesTransformed;

  if (!empty($filesTransformed)) {
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FILES_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
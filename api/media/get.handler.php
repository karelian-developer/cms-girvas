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

  /** @var string */
  $filesDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
  /** @var array */
  $files = array_diff(scandir($filesDirectoryPath), ['.', '..']);
  /** @var array */
  $filesData = [];

  foreach ($files as $file) {
    /** @var string */
    $filePath = $filesDirectoryPath . '/' . $file;
    /** @var string */
    $fileURL = $file;
    
    array_push($filesData, [
      'file_url' => $fileURL,
      'created_unix_timestamp' => filemtime($filePath)
    ]);
  }

  usort($filesData, function($a, $b) {
    if ($a['created_unix_timestamp'] === $b['created_unix_timestamp']) {
      return 0;
    }

    return ($a['created_unix_timestamp'] > $b['created_unix_timestamp']) ? -1 : 1;
  });

  $filesSorted = [];
  foreach ($filesData as $data) {
    array_push($filesSorted, $data['file_url']);
  }

  $filesTransformed = [];
  foreach ($filesSorted as $file) {
    array_push($filesTransformed, '/uploads/media/' . $file);
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
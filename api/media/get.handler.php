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

  if ($CMSURLP->getPath(2) === 'metadata') {

    $filesDirectoryPathParam = $CMSURLP->getParam('directory') !== null
      ? urldecode($CMSURLP->getParam('directory'))
      : null;

    $filesDirectoryPath = $filesDirectoryPathParam === null
      ? '/uploads/media'
      : $filesDirectoryPathParam;

    $filesDirectoryPathWithRoot = CMS_ROOT_DIRECTORY . $filesDirectoryPath;
    $fileMetadataPath = $filesDirectoryPathWithRoot . '/metadata.json';

    if (file_exists($fileMetadataPath)) {
      $filename = $CMSURLP->getParam('fileName');
      $metadata = file_get_contents($fileMetadataPath);
      $metadataJSON = json_decode($metadata, true);
      $handlerOutputData['metadata'] = $filename === null
        ? $metadataJSON
        : $metadataJSON[$filename];
      
      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }

  } else {

    $handlerOutputData['dom'] = [];

    $filesDirectoryPathParam = $CMSURLP->getParam('directory') !== null
      ? urldecode($CMSURLP->getParam('directory'))
      : null;

    $filesExtensionsScopeParam = $CMSURLP->getParam('extensions') !== null
      ? explode(',', urldecode($CMSURLP->getParam('extensions')))
      : [];

    $filesDirectoryPath = $filesDirectoryPathParam === null
      ? '/uploads/media'
      : $filesDirectoryPathParam;

    $filesDirectoryPathWithRoot = CMS_ROOT_DIRECTORY . $filesDirectoryPath;

    $files = array_diff(scandir($filesDirectoryPathWithRoot), ['.', '..']);

    usort($files, function($a, $b) use ($filesDirectoryPathWithRoot) {
      $pathA = $filesDirectoryPathWithRoot . DIRECTORY_SEPARATOR . $a;
      $pathB = $filesDirectoryPathWithRoot . DIRECTORY_SEPARATOR . $b;
      
      $isDirA = is_dir($pathA);
      $isDirB = is_dir($pathB);
      
      if ($isDirA === $isDirB) {
        $timeA = filemtime($pathA);
        $timeB = filemtime($pathB);
        
        if ($timeA === $timeB) {
          return 0;
        }

        return ($timeA > $timeB) ? -1 : 1;
      }
      
      return $isDirA ? 1 : -1;
    });

    $filesData = [];

    foreach ($files as $file) {
      $filePath = $filesDirectoryPathWithRoot . '/' . $file;
      $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
      
      if (in_array($fileExtension, $filesExtensionsScopeParam) || empty($filesExtensionsScopeParam)) {
        $URL = $filesDirectoryPath . '/' . $file;
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        
        $filesData[] = [
          'fileURL' => $URL,
          'filePath' => $filesDirectoryPath,
          'isDirectory' => is_dir($filePath),
          'fileExtension' => $fileExtension,
          'fileName' => $fileName,
          'createdUnixTimestamp' => filemtime($filePath)
        ];
      }
    }

    $filesSorted = [];
    foreach ($filesData as $data) {
      $filesSorted[] = [
        'URL' => $data['fileURL'],
        'isDirectory' => $data['isDirectory'],
        'createdUnixTimestamp' => $data['createdUnixTimestamp'],
        'fullname' => $data['fileName'] . '.' . $data['fileExtension'],
        'extension' => $data['fileExtension']
      ];
    }

    $filesTransformed = [];
    foreach ($filesSorted as $fileData) {
      $filesTransformed[] = [
        'URL' => $fileData['URL'],
        'isDirectory' => (bool) $fileData['isDirectory'],
        'createdUnixTimestamp' => (int) $fileData['createdUnixTimestamp'],
        'fullname' => $fileData['fullname'],
        'extension' => $fileData['extension']
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
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
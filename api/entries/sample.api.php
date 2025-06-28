<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2023, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if ($CMSCore->urlp->getPath(4) === 'categories') {
  $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries/sample/categories.api.php';
  include_once $APIFilePath;
} else if ($CMSCore->urlp->getPath(3) === 'sorttypes') {
  $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries/sample/sorttypes.api.php';
  include_once $APIFilePath;
} else {
  define('API_HANDLERS_ABSOLUTE_PATH', CMS_ROOT_DIRECTORY . '/api/entries/sample');

  if (isset($CMSCore)) {
    // Определение абсолютного пути до обработчика текущего API
    $handlerPath = match ($_SERVER['REQUEST_METHOD']) {
      'POST' => API_HANDLERS_ABSOLUTE_PATH . '/post.handler.php',
      'GET' => API_HANDLERS_ABSOLUTE_PATH . '/get.handler.php',
      'PATCH' => API_HANDLERS_ABSOLUTE_PATH . '/patch.handler.php',
      'DELETE' => API_HANDLERS_ABSOLUTE_PATH . '/delete.handler.php',
      'PUT' => API_HANDLERS_ABSOLUTE_PATH . '/put.handler.php',
    };

    $handlerIsExists = isset($handlerPath) && file_exists($handlerPath);

    // Если абсолютный путь не был инициализирован, то запрещаем дальше работать с API
    if (!$handlerIsExists) {
      http_response_code(500);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_HANDLER_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }

    // Подключаем файл необходимого обработчика
    if ($handlerIsExists) {
      include_once $handlerPath;
    }
  }
}
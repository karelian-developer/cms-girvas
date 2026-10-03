<?php

/**
 * CMS «ГИРВАС»
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @copyright   Copyright (c) 2022 - 2026, ИП Шестаков А.Р.
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

define('API_HANDLERS_ABSOLUTE_PATH', CMS_ROOT_DIRECTORY . '/api/oauth/authorize');

if (isset($CMSCore)) {
  $handlerPath = match ($_SERVER['REQUEST_METHOD']) {
    'GET' => API_HANDLERS_ABSOLUTE_PATH . '/get.handler.php',
    'POST' => API_HANDLERS_ABSOLUTE_PATH . '/post.handler.php',
  };

  $handlerIsExists = isset($handlerPath) && file_exists($handlerPath);

  if (!$handlerIsExists) {
    http_response_code(500);
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_HANDLER_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }

  if ($handlerIsExists) {
    include_once $handlerPath;
  }
}
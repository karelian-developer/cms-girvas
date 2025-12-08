<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

define('API_HANDLERS_ABSOLUTE_PATH', CMS_ROOT_DIRECTORY . '/api/usersGroup');

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
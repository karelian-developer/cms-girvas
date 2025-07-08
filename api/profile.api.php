<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if ($CMSCore->urlp->getPath(2) === 'additional-fields') {
  $locale = $CMSCore->configurator->getDatabaseEntryValue('base_locale');
  $fieldsLocale = $CMSCore->urlp->getParam('locale') ?? $locale;

  $fieldsTypes = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_type') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_type'), true) : [];
  $fieldsTitles = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_title') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_title'), true) : [];
  $fieldsDescriptions = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_description') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_description'), true) : [];
  $fieldsNames = $CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_name') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('users_additional_field_name'), true) : [];
  
  $fields = [];
  foreach ($fieldsTypes as $index => $type) {
    $fields[] = [
      'type' => $type,
      'title' => isset($fieldsTitles[$fieldsLocale]) ? $fieldsTitles[$fieldsLocale][$index] : '',
      'description' => isset($fieldsDescriptions[$fieldsLocale]) ? $fieldsDescriptions[$fieldsLocale][$index] : '',
      'name' => $fieldsNames[$index]
    ];
  }

  $handlerOutputData['additionalFields'] = $fields;
} else {
  define('API_HANDLERS_ABSOLUTE_PATH', CMS_ROOT_DIRECTORY . '/api/profile');

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
      include_once $$handlerPath;
    }
  }
}
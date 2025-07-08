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

<<<<<<< HEAD
if ($system_core->urlp->get_path(2) == 'categories') {
  $api_file_path = sprintf('%s/api/entries/categories.api.php', CMS_ROOT_DIRECTORY);
  include_once($api_file_path);
} else if ($system_core->urlp->get_path(2) == 'sample') {
  $api_file_path = sprintf('%s/api/entries/sample.api.php', CMS_ROOT_DIRECTORY);
  include_once($api_file_path);
} else if ($system_core->urlp->get_path(2) == 'additional-fields') {
  $cms_locale_setted = $system_core->configurator->get_database_entry_value('base_locale');
  $fields_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $cms_locale_setted;

  $fields_types = ($system_core->configurator->exists_database_entry_value('entries_additional_field_type')) ? json_decode($system_core->configurator->get_database_entry_value('entries_additional_field_type'), true) : [];
  $fields_categories_ids = ($system_core->configurator->exists_database_entry_value('entries_additional_field_category_id')) ? json_decode($system_core->configurator->get_database_entry_value('entries_additional_field_category_id'), true) : [];
  $fields_titles = ($system_core->configurator->exists_database_entry_value('entries_additional_field_title')) ? json_decode($system_core->configurator->get_database_entry_value('entries_additional_field_title'), true) : [];
  $fields_descriptions = ($system_core->configurator->exists_database_entry_value('entries_additional_field_description')) ? json_decode($system_core->configurator->get_database_entry_value('entries_additional_field_description'), true) : [];
  $fields_names = ($system_core->configurator->exists_database_entry_value('entries_additional_field_name')) ? json_decode($system_core->configurator->get_database_entry_value('entries_additional_field_name'), true) : [];
  
  $fields = [];
  foreach ($fields_types as $field_index => $field_type) {
    array_push($fields, [
      'type' => $field_type,
      'categoryID' => isset($fields_categories_ids[$field_index]) ? (int)$fields_categories_ids[$field_index] : 1,
      'title' => isset($fields_titles[$fields_locale]) ? $fields_titles[$fields_locale][$field_index] : '',
      'description' => isset($fields_descriptions[$fields_locale]) ? $fields_descriptions[$fields_locale][$field_index] : '',
      'name' => $fields_names[$field_index]
    ]);
  }

  $handler_output_data['additionalFields'] = $fields;
} else {
  define('API_HANDLERS_ABSOLUTE_PATH', sprintf('%s/api/entries', CMS_ROOT_DIRECTORY));
=======
if ($CMSCore->urlp->getPath(2) === 'categories') {
  $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries/categories.api.php';
  include_once $APIFilePath;
} else if ($CMSCore->urlp->getPath(2) === 'sample') {
  $APIFilePath = CMS_ROOT_DIRECTORY . '/api/entries/sample.api.php';
  include_once $APIFilePath;
} else if ($CMSCore->urlp->getPath(2) === 'additional-fields') {
  $locale = $CMSCore->configurator->getDatabaseEntryValue('base_locale');
  $fieldsLocale = $CMSCore->urlp->getParam('locale') ?? $locale;
>>>>>>> develop

  $fieldsTypes = $CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_type') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_type'), true) : [];
  $fieldsCategoriesIDs = $CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_category_id') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_category_id'), true) : [];
  $fieldsTitles = $CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_title') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_title'), true) : [];
  $fieldsDescriptions = $CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_description') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_description'), true) : [];
  $fieldsNames = $CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_name') ? json_decode($CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_name'), true) : [];
  
  $fields = [];
  foreach ($fieldsTypes as $index => $type) {
    $fields[] = [
      'type' => $type,
      'categoryID' => isset($fieldsCategoriesIDs[$index]) ? (int)$fieldsCategoriesIDs[$index] : 1,
      'title' => isset($fieldsTitles[$fieldsLocale]) ? $fieldsTitles[$fieldsLocale][$index] : '',
      'description' => isset($fieldsDescriptions[$fieldsLocale]) ? $fieldsDescriptions[$fieldsLocale][$index] : '',
      'name' => $fieldsNames[$index]
    ];
  }

  $handlerOutputData['additionalFields'] = $fields;
} else {
  define('API_HANDLERS_ABSOLUTE_PATH', CMS_ROOT_DIRECTORY . '/api/entries');

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
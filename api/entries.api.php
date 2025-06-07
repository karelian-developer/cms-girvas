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

  if (isset($system_core)) {
    // Определение абсолютного пути до обработчика текущего API
    switch ($_SERVER['REQUEST_METHOD']) {
      case 'POST': $handler_path = sprintf('%s/post.handler.php', API_HANDLERS_ABSOLUTE_PATH); break;
      case 'GET': $handler_path = sprintf('%s/get.handler.php', API_HANDLERS_ABSOLUTE_PATH); break;
      case 'PATCH': $handler_path = sprintf('%s/patch.handler.php', API_HANDLERS_ABSOLUTE_PATH); break;
      case 'DELETE': $handler_path = sprintf('%s/delete.handler.php', API_HANDLERS_ABSOLUTE_PATH); break;
      case 'PUT': $handler_path = sprintf('%s/put.handler.php', API_HANDLERS_ABSOLUTE_PATH); break;
    }

    // Если абсолютный путь не был инициализирован, то запрещаем дальше работать с API
    if (!isset($handler_path)) {
      http_response_code(500);
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_HANDLER_NOT_FOUND')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }

    // Подключаем файл необходимого обработчика
    if (file_exists($handler_path)) {
      include_once($handler_path);
    }
  }
}

?>
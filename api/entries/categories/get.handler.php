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

use \core\PHPLibrary\EntriesCategories as EntriesCategories;

if ($system_core->client->is_logged(1) || $system_core->client->is_logged(2)) {
  $handler_output_data['entriesCategories'] = [];

  $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

  $entries_categories = new EntriesCategories($system_core);
  $entries_categories_objects_array = $entries_categories->get_all();

  foreach ($entries_categories_objects_array as $entries_category_object) {
    $entries_category_object->init_data(['texts']);

    $handler_output_data['entriesCategories'][] = [
      'id' => $entries_category_object->get_id(),
      'title' => $entries_category_object->get_title($locale)
    ];
  }

  $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handler_status_code = 1;
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
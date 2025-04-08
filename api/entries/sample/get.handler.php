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

use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\EntriesSamples as EntriesSamples;

if ($system_core->client->is_logged(1) || $system_core->client->is_logged(2)) {
  /** @var int ID выборки */
  $sample_id = (is_numeric($system_core->urlp->get_path(3))) ? (int)$system_core->urlp->get_path(3) : 0;

  if (EntriesSample::exists_by_id($system_core, $sample_id)) {
    $sample = new EntriesSample($system_core, $sample_id);
    $sample->init_data(['texts', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);
    
    $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

    $handler_output_data['entriesSample'] = [];
    $handler_output_data['entriesSample']['id'] = $sample->get_id();
    $handler_output_data['entriesSample']['title'] = $sample->get_title($locale);
    $handler_output_data['entriesSample']['description'] = $sample->get_description($locale);
    $handler_output_data['entriesSample']['createdUnixTimestamp'] = $sample->get_created_unix_timestamp();
    $handler_output_data['entriesSample']['updatedUnixTimestamp'] = $sample->get_updated_unix_timestamp();
    $handler_output_data['entriesSample']['limitCount'] = $sample->get_limit_count();
    $handler_output_data['entriesSample']['sortTypeID'] = $sample->get_sort_type_id();

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
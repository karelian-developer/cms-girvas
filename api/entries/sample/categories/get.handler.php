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
    $sample->init_data(['metadata']);
    
    $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

    $handler_output_data['entriesSample'] = ['categories' => []];
    $handler_output_data['entriesSample']['id'] = $sample->get_id();

    $sample_categories_ids = $sample->get_categories_ids();
    $sample_categories = $sample->get_categories();
    if (count($sample_categories) > 0) {
      foreach ($sample_categories as $sample_category) {
        $sample_category->init_data(['texts']);
        $sample_category_is_selected = (array_search($sample_category->get_id(), $sample_categories_ids) !== false) ? true : false;

        $handler_output_data['entriesSample']['categories'][] = [
          'id' => $sample_category->get_id(),
          'title' => $sample_category->get_title($locale),
          'isSelected' => $sample_category_is_selected
        ];
      }
    }

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
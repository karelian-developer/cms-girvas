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

use \core\PHPLibrary\EntriesSample\EnumSortTypeID as EnumSortTypeID;
use \ReflectionEnum as ReflectionEnum;

if ($system_core->client->is_logged(1) || $system_core->client->is_logged(2)) {
  $handler_output_data['types'] = [];

  $locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');
  $data_type = $system_core->urlp->get_param('dataType');

  if ($data_type == 'names') {
    $reflection_enum = new ReflectionEnum(EnumSortTypeID::class);
    foreach ($reflection_enum->getCases() as $case_index => $case) {
      $handler_output_data['types'][] = [
        'id' => $case_index + 1,
        'name' => $case->getName()
      ];
    }
  }

  $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handler_status_code = 1;
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
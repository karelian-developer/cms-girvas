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

use \core\PHPLibrary\Feed as Feed;

if (is_numeric($system_core->urlp->get_path(2))) {
  $web_channel_id = (int)$system_core->urlp->get_path(2);

  if (Feed::exists_by_id($system_core, $web_channel_id)) {
    $web_channel = new Feed($system_core, $web_channel_id);
    $web_channel->init_data(['name', 'type_id', 'entries_category_id', 'texts', 'created_unix_timestamp', 'updated_unix_timestamp']);
    $web_channel_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

    $handler_output_data['feed'] = [];
    $handler_output_data['feed']['id'] = $web_channel->get_id();
    $handler_output_data['feed']['name'] = $web_channel->get_name();
    $handler_output_data['feed']['title'] = $web_channel->get_title($web_channel_locale);
    $handler_output_data['feed']['description'] = $web_channel->get_description($web_channel_locale);
    $handler_output_data['feed']['typeID'] = $web_channel->get_type_id();
    $handler_output_data['feed']['entriesCategoryID'] = $web_channel->get_entries_category_id();
    $handler_output_data['feed']['createdUnixTimestamp'] = $web_channel->get_created_unix_timestamp();
    $handler_output_data['feed']['updatedUnixTimestamp'] = $web_channel->get_updated_unix_timestamp();

    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_FEED_ERROR_NOT_FOUND')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

?>
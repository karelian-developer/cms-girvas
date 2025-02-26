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
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $web_channel_id = (isset($_PATCH['web_channel_id'])) ? $_PATCH['web_channel_id'] : 0;
    $web_channel_id = (is_numeric($web_channel_id)) ? (int)$web_channel_id : 0;

    if (Feed::exists_by_id($system_core, $web_channel_id)) {
      $web_channel = new Feed($system_core, $web_channel_id);
      $web_channel_data = [];

      $cms_locales_names = $system_core->get_array_locales_names();
      if (count($cms_locales_names) > 0) {
        foreach ($cms_locales_names as $index => $cms_locale_name) {
          $cms_locale = new Locale($system_core, $cms_locale_name);

          $feed_title_input_name = sprintf('web_channel_title_%s', $cms_locale->get_iso_639_2());
          $feed_description_textarea_name = sprintf('web_channel_description_%s', $cms_locale->get_iso_639_2());

          if (array_key_exists($feed_title_input_name, $_PATCH) || array_key_exists($feed_description_textarea_name, $_PATCH)) {
            if (!array_key_exists('texts', $web_channel_data)) $web_channel_data['texts'] = [];
            if (!array_key_exists($cms_locale->get_name(), $web_channel_data['texts'])) $web_channel_data['texts'][$cms_locale->get_name()] = [];

            if (array_key_exists($feed_title_input_name, $_PATCH)) $web_channel_data['texts'][$cms_locale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$feed_title_input_name]));
            if (array_key_exists($feed_description_textarea_name, $_PATCH)) $web_channel_data['texts'][$cms_locale->get_name()]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$feed_description_textarea_name]));
          }
        }
      }

      if (isset($_PATCH['web_channel_name'])) $web_channel_data['name'] = urlencode(htmlentities($_PATCH['web_channel_name']));
      if (isset($_PATCH['web_channel_type_id'])) $web_channel_data['type_id'] = $_PATCH['web_channel_type_id'];
      if (isset($_PATCH['web_channel_entries_category_id'])) $web_channel_data['entries_category_id'] = $_PATCH['web_channel_entries_category_id'];

      $web_channel_is_updated = $web_channel->update($web_channel_data);

      if ($web_channel_is_updated) {
        $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS') : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_FEED_ERROR_NOT_FOUND')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
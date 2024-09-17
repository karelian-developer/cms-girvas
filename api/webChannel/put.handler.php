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

use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\WebChannel as WebChannel;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $web_channel_name = (isset($_PUT['web_channel_name'])) ? urlencode(htmlentities($_PUT['web_channel_name'])) : '';
    
    $web_channel_entries_category_id = (isset($_PUT['web_channel_entries_category_id'])) ? $_PUT['web_channel_entries_category_id'] : 0;
    $web_channel_entries_category_id = (is_numeric($_PUT['web_channel_entries_category_id'])) ? (int)$_PUT['web_channel_entries_category_id'] : 0;
    
    $web_channel_type_id = (isset($_PUT['web_channel_type_id'])) ? $_PUT['web_channel_type_id'] : 0;
    $web_channel_type_id = (is_numeric($_PUT['web_channel_type_id'])) ? (int)$_PUT['web_channel_type_id'] : 0;

    $texts = [];

    $cms_locales_names = $system_core->get_array_locales_names();
    if (count($cms_locales_names) > 0) {
      foreach ($cms_locales_names as $index => $cms_locale_name) {
        $cms_locale = new SystemCoreLocale($system_core, $cms_locale_name);

        $title_input_name = sprintf('web_channel_title_%s', $cms_locale->get_iso_639_2());
        $description_textarea_name = sprintf('web_channel_description_%s', $cms_locale->get_iso_639_2());

        if (array_key_exists($title_input_name, $_PUT) || array_key_exists($description_textarea_name, $_PUT)) {
          if (!array_key_exists($cms_locale->get_name(), $texts)) $texts[$cms_locale->get_name()] = [];

          if (array_key_exists($title_input_name, $_PUT)) $texts[$cms_locale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$title_input_name]));
          if (array_key_exists($description_textarea_name, $_PUT)) $texts[$cms_locale->get_name()]['description'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$description_textarea_name]));
        }
      }
    }

    $web_channel = WebChannel::create($system_core, $web_channel_name, $web_channel_entries_category_id, $web_channel_type_id, $texts);
    if (!is_null($web_channel)) {
      $handler_output_data['webChannel'] = [];
      $handler_output_data['webChannel']['id'] = $web_channel->get_id();

      $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS') : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
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
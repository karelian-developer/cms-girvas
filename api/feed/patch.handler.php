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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedID = (isset($_PATCH['web_channel_id'])) ? $_PATCH['web_channel_id'] : 0;
    $feedID = (is_numeric($feedID)) ? (int)$feedID : 0;

    if (Feed::exists_by_id($CMSCore, $feedID)) {
      $feed = new Feed($CMSCore, $feedID);
      $feedData = [];

      $CMSLocalesNames = $CMSCore->get_array_locales_names();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $name) {
          $CMSLocale = new Locale($CMSCore, $name);

          $inputTitleName = 'web_channel_title_' . $CMSLocale->get_iso_639_2();
          $textareaDescriptionName = 'web_channel_description_' . $CMSLocale->get_iso_639_2();

          if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH)) {
            if (!array_key_exists('texts', $feedData)) $feedData['texts'] = [];
            if (!array_key_exists($CMSLocale->get_name(), $feedData['texts'])) $feedData['texts'][$CMSLocale->get_name()] = [];

            if (array_key_exists($inputTitleName, $_PATCH)) $feedData['texts'][$CMSLocale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$inputTitleName]));
            if (array_key_exists($textareaDescriptionName, $_PATCH)) $feedData['texts'][$CMSLocale->get_name()]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$textareaDescriptionName]));
          }
        }
      }

      if (isset($_PATCH['web_channel_name'])) $feedData['name'] = urlencode(htmlentities($_PATCH['web_channel_name']));
      if (isset($_PATCH['web_channel_type_id'])) $feedData['type_id'] = $_PATCH['web_channel_type_id'];
      if (isset($_PATCH['web_channel_entries_category_id'])) $feedData['entries_category_id'] = $_PATCH['web_channel_entries_category_id'];

      $feedIsUpdated = $feed->update($feedData);

      if ($feedIsUpdated) {
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_FEED_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
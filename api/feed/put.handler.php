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
use \core\PHPLibrary\Feed as Feed;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedName = (isset($_PUT['web_channel_name'])) ? urlencode(htmlentities($_PUT['web_channel_name'])) : '';
    
    $feedEntriesCategoryID = $_PUT['web_channel_entries_category_id'] ?? 0;
    $feedEntriesCategoryID = (is_numeric($_PUT['web_channel_entries_category_id'])) ? (int)$_PUT['web_channel_entries_category_id'] : 0;
    
    $feedTypeID = $_PUT['web_channel_type_id'] ?? 0;
    $feedTypeID = (is_numeric($_PUT['web_channel_type_id'])) ? (int)$_PUT['web_channel_type_id'] : 0;

    $texts = [];

    $CMSLocalesNames = $CMSCore->get_array_locales_names();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new SystemCoreLocale($CMSCore, $name);

        $inputTitleName = 'web_channel_title_' . $CMSLocale->get_iso_639_2();
        $textareaDescriptionName = 'web_channel_description_' . $CMSLocale->get_iso_639_2();

        if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT)) {
          if (!array_key_exists($CMSLocale->get_name(), $texts)) $texts[$CMSLocale->get_name()] = [];

          if (array_key_exists($inputTitleName, $_PUT)) $texts[$CMSLocale->get_name()]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$inputTitleName]));
          if (array_key_exists($textareaDescriptionName, $_PUT)) $texts[$CMSLocale->get_name()]['description'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$textareaDescriptionName]));
        }
      }
    }

    $feed = Feed::create($CMSCore, $feedName, $feedEntriesCategoryID, $feedTypeID, $texts);
    if (!is_null($feed)) {
      $handlerOutputData['feed'] = [];
      $handlerOutputData['feed']['id'] = $feed->get_id();

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
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
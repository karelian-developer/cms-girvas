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
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedID = (isset($_PATCH['web_channel_id'])) ? $_PATCH['web_channel_id'] : 0;
    $feedID = (is_numeric($feedID)) ? (int)$feedID : 0;

    if (Feed::existsByID($CMSCore, $feedID)) {
      $feed = new Feed($CMSCore, $feedID);
      $feedData = [];

      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $name) {
          $CMSLocale = new CMSLocale($CMSCore, $name);
          $CMSLocale->setTypeName('handler');
          $CMSLocale->initPathes();

          $CMSLocaleName = $CMSLocale->getName();

          $inputTitleName = 'web_channel_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'web_channel_description_' . $CMSLocale->getISO639(2);

          if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH)) {
            if (!array_key_exists('texts', $feedData)) $feedData['texts'] = [];
            if (!array_key_exists($CMSLocaleName, $feedData['texts'])) $feedData['texts'][$CMSLocaleName] = [];

            if (array_key_exists($inputTitleName, $_PATCH)) $feedData['texts'][$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$inputTitleName]));
            if (array_key_exists($textareaDescriptionName, $_PATCH)) $feedData['texts'][$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$textareaDescriptionName]));
          }
        }
      }

      if (isset($_PATCH['web_channel_name'])) $feedData['name'] = urlencode(htmlentities($_PATCH['web_channel_name']));
      if (isset($_PATCH['web_channel_type_id'])) $feedData['type_id'] = $_PATCH['web_channel_type_id'];
      if (isset($_PATCH['web_channel_entries_category_id'])) $feedData['entries_category_id'] = $_PATCH['web_channel_entries_category_id'];

      $feedIsUpdated = $feed->update($feedData);

      if ($feedIsUpdated) {
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FEED_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
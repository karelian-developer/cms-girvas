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

use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Feed as Feed;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedName = (isset($_PUT['feed_name'])) ? urlencode(htmlentities($_PUT['feed_name'])) : '';
    
    $feedEntriesCategoryID = $_PUT['feed_entries_category_id'] ?? 0;
    $feedEntriesCategoryID = (is_numeric($_PUT['feed_entries_category_id'])) ? (int)$_PUT['feed_entries_category_id'] : 0;
    
    $feedTypeID = $_PUT['feed_type_id'] ?? 0;
    $feedTypeID = (is_numeric($_PUT['feed_type_id'])) ? (int)$_PUT['feed_type_id'] : 0;

    $texts = [];

    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new  CMSLocale($CMSCore, $name);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();

        $CMSLocaleName = $CMSLocale->getName();

        $inputTitleName = 'feed_title_' . $CMSLocale->getISO639(2);
        $textareaDescriptionName = 'feed_description_' . $CMSLocale->getISO639(2);

        if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT)) {
          if (!array_key_exists($CMSLocaleName, $texts)) $texts[$CMSLocaleName] = [];

          if (array_key_exists($inputTitleName, $_PUT)) $texts[$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$inputTitleName]));
          if (array_key_exists($textareaDescriptionName, $_PUT)) $texts[$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$textareaDescriptionName]));
        }
      }
    }

    $feed = Feed::create($CMSCore, $feedName, $feedEntriesCategoryID, $feedTypeID, $texts);
    if (!is_null($feed)) {
      $handlerOutputData['feed'] = [];
      $handlerOutputData['feed']['id'] = $feed->getID();

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
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
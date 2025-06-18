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

if (is_numeric($CMSCore->urlp->get_path(2))) {
  $feedID = (int)$CMSCore->urlp->get_path(2);

  if (Feed::exists_by_id($CMSCore, $feedID)) {
    $feed = new Feed($CMSCore, $feedID);
    $feed->init_data(['name', 'typeID', 'entriesCategoryID', 'texts', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $feedLocale = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

    $handlerOutputData['feed'] = [];
    $handlerOutputData['feed']['id'] = $feed->get_id();
    $handlerOutputData['feed']['name'] = $feed->get_name();
    $handlerOutputData['feed']['title'] = $feed->get_title($feedLocale);
    $handlerOutputData['feed']['description'] = $feed->get_description($feedLocale);
    $handlerOutputData['feed']['typeID'] = $feed->get_type_id();
    $handlerOutputData['feed']['entriesCategoryID'] = $feed->get_entries_category_id();
    $handlerOutputData['feed']['createdUnixTimestamp'] = $feed->get_created_unix_timestamp();
    $handlerOutputData['feed']['updatedUnixTimestamp'] = $feed->get_updated_unix_timestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_FEED_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

?>
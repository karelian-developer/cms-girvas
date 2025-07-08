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

if (is_numeric($CMSCore->urlp->getPath(2))) {
  $feedID = (int) $CMSCore->urlp->getPath(2);

  if (Feed::existsByID($CMSCore, $feedID)) {
    $feed = new Feed($CMSCore, $feedID);
    $feed->initData(['name', 'typeID', 'entriesCategoryID', 'texts', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $feedLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['feed'] = [];
    $handlerOutputData['feed']['id'] = $feed->getID();
    $handlerOutputData['feed']['name'] = $feed->getName();
    $handlerOutputData['feed']['title'] = $feed->getTitle($feedLocale);
    $handlerOutputData['feed']['description'] = $feed->getDescription($feedLocale);
    $handlerOutputData['feed']['typeID'] = $feed->getTypeID();
    $handlerOutputData['feed']['entriesCategoryID'] = $feed->getEntriesCategoryID();
    $handlerOutputData['feed']['createdUnixTimestamp'] = $feed->getCreatedUnixTimestamp();
    $handlerOutputData['feed']['updatedUnixTimestamp'] = $feed->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FEED_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}
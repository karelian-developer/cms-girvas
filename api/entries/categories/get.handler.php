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

use \core\PHPLibrary\EntriesCategories as EntriesCategories;

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  $handlerOutputData['entriesCategories'] = [];

  $localeName = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

  $entriesCategories = new EntriesCategories($CMSCore);
  $entriesCategoriesObjects = $entriesCategories->getAll();

  foreach ($entriesCategoriesObjects as $objects) {
    $objects->initData(['texts']);

    $handlerOutputData['entriesCategories'][] = [
      'id' => $objects->getID(),
      'title' => $objects->getTitle($localeName)
    ];
  }

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
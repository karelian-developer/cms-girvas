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

if ($CMSCore->client->is_logged(1) || $CMSCore->client->is_logged(2)) {
  $handlerOutputData['entriesCategories'] = [];

  $localeName = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

  $entriesCategories = new EntriesCategories($CMSCore);
  $entriesCategoriesObjects = $entriesCategories->get_all();

  foreach ($entriesCategoriesObjects as $objects) {
    $objects->init_data(['texts']);

    $handlerOutputData['entriesCategories'][] = [
      'id' => $objects->get_id(),
      'title' => $objects->get_title($localeName)
    ];
  }

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
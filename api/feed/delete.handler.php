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
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedID = $_DELETE['feed_id'] ?? 0;
    $feedID = (is_numeric($feedID)) ? (int)$feedID : 0;

    if ($feedID != 0) {
      if (Feed::existsByID($CMSCore, $feedID)) {
        $feed = new Feed($CMSCore, $feedID);
        
        // Получаем данные фида перед удалением
        $feed->initData(['name', 'texts']);
        $feedName = $feed->getName();
        $feedTypeID = $feed->getTypeID();
        $feedCategoryID = $feed->getEntriesCategoryID();

        $feedTitles = [];
        $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
        foreach ($CMSLocalesNames as $localeName) {
          $feedTitles[$localeName] = $feed->getTitle($localeName);
        }

        // ============================================================
        // ЛОГИРОВАНИЕ УДАЛЕНИЯ ВЕБ-КАНАЛА (152-ФЗ)
        // ============================================================
        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_FEED_DELETED,
          [
            'feedID' => $feedID,
            'feedName' => $feedName,
            'feedTitles' => $feedTitles,
            'feedTypeID' => $feedTypeID,
            'feedCategoryID' => $feedCategoryID,
            'deletedByID' => $clientUser->getID(),
            'deletedByLogin' => $clientUser->getLogin(),
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );

        $feedIsDeleted = $feed->delete();
        if ($feedIsDeleted) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
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
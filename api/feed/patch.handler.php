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
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) {
    $feedID = (isset($_PATCH['feed_id'])) ? $_PATCH['feed_id'] : 0;
    $feedID = (is_numeric($feedID)) ? (int)$feedID : 0;

    if (Feed::existsByID($CMSCore, $feedID)) {
      $feed = new Feed($CMSCore, $feedID);
      $feed->initData(['name', 'texts', 'typeID', 'entriesCategoryID']);
      
      // Сохраняем старые значения для сравнения
      $oldValues = [
        'name' => $feed->getName(),
        'title' => $feed->getTitle($CMSCore->locale->getName()),
        'typeID' => $feed->getTypeID(),
        'entriesCategoryID' => $feed->getEntriesCategoryID(),
      ];
      
      $feedData = [];
      $changedFields = [];

      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $name) {
          $CMSLocale = new CMSLocale($CMSCore, $name);
          $CMSLocale->setTypeName('handler');
          $CMSLocale->initPathes();

          $CMSLocaleName = $CMSLocale->getName();

          $inputTitleName = 'feed_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'feed_description_' . $CMSLocale->getISO639(2);

          if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH)) {
            if (!array_key_exists('texts', $feedData)) $feedData['texts'] = [];
            if (!array_key_exists($CMSLocaleName, $feedData['texts'])) $feedData['texts'][$CMSLocaleName] = [];

            if (array_key_exists($inputTitleName, $_PATCH)) {
              $feedData['texts'][$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$inputTitleName]));
              if ($oldValues['title'] !== $feedData['texts'][$CMSLocaleName]['title']) $changedFields[] = 'title';
            }
            
            if (array_key_exists($textareaDescriptionName, $_PATCH)) {
              $feedData['texts'][$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$textareaDescriptionName]));
              $changedFields[] = 'description';
            }
          }
        }
      }

      if (isset($_PATCH['feed_name'])) {
        $feedData['name'] = urlencode(htmlentities($_PATCH['feed_name']));
        if ($oldValues['name'] !== $feedData['name']) $changedFields[] = 'name';
      }
      
      if (isset($_PATCH['feed_type_id'])) {
        $feedData['typeID'] = $_PATCH['feed_type_id'];
        if ($oldValues['typeID'] != $_PATCH['feed_type_id']) $changedFields[] = 'type_id';
      }
      
      if (isset($_PATCH['feed_entries_category_id'])) {
        $feedData['entriesCategoryID'] = $_PATCH['feed_entries_category_id'];
        if ($oldValues['entriesCategoryID'] != $_PATCH['feed_entries_category_id']) $changedFields[] = 'category_id';
      }

      $feedIsUpdated = $feed->update($feedData);

      if ($feedIsUpdated) {
        // ============================================================
        // ЛОГИРОВАНИЕ ОБНОВЛЕНИЯ ВЕБ-КАНАЛА (152-ФЗ)
        // ============================================================
        $feed->initData(['name', 'texts']);

        $feedTitles = [];
        foreach ($CMSLocalesNames as $localeName) {
          $feedTitles[$localeName] = $feed->getTitle($localeName);
        }

        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_FEED_EDITED,
          [
            'feedID' => $feedID,
            'feedName' => $feed->getName(),
            'feedTitles' => $feedTitles,
            'feedTypeID' => $feed->getTypeID(),
            'feedCategoryID' => $feed->getEntriesCategoryID(),
            'updatedByID' => $clientUser->getID(),
            'updatedByLogin' => $clientUser->getLogin(),
            'changedFields' => $changedFields,
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );

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
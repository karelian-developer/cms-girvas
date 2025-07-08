<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2023, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($CMSCore->urlp->getPath(2) === 'category') {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      $entriesCategoryID = is_numeric($_DELETE['entries_category_id']) ? (int) $_DELETE['entries_category_id'] : 0;
      $entries = new Entries($CMSCore);

      if (EntryCategory::existsByID($CMSCore, $entriesCategoryID)) {
        if ($entries->getCountByCategoryID($entriesCategoryID) === 0) {
          $entriesСategory = new EntryCategory($CMSCore, $entriesCategoryID);
          $entriesСategoryIsDeleted = $entriesСategory->delete();

          if ($entriesСategoryIsDeleted) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_CATEGORY_ERROR_DELETION_EXISTS_ENTRIES');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_DELETE['entry_id'])) {
        $entryID = is_numeric($_DELETE['entry_id']) ? (int) $_DELETE['entry_id'] : 0;

        if (Entry::existsByID($CMSCore, $entryID)) {
          $entry = new Entry($CMSCore, $entryID);

          $entry->initData(['texts']);
          $entryTitle = $entry->getTitle();

          $entryIsDeleted = $entry->delete();

          if ($entryIsDeleted) {
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED, [
              'clientIP' => $CMSCore->client->getIPAddress(),
              'entryTitle' => $entryTitle,
              'date' => date('Y/m/d H:i:s', time())
            ]);

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }

        $handler_output_data['modalClose'] = true;
        $handler_output_data['reload'] = true;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
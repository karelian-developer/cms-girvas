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
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($CMSCore->urlp->getPath(2) === 'category') {
    // ============================================================
    // УДАЛЕНИЕ КАТЕГОРИИ
    // ============================================================
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      $entriesCategoryID = is_numeric($_DELETE['entries_category_id']) ? (int) $_DELETE['entries_category_id'] : 0;
      $entries = new Entries($CMSCore);

      if (EntryCategory::existsByID($CMSCore, $entriesCategoryID)) {
        if ($entries->getCountByCategoryID($entriesCategoryID) === 0) {
          $entriesCategory = new EntryCategory($CMSCore, $entriesCategoryID);
          $entriesCategory->initData(['name', 'texts']);

          // Получаем все языковые версии заголовка
          $categoryTitles = [];
          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          foreach ($CMSLocalesNames as $localeName) {
            $categoryTitles[$localeName] = $entriesCategory->getTitle($localeName);
          }
          $categoryName = $entriesCategory->getName();

          CMSReport::create(
            $CMSCore,
            CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED,
            [
              'categoryID' => $entriesCategoryID,
              'categoryName' => $categoryName,
              'categoryTitles' => $categoryTitles, // ← массив по локалям
              'deletedByID' => $clientUser->getID(),
              'deletedByLogin' => $clientUser->getLogin(),
              'ip' => $CMSCore->client->getIPAddress()
            ]
          );
          
          $entriesCategoryIsDeleted = $entriesCategory->delete();

          if ($entriesCategoryIsDeleted) {
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
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    // ============================================================
    // УДАЛЕНИЕ ЗАПИСИ
    // ============================================================
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_DELETE['entry_id'])) {
        $entryID = is_numeric($_DELETE['entry_id']) ? (int) $_DELETE['entry_id'] : 0;

        if (Entry::existsByID($CMSCore, $entryID)) {
          $entry = new Entry($CMSCore, $entryID);
          $entry->initData(['texts', 'name', 'metadata']);

          // Получаем все языковые версии заголовка
          $entryTitles = [];
          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          foreach ($CMSLocalesNames as $localeName) {
            $entryTitles[$localeName] = $entry->getTitle($localeName);
          }
          $entryName = $entry->getName();

          // Логируем удаление записи (152-ФЗ)
          CMSReport::create(
            $CMSCore,
            CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED,
            [
              'entryID' => $entryID,
              'entryName' => $entryName,
              'entryTitles' => $entryTitles,
              'deletedByID' => $clientUser->getID(),
              'deletedByLogin' => $clientUser->getLogin(),
              'ip' => $CMSCore->client->getIPAddress()
            ]
          );

          $entryIsDeleted = $entry->delete();

          if ($entryIsDeleted) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }

        $handlerOutputData['modalClose'] = true;
        $handlerOutputData['reload'] = true;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
        $handlerStatusCode = $handlerStatusCode ?? 0;
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
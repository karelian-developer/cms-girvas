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
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($CMSCore->urlp->getPath(2) === 'category') {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      $entriesCategoryName = isset($_PUT['entries_category_name']) ? urlencode(htmlentities($_PUT['entries_category_name'])) : '';
      $entriesCategoryParentID = $_PUT['entries_category_parent_id'] ?? 0;
      $entriesCategoryParentID = is_numeric($entriesCategoryParentID) ? (int) $entriesCategoryParentID : 0;

      $texts = [];
      $metadata = [];
      
      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          $CMSLocale = new Locale($CMSCore, $localeName);
          $CMSLocaleName = $CMSLocale->getName();

          $inputTitleName = 'entries_category_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'entries_category_description_' . $CMSLocale->getISO639(2);

          if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT)) {
            if (!array_key_exists($CMSLocaleName, $texts)) $texts[$CMSLocaleName] = [];

            if (array_key_exists($inputTitleName, $_PUT)) {
              $inputValue = $_PUT[$inputTitleName];
              $inputValue = strip_tags($inputValue);
              $inputValue = str_replace('\'', '"', $inputValue);
  
              $texts[$CMSLocaleName]['title'] = $inputValue;
            }

            if (array_key_exists($textareaDescriptionName, $_PUT)) {
              $textareaValue = $_PUT[$textareaDescriptionName];
              $textareaValue = strip_tags($textareaValue);
              $textareaValue = str_replace('\'', '"', $textareaValue);
  
              $texts[$CMSLocaleName]['description'] = $textareaValue;
            }
          }
        }
      }

      $entriesCategory = EntryCategory::create($CMSCore, $entriesCategoryName, $entriesCategoryParentID, $texts, $metadata);
      if (!is_null($entriesCategory)) {
        $entriesCategory->initData(['metadata']);

        if (isset($_PUT['entries_category_show_index'])) {
          if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
          $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 1;
        } else {
          if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
          $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 0;
        }

        $handlerOutputData['entriesCategory'] = [];
        $handlerOutputData['entriesCategory']['id'] = $entriesCategory->getID();

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
  }

  if ($CMSCore->urlp->getPath(2) == null) {
    $entryTitleLengthMax = 80;
    $entryDescriptionLengthMax = 600;

    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      $entryName = isset($_PUT['entry_name']) ? urlencode(htmlentities($_PUT['entry_name'])) : '';
      $entryCategoryID = $_PUT['entry_category_id'] ?? 1;
      $entryCategoryID = is_numeric($entryCategoryID) ? (int) $entryCategoryID : 0;
      $texts = [];

      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          $CMSLocale = new Locale($CMSCore, $localeName);
          $CMSLocaleName = $CMSLocale->getName();

          $inputTitleName = 'entry_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'entry_description_' . $CMSLocale->getISO639(2);
          $textareaContentName = 'entry_content_' . $CMSLocale->getISO639(2);
          $textareaKeywordsName = 'entry_keywords_' . $CMSLocale->getISO639(2);

          if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT) || array_key_exists($textareaContentName, $_PUT)) {
            if (!array_key_exists($CMSLocaleName, $texts)) $texts[$CMSLocaleName] = [];

            if (array_key_exists($inputTitleName, $_PUT)) {
              $inputValue = $_PUT[$inputTitleName];
              $inputValue = strip_tags($inputValue);
              $inputValue = str_replace('\'', '"', $inputValue);
  
              $texts[$CMSLocaleName]['title'] = $inputValue;
            }

            if (array_key_exists($textareaDescriptionName, $_PUT)) {
              $textareaValue = $_PUT[$textareaDescriptionName];
              $textareaValue = strip_tags($textareaValue);
              $textareaValue = str_replace('\'', '"', $textareaValue);
  
              $texts[$CMSLocaleName]['description'] = $textareaValue;
            }
            
            if (array_key_exists($textareaContentName, $_PUT)) {
              $textareaValue = $_PUT[$textareaContentName];
              $textareaValue = strip_tags($textareaValue, '<table><tr><td><th><b><u><i><hr>');
              $textareaValue = str_replace('\'', '"', $textareaValue);
  
              $texts[$CMSLocaleName]['content'] = $textareaValue;
            }

            if (array_key_exists($textareaKeywordsName, $_PUT)) {
              $textareaValue = $_PUT[$textareaKeywordsName];
              $textareaValue = strip_tags($textareaValue);
              $textareaValue = str_replace('\'', '"', $textareaValue);

              $texts[$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
            }
          }
        }
      }

      foreach ($_PUT as $key => $value) {
        if (preg_match('/^entry\_additional\_field\_([a-z0-9\_]+)$/i', $key, $key_matches, PREG_OFFSET_CAPTURE) && !empty($value)) {
          if (!isset($entryData)) $entryData = [];
          if (!isset($entryData['metadata'])) $entryData['metadata'] = [];
          if (!isset($entryData['metadata']['additionalFields'])) $entryData['metadata']['additionalFields'] = [];
          
          $valueNameParts = explode('_', $key_matches[1][0]);
          foreach ($valueNameParts as $index => $part) {
            if ($index > 0) {
              $valueNameParts[$index] = ucfirst($part);
            }
          }
  
          if (is_bool($value)) $value = (int) $value;
  
          $entryData['metadata']['additionalFields'][implode($valueNameParts)] = htmlspecialchars(str_replace('\'', '"', $value));
        }
      }

      $clientSession = $CMSCore->client->getSession(2, ['userID']);
      $entry = Entry::create($CMSCore, $entryName, $clientSession->getUserID(), 1, $texts);
      if (!is_null($entry)) {
        $entry->initData(['texts']);

        // Обновление дополнительной информации
        $entryData['categoryID'] = $entryCategoryID;
        $entry->update($entryData);

        $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED, [
          'clientIP' => $CMSCore->client->getIPAddress(),
          'entryTitle' => $entry->getTitle(),
          'date' => date('Y/m/d H:i:s', time())
        ]);
        
        $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;

        $handlerOutputData['entry'] = [];
        $handlerOutputData['entry']['id'] = $entry->getID();

        $handlerOutputData['href'] = '/admin/entry/' . $entry->getID();
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
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
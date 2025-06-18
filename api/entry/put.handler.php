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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($CMSCore->urlp->get_path(2) === 'category') {
    if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      $entriesCategoryName = isset($_PUT['entries_category_name']) ? urlencode(htmlentities($_PUT['entries_category_name'])) : '';
      $entriesCategoryParentID = $_PUT['entries_category_parent_id'] ?? 0;
      $entriesCategoryParentID = is_numeric($entriesCategoryParentID) ? (int) $entriesCategoryParentID : 0;

      $texts = [];
      $metadata = [];
      
      $CMSLocalesNames = $CMSCore->get_array_locales_names();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          $CMSLocale = new Locale($CMSCore, $localeName);
          $CMSLocaleName = $CMSLocale->get_name();

          $inputTitleName = 'entries_category_title_' . $CMSLocale->get_iso_639_2();
          $textareaDescriptionName = 'entries_category_description_' . $CMSLocale->get_iso_639_2();

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
        $entriesCategory->init_data(['metadata']);

        if (isset($_PUT['entries_category_show_index'])) {
          if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
          $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 1;
        } else {
          if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
          $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 0;
        }

        $handlerOutputData['entriesCategory'] = [];
        $handlerOutputData['entriesCategory']['id'] = $entriesCategory->get_id();

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }

  if ($CMSCore->urlp->get_path(2) == null) {
    $entryTitleLengthMax = 80;
    $entryDescriptionLengthMax = 600;

    if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      $entryName = isset($_PUT['entry_name']) ? urlencode(htmlentities($_PUT['entry_name'])) : '';
      $entryCategoryID = $_PUT['entry_category_id'] ?? 1;
      $entryCategoryID = is_numeric($entryCategoryID) ? (int) $entryCategoryID : 0;
      $texts = [];

      $CMSLocalesNames = $CMSCore->get_array_locales_names();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          $CMSLocale = new Locale($CMSCore, $localeName);
          $CMSLocaleName = $CMSLocale->get_name();

          $inputTitleName = 'entry_title_' . $CMSLocale->get_iso_639_2();
          $textareaDescriptionName = 'entry_description_' . $CMSLocale->get_iso_639_2();
          $textareaContentName = 'entry_content_' . $CMSLocale->get_iso_639_2();
          $textareaKeywordsName = 'entry_keywords_' . $CMSLocale->get_iso_639_2();

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

      $clientSession = $CMSCore->client->get_session(2, ['userID']);
      $entry = Entry::create($CMSCore, $entryName, $clientSession->get_user_id(), 1, $texts);
      if (!is_null($entry)) {
        $entry->init_data(['texts']);

        // Обновление дополнительной информации
        $entryData['category_id'] = $entryCategoryID;
        $entry->update($entryData);

        $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED, [
          'clientIP' => $CMSCore->client->get_ip_address(),
          'entryTitle' => $entry->get_title(),
          'date' => date('Y/m/d H:i:s', time())
        ]);
        
        $handlerMessage = $CMSCore->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;

        $handlerOutputData['entry'] = [];
        $handlerOutputData['entry']['id'] = $entry->get_id();

        $handlerOutputData['href'] = '/admin/entry/' . $entry->get_id();
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
 $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
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
use \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat as FileConverterEnumFileFormat;
use \core\PHPLibrary\SystemCore\FileConverter as FileConverter;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($CMSCore->urlp->get_path(2) === 'category') {
    if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      if (isset($_PATCH['entries_category_id'])) {
        $entriesCategoryID = $_PATCH['entries_category_id'] ?? 0;
        $entriesCategoryID = is_numeric($entriesCategoryID) ? (int) $entriesCategoryID : 0;

        if (EntryCategory::exists_by_id($CMSCore, $entriesCategoryID)) {
          $entriesCategory = new EntryCategory($CMSCore, $entriesCategoryID);
          $entriesCategory->init_data(['metadata']);
          $entriesCategoryData = [];

          $CMSLocalesNames = $CMSCore->get_array_locales_names();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $CMSLocaleName) {
              $CMSLocale = new Locale($CMSCore, $CMSLocaleName);
              $CMSLocaleName = $CMSLocale->get_name();

              $inputTitleName = 'entries_category_title_' . $CMSLocale->get_iso_639_2();
              $textareaDescriptionName = 'entries_category_description_' . $CMSLocale->get_iso_639_2();

              if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH)) {
                if (!array_key_exists('texts', $entriesCategoryData)) $entriesCategoryData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $entriesCategoryData['texts'])) $entriesCategoryData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($inputTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputTitleName];
                  $inputValue = strip_tags($inputValue);
                  $inputValue = str_replace('\'', '"', $inputValue);
                  $inputValue = htmlspecialchars($inputValue);
      
                  $entriesCategoryData['texts'][$CMSLocaleName]['title'] = $inputValue;
                }
    
                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $textareaValue = htmlspecialchars($textareaValue);
      
                  $entriesCategoryData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                }
              }
            }
          }
          
          if (isset($_PATCH['entries_category_name'])) $entriesCategoryData['name'] = urlencode(htmlentities($_PATCH['entries_category_name']));
          if (isset($_PATCH['entries_category_parent_id'])) $entriesCategoryData['parent_id'] = $_PATCH['entries_category_parent_id'];
          
          if (isset($_PATCH['entries_category_show_index'])) {
            if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
            $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 1;
          } else {
            if (!isset($entriesCategoryData['metadata'])) $entriesCategoryData['metadata'] = [];
            $entriesCategoryData['metadata']['isShowedOnIndexPage'] = 0;
          }
          
          $entriesCategoryIsUpdated = $entriesCategory->update($entriesCategoryData);

          if ($entriesCategoryIsUpdated) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    }
  } else {
    $entryTitleLengthMax = 80;
    $entryDescriptionLengthMax = 600;

    if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_PATCH['entry_id'])) {
        $entryID = $_PATCH['entry_id'] ?? 0;
        $entryID = is_numeric($entryID) ? (int) $entryID : 0;

        if (Entry::exists_by_id($CMSCore, $entryID)) {
          $entry = new Entry($CMSCore, $entryID);
          $entryData = [];

          $CMSLocalesNames = $CMSCore->get_array_locales_names();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $CMSLocaleName) {
              $CMSLocale = new Locale($CMSCore, $CMSLocaleName);
              $CMSLocaleName = $CMSLocale->get_name();

              $inputTitleName = 'entry_title_' . $CMSLocale->get_iso_639_2();
              $textareaDescriptionName = 'entry_description_' . $CMSLocale->get_iso_639_2();
              $textareaContentName = 'entry_content_' . $CMSLocale->get_iso_639_2();
              $textareaKeywordsName = 'entry_keywords_' . $CMSLocale->get_iso_639_2();

              if (!array_key_exists('metadata', $entryData)) $entryData['metadata'] = [];
              if (isset($_PATCH['entry_is_published'])) $entryData['metadata']['isPublished'] = $_PATCH['entry_is_published'];

              if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH) || array_key_exists($textareaContentName, $_PATCH)) {
                if (!array_key_exists('texts', $entryData)) $entryData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $entryData['texts'])) $entryData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($inputTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputTitleName];
                  $inputValue = strip_tags($inputValue);
                  $inputValue = str_replace('\'', '"', $inputValue);
      
                  $entryData['texts'][$CMSLocaleName]['title'] = $inputValue;
                }
    
                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $entryData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                }
                
                if (array_key_exists($textareaContentName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaContentName];
                  $textareaValue = strip_tags($textareaValue, '<table><tr><td><th><b><u><i><hr>');
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $entryData['texts'][$CMSLocaleName]['content'] = $textareaValue;
                }
    
                if (array_key_exists($textareaKeywordsName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaKeywordsName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
    
                  $entryData['texts'][$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
                }
              }
            }
          }

          if (isset($_PATCH['entry_name'])) $entryData['name'] = urlencode(htmlentities($_PATCH['entry_name']));
          if (isset($_PATCH['entry_category_id'])) $entryData['categoryID'] = $_PATCH['entry_category_id'];
          
          if (isset($_PATCH['entry_preview'])) {
            $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
            $fileConverter = new FileConverter($CMSCore);
            $fileConverted = $fileConverter->convert($_PATCH['entry_preview'], $fileDirectoryPath, FileConverterEnumFileFormat::WEBP, true);
            
            if (is_array($fileConverted)) {
              if (!array_key_exists('metadata', $entryData)) $entryData['metadata'] = [];
              $entryData['metadata']['previewURL'] = '/uploads/media/' . $fileConverted['file_name'];
            }
          }

          foreach ($_PATCH as $name => $value) {
            if (preg_match('/^entry_additional_field_([a-z0-9_]+)$/', $name, $matches, PREG_OFFSET_CAPTURE)) {
              if (!isset($entryData['metadata']['additionalFields'])) $entryData['metadata']['additionalFields'] = [];
              
              $fieldName = $matches[1][0];
              $fieldNameTransformed = '';
  
              $fieldNameParts = explode('_', $fieldName);
              for ($i = 0; $i < count($fieldNameParts); $i++) {
                $fieldNameTransformed .= $i > 0 ? ucfirst($fieldNameParts[$i]) : $fieldNameParts[$i];
              }
  
              $entryData['metadata']['additionalFields'][$fieldNameTransformed] = htmlspecialchars(str_replace('\'', '"', $value));
            }
          }

          $entryIsPublished = $entryData['metadata']['isPublished'] ?? 0;

          // Если происходит публикация записи, то необходимо удостовериться, что
          // в записи присутствует стандартная локализация, в противном случае
          // система не даст сохранить ее.
          if ($entryIsPublished) {
            $CMSBaseLocale = $CMSCore->get_cms_locale();
            $CMSBaseLocaleName = $CMSBaseLocale->get_name();

            $entry->init_data(['texts']);

            /** @var string Заголовок записи */
            $entryTitle = $entry->get_title($CMSBaseLocaleName);
            /** @var string описание записи */
            $entryDescription = $entry->get_description($CMSBaseLocaleName);
            /** @var string содержимое записи */
            $entryContent = $entry->get_content($CMSBaseLocaleName);
            /** @var int дата обновления страницы в формате UNIX */
            $entryData['metadata']['publishedUnixTimestamp'] = time();

            // Если заголовок, описание или содержимое стандартной локализации не задано, то
            // запись не будет обновлена.
            if (empty($entryTitle) || empty($entryDescription) || empty($entryContent)) {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_ENTRY_EMPTY_LOCALE_DEFAULT_PUBLISHED_ERROR'), $CMSBaseLocaleName);
              $handlerStatusCode = $handlerStatusCode ?? 0;
            } else {
              /** @var bool Обновление записи */
              $entryIsUpdated = $entry->update($entryData);
            }
          } else {
            /** @var bool Обновление записи */
            $entryIsUpdated = $entry->update($entryData);
          }

          /** @var bool Костыль */
          $entryIsUpdated = isset($entryIsUpdated) ? $entryIsUpdated : false;

          if ($entryIsUpdated) {
            // Инициализация данных с текстом записи
            $entry->init_data(['texts']);
            
            /** @var CMSReport Новый отчет */
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED, [
              'clientIP' => $CMSCore->client->get_ip_address(),
              'entryTitle' => $entry->get_title(),
              'date' => date('Y/m/d H:i:s', time())
            ]);

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
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
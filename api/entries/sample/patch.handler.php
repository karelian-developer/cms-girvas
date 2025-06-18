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

use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\EntriesSamples as EntriesSamples;
use \core\PHPLibrary\SystemCore\Locale as Locale;
use \core\PHPLibrary\EntriesSample\EnumSortTypeID as EnumSortTypeID;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
    $dataUpdated = [];
    
    /** @var int ID выборки */
    $sampleID = $CMSCore->urlp->get_path(3) ?? 0;
    $sampleID = is_numeric($sampleID) ? (int) $sampleID : 0;
    
    /** @var string Техническое наименование выборки */
    $sampleName = $_PATCH['entries_sample_name'] ?? '';
    $sampleName = strtolower(trim($sampleName));

    /** @var int Лимит на количество записей в выборке */
    $sampleLimitCount = $_PATCH['entries_sample_limit_count'] ?? 0;
    $sampleLimitCount = is_numeric($sampleLimitCount) ? (int) $sampleLimitCount : 0;
    
    $sampleSortTypeID = $_PATCH['entries_sample_sort_type_id'] ?? 0;
    $sampleSortTypeID = is_numeric($sampleSortTypeID) ? (int) $sampleSortTypeID : 0;
    $sampleSortTypeID = match ($sampleSortTypeID) {
      1 => EnumSortTypeID::BY_DATE_OF_PUBLICATION->get_id(),
      2 => EnumSortTypeID::BY_DATE_OF_CREATION->get_id(),
      3 => EnumSortTypeID::BY_NUMBER_OF_VIEW->get_id(),
      4 => EnumSortTypeID::BY_NUMBER_OF_COMMENTS->get_id(),
      5 => EnumSortTypeID::BY_RELEVANCE->get_id(),
      default => EnumSortTypeID::BY_DATE_OF_PUBLICATION->get_id()
    };

    $sampleCategoriesIDs = $_PATCH['entries_sample_categories_id'] ?? [];
    
    /** @var array Текстовые значения для выборки */
    $sampleTexts = [];

    $CMSLocalesNames = $CMSCore->get_array_locales_names();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $localeName) {
        $CMSLocale = new Locale($CMSCore, $localeName);
        $CMSLocaleName = $CMSLocale->get_name();

        $inputTitleName = 'entries_sample_title_' . $CMSLocale->get_iso_639_2();
        $textareaDescriptionName = 'entries_sample_description_' . $CMSLocale->get_iso_639_2();

        if (isset($_PATCH[$inputTitleName]) || isset($_PATCH[$textareaDescriptionName])) {
          $dataUpdated['texts'] = [];
          
          $sampleTitle = $_PATCH[$inputTitleName] ?? '';
          $sampleDescription = $_PATCH[$textareaDescriptionName] ?? '';
          
          if (!isset($sampleTexts[$localeName])) $sampleTexts[$localeName] = [];

          if (preg_match('/\S/', $sampleTitle)) {
            $inputValue = trim($sampleTitle);
            $inputValue = strip_tags($inputValue);
            $inputValue = str_replace('\'', '"', $inputValue);

            $dataUpdated['texts'][$localeName]['title'] = $inputValue;
          }

          if (preg_match('/\S/', $sampleDescription)) {
            $textareaValue = trim($sampleDescription);
            $textareaValue = strip_tags($textareaValue);
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $dataUpdated['texts'][$localeName]['description'] = $textareaValue;
          }
        }
      }
    }

    /** @var array Метаданные для выборки */
    $dataUpdated['metadata'] = [];

    $dataUpdated['metadata']['limitCount'] = $sampleLimitCount;
    $dataUpdated['metadata']['sortTypeID'] = $sampleSortTypeID;

    if (!empty($sampleCategoriesIDs)) {
      $dataUpdated['metadata']['categoriesIDs'] = [];

      foreach ($sampleCategoriesIDs as $id) {
        if (EntryCategory::exists_by_id($CMSCore, $id)) {
          array_push($dataUpdated['metadata']['categoriesIDs'], $id);
        }
      }
    }

    if (preg_match('/\S/', $sampleName)) {
      if (EntriesSample::exists_by_id($CMSCore, $sample_id)) {
        $sample = new EntriesSample($CMSCore, $sample_id);
        $sample->init_data(['name', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

        if (!EntriesSample::exists_by_name($CMSCore, $sampleName) || $sampleName === $sample->get_name()) {
          $dataUpdated['name'] = $sampleName;

          $isUpdated = $sample->update($dataUpdated);

          if ($isUpdated) {
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRIES_SAMPLE_ERROR_NAME_EMPTY');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
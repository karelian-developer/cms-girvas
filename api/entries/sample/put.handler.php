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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
    /** @var string Техническое наименование выборки */
    $sampleName = $_PUT['entries_sample_name'] ?? '';
    $sampleName = trim($sampleName);
    $sampleName = strtolower($sampleName);

    /** @var int Лимит на количество записей в выборке */
    $sampleLimitCount = $_PUT['entries_sample_limit_count'] ?? 0;
    $sampleLimitCount = is_numeric($sampleLimitCount) ? (int) $sampleLimitCount : 0;
    
    $sampleSortTypeID = $_PUT['entries_sample_sort_type_id'] ?? 0;
    $sampleSortTypeID = is_numeric($sampleSortTypeID) ? (int) $sampleSortTypeID : 0;
    $sampleSortTypeID = match ($sampleSortTypeID) {
      1 => EnumSortTypeID::BY_DATE_OF_PUBLICATION->getID(),
      2 => EnumSortTypeID::BY_DATE_OF_CREATION->getID(),
      3 => EnumSortTypeID::BY_NUMBER_OF_VIEW->getID(),
      4 => EnumSortTypeID::BY_NUMBER_OF_COMMENTS->getID(),
      5 => EnumSortTypeID::BY_RELEVANCE->getID(),
      default => EnumSortTypeID::BY_DATE_OF_PUBLICATION->getID()
    };

    $sampleCategoriesIDs = $_PUT['entries_sample_categories_id'] ?? [];
    
    /** @var array Текстовые значения для выборки */
    $sampleTexts = [];

    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $localeName) {
        $CMSLocale = new Locale($CMSCore, $localeName);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();
        
        $CMSLocaleName = $CMSLocale->getName();

        $inputTitleName = 'entries_sample_title_' . $CMSLocale->getISO639(2);
        $textareaDescriptionName = 'entries_sample_description_' . $CMSLocale->getISO639(2);

        if (isset($_PUT[$inputTitleName]) || isset($_PUT[$textareaDescriptionName])) {
          $sampleTitle = $_PUT[$inputTitleName] ?? '';
          $sampleDescription = $_PUT[$textareaDescriptionName] ?? '';
          
          if (!isset($sampleTexts[$localeName])) $sampleTexts[$localeName] = [];

          if (preg_match('/\S/', $sampleTitle)) {
            $inputValue = trim($sampleTitle);
            $inputValue = strip_tags($inputValue);
            $inputValue = str_replace('\'', '"', $inputValue);

            $sampleTexts[$localeName]['title'] = $inputValue;
          }

          if (preg_match('/\S/', $sampleDescription)) {
            $textareaValue = trim($sampleDescription);
            $textareaValue = strip_tags($textareaValue);
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $sampleTexts[$localeName]['description'] = $textareaValue;
          }
        }
      }
    }

    /** @var array Метаданные для выборки */
    $sampleMetadata = [];

    $sampleMetadata['limitCount'] = $sampleLimitCount;
    $sampleMetadata['sortTypeID'] = $sampleSortTypeID;

    if (!empty($sampleCategoriesIDs)) {
      $sampleMetadata['categoriesIDs'] = [];

      foreach ($sampleCategoriesIDs as $id) {
        if (EntryCategory::existsByID($CMSCore, $id)) {
          array_push($sampleMetadata['categoriesIDs'], $id);
        }
      }
    }

    if (preg_match('/\S/', $sampleName)) {
      if (!EntriesSample::existsByName($CMSCore, $sampleName)) {
        $sample = EntriesSample::create($CMSCore, $sampleName, $sampleTexts, $sampleMetadata);

        if (!is_null($sample)) {
          $handleOutputData['entriesSample'] = [];
          $handleOutputData['entriesSample']['id'] = $sample->getID();

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
          $handlerStatusCode = 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_SAMPLE_ERROR_NAME_EXISTS');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_SAMPLE_ERROR_NAME_EMPTY');
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
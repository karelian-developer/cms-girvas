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
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
    $dataUpdated = [];
    $changedFields = [];
    
    /** @var int ID выборки */
    $sampleID = $CMSCore->urlp->getPath(3) ?? 0;
    $sampleID = is_numeric($sampleID) ? (int) $sampleID : 0;
    
    if (!EntriesSample::existsByID($CMSCore, $sampleID)) {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_SAMPLE_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $sample = new EntriesSample($CMSCore, $sampleID);
    $sample->initData(['name', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    
    // Сохраняем старые значения для сравнения
    $oldValues = [
      'name' => $sample->getName(),
      'title' => $sample->getTitle($CMSCore->locale->getName()),
      'description' => $sample->getDescription($CMSCore->locale->getName()),
      'limitCount' => $sample->getLimitCount(),
      'sortTypeID' => $sample->getSortTypeID(),
      'categoriesIDs' => $sample->getCategoriesIDs(),
    ];
    
    /** @var string Техническое наименование выборки */
    $sampleName = $_PATCH['entries_sample_name'] ?? '';
    $sampleName = strtolower(trim($sampleName));

    /** @var int Лимит на количество записей в выборке */
    $sampleLimitCount = $_PATCH['entries_sample_limit_count'] ?? 0;
    $sampleLimitCount = is_numeric($sampleLimitCount) ? (int) $sampleLimitCount : 0;
    
    $sampleSortTypeID = $_PATCH['entries_sample_sort_type_id'] ?? 0;
    $sampleSortTypeID = is_numeric($sampleSortTypeID) ? (int) $sampleSortTypeID : 0;
    $sampleSortTypeID = match ($sampleSortTypeID) {
      1 => EnumSortTypeID::BY_DATE_OF_PUBLICATION->getID(),
      2 => EnumSortTypeID::BY_DATE_OF_CREATION->getID(),
      3 => EnumSortTypeID::BY_NUMBER_OF_VIEW->getID(),
      4 => EnumSortTypeID::BY_NUMBER_OF_COMMENTS->getID(),
      5 => EnumSortTypeID::BY_RELEVANCE->getID(),
      default => EnumSortTypeID::BY_DATE_OF_PUBLICATION->getID()
    };

    $sampleCategoriesIDs = $_PATCH['entries_sample_categories_id'] ?? [];
    
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
        if (EntryCategory::existsByID($CMSCore, $id)) {
          array_push($dataUpdated['metadata']['categoriesIDs'], $id);
        }
      }
    }

    if (preg_match('/\S/', $sampleName)) {
      if (EntriesSample::existsByID($CMSCore, $sampleID)) {
        if (!EntriesSample::existsByName($CMSCore, $sampleName) || $sampleName === $sample->getName()) {
          $dataUpdated['name'] = $sampleName;

          $isUpdated = $sample->update($dataUpdated);

          // ============================================================
          // ЛОГИРОВАНИЕ ОБНОВЛЕНИЯ ВЫБОРКИ (152-ФЗ)
          // ============================================================
          if ($isUpdated) {
            // Определяем, какие поля изменились
            $newValues = [
              'name' => $sample->getName(),
              'title' => $sample->getTitle($CMSCore->locale->getName()),
              'description' => $sample->getDescription($CMSCore->locale->getName()),
              'limitCount' => $sample->getLimitCount(),
              'sortTypeID' => $sample->getSortTypeID(),
              'categoriesIDs' => $sample->getCategoriesIDs(),
            ];
            
            foreach ($newValues as $key => $value) {
              if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changedFields[] = $key;
              }
            }
            
            CMSReport::create(
              $CMSCore,
              CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_EDITED,
              [
                'sampleID' => $sample->getID(),
                'sampleName' => $sample->getName(),
                'sampleTitle' => $sample->getTitle($CMSCore->locale->getName()),
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
        }
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
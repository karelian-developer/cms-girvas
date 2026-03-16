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
use \core\PHPLibrary\SystemCore\File\EnumFormat as FileConverterEnumFileFormat;
use \core\PHPLibrary\SystemCore\File\Converter as FileConverter;
use \core\PHPLibrary\SystemCore\File\Resizer as FileResizer;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \Exception as Exception;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($CMSCore->urlp->getPath(2) === 'category') {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      if (isset($_PATCH['entries_category_id'])) {
        $entriesCategoryID = $_PATCH['entries_category_id'] ?? 0;
        $entriesCategoryID = is_numeric($entriesCategoryID) ? (int) $entriesCategoryID : 0;

        if (EntryCategory::existsByID($CMSCore, $entriesCategoryID)) {
          $entriesCategory = new EntryCategory($CMSCore, $entriesCategoryID);
          $entriesCategory->initData(['metadata']);
          $entriesCategoryData = [];

          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $CMSLocaleName) {
              $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleName);
              $CMSLocale->setTypeName('handler');
              $CMSLocale->initPathes();
              
              $CMSLocaleName = $CMSLocale->getName();

              $inputTitleName = 'entries_category_title_' . $CMSLocale->getISO639(2);
              $inputSEOTitleName = 'entries_category_seo_title_' . $CMSLocale->getISO639(2);
              $textareaDescriptionName = 'entries_category_description_' . $CMSLocale->getISO639(2);
              $textareaSEODescriptionName = 'entries_category_seo_description_' . $CMSLocale->getISO639(2);
              $textareaKeywordsName = 'entries_category_keywords_' . $CMSLocale->getISO639(2);

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

                if (array_key_exists($inputSEOTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputSEOTitleName];
                  $inputValue = strip_tags($inputValue);
                  $inputValue = str_replace('\'', '"', $inputValue);
                  $inputValue = htmlspecialchars($inputValue);
      
                  $entriesCategoryData['texts'][$CMSLocaleName]['SEOTitle'] = $inputValue;
                }
    
                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $textareaValue = htmlspecialchars($textareaValue);
      
                  $entriesCategoryData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                }
    
                if (array_key_exists($textareaSEODescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaSEODescriptionName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $textareaValue = htmlspecialchars($textareaValue);
      
                  $entriesCategoryData['texts'][$CMSLocaleName]['SEODescription'] = $textareaValue;
                }
    
                if (array_key_exists($textareaKeywordsName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaKeywordsName];
                  $textareaValue = strip_tags($textareaValue);
                  $textareaValue = str_replace('\'', '"', $textareaValue);
    
                  $entriesCategoryData['texts'][$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
                }
              }
            }
          }
          
          if (isset($_PATCH['entries_category_name'])) $entriesCategoryData['name'] = urlencode(htmlentities($_PATCH['entries_category_name']));
          if (isset($_PATCH['entries_category_parent_id'])) $entriesCategoryData['parentID'] = $_PATCH['entries_category_parent_id'];
          
          $entriesCategoryIsUpdated = $entriesCategory->update($entriesCategoryData);

          if ($entriesCategoryIsUpdated) {
            /** @var CMSReport Новый отчет */
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_EDITED, [
              'clientIP' => $CMSCore->client->getIPAddress(),
              'entriesCategoryID' => $entriesCategory->getID()
            ]);

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    }
  } else {
    $entryTitleLengthMax = 80;
    $entryDescriptionLengthMax = 600;

    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_PATCH['entry_id'])) {
        $entryID = $_PATCH['entry_id'] ?? 0;
        $entryID = is_numeric($entryID) ? (int) $entryID : 0;

        if (Entry::existsByID($CMSCore, $entryID)) {
          $entry = new Entry($CMSCore, $entryID);
          $entryData = [];

          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $CMSLocaleName) {
              $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleName);
              $CMSLocale->setTypeName('handler');
              $CMSLocale->initPathes();

              $CMSLocaleName = $CMSLocale->getName();

              $inputTitleName = 'entry_title_' . $CMSLocale->getISO639(2);
              $inputSEOTitleName = 'entry_seo_title_' . $CMSLocale->getISO639(2);
              $textareaDescriptionName = 'entry_description_' . $CMSLocale->getISO639(2);
              $textareaSEODescriptionName = 'entry_seo_description_' . $CMSLocale->getISO639(2);
              $textareaContentName = 'entry_content_' . $CMSLocale->getISO639(2);
              $textareaKeywordsName = 'entry_keywords_' . $CMSLocale->getISO639(2);

              if (!array_key_exists('metadata', $entryData)) $entryData['metadata'] = [];
              if (isset($_PATCH['entry_is_published'])) {
                $entryData['metadata']['publishedUnixTimestamp'] = time();
                $entryData['metadata']['isPublished'] = 1;
              }

              if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH) || array_key_exists($textareaContentName, $_PATCH)) {
                if (!array_key_exists('texts', $entryData)) $entryData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $entryData['texts'])) $entryData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($inputTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputTitleName];
                  $inputValue = str_replace('\'', '"', $inputValue);
      
                  $entryData['texts'][$CMSLocaleName]['title'] = $inputValue;
                }

                if (array_key_exists($inputSEOTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputSEOTitleName];
                  $inputValue = str_replace('\'', '"', $inputValue);
      
                  $entryData['texts'][$CMSLocaleName]['SEOTitle'] = $inputValue;
                }
    
                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $entryData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                }
    
                if (array_key_exists($textareaSEODescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaSEODescriptionName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $entryData['texts'][$CMSLocaleName]['SEODescription'] = $textareaValue;
                }
                
                if (array_key_exists($textareaContentName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaContentName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $entryData['texts'][$CMSLocaleName]['content'] = $textareaValue;
                }
    
                if (array_key_exists($textareaKeywordsName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaKeywordsName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
    
                  $entryData['texts'][$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
                }
              }
            }
          }

          if (isset($_PATCH['entry_name'])) $entryData['name'] = urlencode(htmlentities($_PATCH['entry_name']));
          if (isset($_PATCH['entry_category_id'])) $entryData['categoryID'] = $_PATCH['entry_category_id'];
          
          if (isset($_PATCH['entry_preview'])) {
            $fileExtension = pathinfo($_PATCH['entry_preview'], PATHINFO_EXTENSION);
            $fileExtension = strtolower($fileExtension);
            
            $extensionMap = [
              'jpg' => FileConverterEnumFileFormat::JPG,
              'jpeg' => FileConverterEnumFileFormat::JPG,
              'png' => FileConverterEnumFileFormat::PNG,
              'gif' => FileConverterEnumFileFormat::GIF,
              'bmp' => FileConverterEnumFileFormat::BMP,
              'webp' => FileConverterEnumFileFormat::WEBP,
              'avif' => FileConverterEnumFileFormat::AVIF,
            ];

            $fileExtensionEnum = $extensionMap[$fileExtension] ?? FileConverterEnumFileFormat::JPG;

            if ($CMSCore->configurator->getAutoConvertFileImageStatus(true)) {
              $fileExtensionConvertedEnum = match ($CMSCore->configurator->getAutoConvertFileImageExtension()) {
                'webp' => FileConverterEnumFileFormat::WEBP,
                'avif' => FileConverterEnumFileFormat::AVIF
              };
            } else {
              $fileExtensionConvertedEnum = $fileExtensionEnum;
            }

            $qualityPercent = 100 - $CMSCore->configurator->getUploadImageCompression();
            if ($qualityPercent <= 0) {
              $previewQuality = -1;
            } else {
              $previewQuality = min(9, max(0, (int) round(($qualityPercent / 100) * 9)));
            }

            $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
            $fileConverter = new FileConverter($CMSCore);
            $fileConverted = $fileConverter->convert($_PATCH['entry_preview'], $fileDirectoryPath, $fileExtensionConvertedEnum, true, 0, $previewQuality);
            
            if (is_array($fileConverted)) {
              if (!array_key_exists('metadata', $entryData)) $entryData['metadata'] = [];
              $entryData['metadata']['previewURL'] = '/uploads/media/' . $fileConverted['fileName'];

              $fileResizer = new FileResizer($CMSCore);

              try {
                $fileResizesDir = CMS_ROOT_DIRECTORY . '/uploads/media';

                $fileResizer->multipleResize(
                  $fileConverted['filePath'],
                  $fileResizesDir
                );
              } catch (Exception $exception) {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $exception->getMessage();
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
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

          if (isset($_PATCH['entry_published_timestamp'])) {
            $entryData['metadata']['publishedUnixTimestamp'] = strtotime(str_replace('T', ' ', $_PATCH['entry_published_timestamp']));
          }

          // Если происходит публикация записи, то необходимо удостовериться, что
          // в записи присутствует стандартная локализация, в противном случае
          // система не даст сохранить ее.
          if ($entryIsPublished) {
            $CMSBaseLocale = $CMSCore->getCMSLocale();
            $CMSBaseLocaleName = $CMSBaseLocale->getName();

            $entry->initData(['texts']);

            /** @var string Заголовок записи */
            $entryTitle = $entry->getTitle($CMSBaseLocaleName);
            /** @var string описание записи */
            $entryDescription = $entry->getDescription($CMSBaseLocaleName);
            /** @var string содержимое записи */
            $entryContent = $entry->getContent($CMSBaseLocaleName);
            /** @var int дата обновления страницы в формате UNIX */
            $entryData['metadata']['publishedUnixTimestamp'] = time();

            // Если заголовок, описание или содержимое стандартной локализации не задано, то
            // запись не будет обновлена.
            if (empty($entryTitle) || empty($entryDescription) || empty($entryContent)) {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_ENTRY_EMPTY_LOCALE_DEFAULT_PUBLISHED_ERROR'), $CMSBaseLocaleName);
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
            $entry->initData(['texts']);
            
            /** @var CMSReport Новый отчет */
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED, [
              'clientIP' => $CMSCore->client->getIPAddress(),
              'entryID' => $entry->getID()
            ]);

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
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
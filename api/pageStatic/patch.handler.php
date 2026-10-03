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

use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\PageStatic\Version as PageStaticVersion;
use \core\PHPLibrary\SystemCore\File\Converter as FileConverter;
use \core\PHPLibrary\SystemCore\File\EnumFormat as FileConverterEnumFileFormat;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    if (isset($_PATCH['page_static_id'])) {
      $pageStaticName = isset($_PATCH['page_static_name']) ? urlencode(htmlentities($_PATCH['page_static_name'])) : '';
      $pageStaticID = $_PATCH['page_static_id'] ?? 0;
      $pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

      if (PageStatic::existsByID($CMSCore, $pageStaticID)) {
                // ============================================================
        // СОБЫТИЕ: ВЫПУСК ВЕРСИИ ДОКУМЕНТА (152-ФЗ)
        // ============================================================
        if (($_PATCH['page_static_event'] ?? '') === 'publishVersion') {
          $pageStatic = new PageStatic($CMSCore, $pageStaticID);
          $pageStatic->initData(['id', 'name', 'texts', 'metadata']);

          if ($pageStatic->isLegalDocument()) {
            $CMSBaseLocaleName = $CMSCore->getCMSLocale()->getName();
            $versionInput = trim((string)($_PATCH['page_static_version'] ?? ''));

            if (empty($versionInput)) {
              $versionInput = PageStaticVersion::getDefaultVersion($CMSCore, $pageStaticID, $CMSBaseLocaleName);
            } else {
              $versionInput = PageStaticVersion::getNextVersion($CMSCore, $pageStaticID, $versionInput, $CMSBaseLocaleName);
            }

            // Пересоздаём объект — чтобы texts были актуальны
            $pageStatic = new PageStatic($CMSCore, $pageStaticID);
            $pageStatic->initData(['id', 'name', 'texts', 'metadata']);

            $versions = $pageStatic->publishVersionBatch($versionInput, $clientUser->getID());

            $publishedVersions = [];
            foreach ($versions as $locale => $version) {
              $publishedVersions[$locale] = $versionInput;
            }

            if (!empty($publishedVersions)) {
              $pageTitles = [];
              foreach ($CMSCore->getArrayLocalesNames() as $locale) {
                $pageTitles[$locale] = $pageStatic->getTitle($locale);
              }

              CMSReport::create(
                $CMSCore,
                CMSReport::REPORT_TYPE_ID_AP_DOCUMENT_VERSION_PUBLISHED,
                [
                  'pageStaticID' => $pageStaticID,
                  'pageName' => $pageStatic->getName(),
                  'pageTitles' => $pageTitles,
                  'versions' => $publishedVersions,
                  'userID' => $clientUser->getID(),
                  'userLogin' => $clientUser->getLogin(),
                  'ip' => $CMSCore->client->getIPAddress()
                ]
              );

              $handlerOutputData['version'] = $versionInput;
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: Страница не является юридическим документом.';
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }

          // Прерываем дальнейшую обработку
          return;
        }

        $pageStatic = new PageStatic($CMSCore, $pageStaticID);
        $pageStatic->initData(['name', 'texts', 'metadata']);
        
        // Сохраняем старые значения для сравнения
        $oldValues = [
          'name' => $pageStatic->getName(),
          'title' => $pageStatic->getTitle($CMSCore->locale->getName()),
          'description' => $pageStatic->getDescription($CMSCore->locale->getName()),
          'content' => $pageStatic->getContent($CMSCore->locale->getName()),
          'isPublished' => $pageStatic->isPublished(),
        ];
        
        $pageStaticData = [];
        $changedFields = [];

        if (!PageStatic::existsByName($CMSCore, $pageStaticName) || $pageStaticName === $pageStatic->getName()) {
          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $localeName) {
              $CMSLocale = new CMSLocale($CMSCore, $localeName);
              $CMSLocale->setTypeName('handler');
              $CMSLocale->initPathes();

              $CMSLocaleName = $CMSLocale->getName();

              $inputTitleName = 'page_static_title_' . $CMSLocale->getISO639(2);
              $inputSEOTitleName = 'page_static_seo_title_' . $CMSLocale->getISO639(2);
              $textareaDescriptionName = 'page_static_description_' . $CMSLocale->getISO639(2);
              $textareaSEODescriptionName = 'page_static_seo_description_' . $CMSLocale->getISO639(2);
              $textareaContentName = 'page_static_content_' . $CMSLocale->getISO639(2);
              $textareaKeywordsName = 'page_static_keywords_' . $CMSLocale->getISO639(2);

              if (!array_key_exists('metadata', $pageStaticData)) $pageStaticData['metadata'] = [];
              if (isset($_PATCH['page_static_is_published'])) {
                $pageStaticData['metadata']['publishedUnixTimestamp'] = time();
                $pageStaticData['metadata']['isPublished'] = 1;
                if ($oldValues['isPublished'] != 1) $changedFields[] = 'is_published';
              }

              if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH) || array_key_exists($textareaContentName, $_PATCH)) {
                if (!array_key_exists('texts', $pageStaticData)) $pageStaticData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $pageStaticData['texts'])) $pageStaticData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($inputTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputTitleName];
                  $inputValue = str_replace('\'', '"', $inputValue);
                  $pageStaticData['texts'][$CMSLocaleName]['title'] = $inputValue;
                  if ($oldValues['title'] !== $inputValue) $changedFields[] = 'title';
                }

                if (array_key_exists($inputSEOTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputSEOTitleName];
                  $inputValue = str_replace('\'', '"', $inputValue);
                  $pageStaticData['texts'][$CMSLocaleName]['SEOTitle'] = $inputValue;
                  $changedFields[] = 'seo_title';
                }

                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $pageStaticData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                  if ($oldValues['description'] !== $textareaValue) $changedFields[] = 'description';
                }
      
                if (array_key_exists($textareaSEODescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaSEODescriptionName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $pageStaticData['texts'][$CMSLocaleName]['SEODescription'] = $textareaValue;
                  $changedFields[] = 'seo_description';
                }

                if (array_key_exists($textareaContentName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaContentName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $pageStaticData['texts'][$CMSLocaleName]['content'] = $textareaValue;
                  if ($oldValues['content'] !== $textareaValue) $changedFields[] = 'content';
                }

                if (array_key_exists($textareaKeywordsName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaKeywordsName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
                  $pageStaticData['texts'][$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
                  $changedFields[] = 'keywords';
                }
              }
            }
          }

          if (isset($_PATCH['page_static_name'])) {
            $pageStaticData['name'] = urlencode(htmlentities($_PATCH['page_static_name']));
            if ($oldValues['name'] !== $pageStaticData['name']) $changedFields[] = 'name';
          }
          
          if (isset($_PATCH['page_static_preview'])) {
            $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
            $fileConverter = new FileConverter($CMSCore);
            $fileConverted = $fileConverter->convert($_PATCH['page_static_preview'], $fileDirectoryPath, FileConverterEnumFileFormat::WEBP, true);
            
            if (is_array($fileConverted)) {
              if (!array_key_exists('metadata', $pageStaticData)) $pageStaticData['metadata'] = [];
              $pageStaticData['metadata']['previewURL'] = '/uploads/media/' . $fileConverted['fileName'];
              $changedFields[] = 'preview';
            }
          }

          foreach ($_PATCH as $name => $value) {
            if (preg_match('/^page_static_additional_field_([a-z0-9_]+)$/', $name, $matches, PREG_OFFSET_CAPTURE)) {
              if (!isset($pageStaticData['metadata']['additionalFields'])) $pageStaticData['metadata']['additionalFields'] = [];
              
              $fieldName = $matches[1][0];
              $fieldNameTransformed = '';

              $fieldNameParts = explode('_', $fieldName);
              for ($i = 0; $i < count($fieldNameParts); $i++) {
                $fieldNameTransformed .= $i > 0 ? ucfirst($fieldNameParts[$i]) : $fieldNameParts[$i];
              }

              $pageStaticData['metadata']['additionalFields'][$fieldNameTransformed] = htmlspecialchars(str_replace('\'', '"', $value));
              $changedFields[] = 'additional_field_' . $fieldNameTransformed;
            }

            if ($name === 'page_static_template_path') {
              $pageStaticData['metadata']['personalTemplatePath'] = htmlspecialchars(str_replace('\'', '"', trim($value)));
              $changedFields[] = 'template_path';
            }
          }

          $pageStaticIsPublished = $pageStaticData['metadata']['isPublished'] ?? 0;

          if (isset($_PATCH['page_static_published_timestamp'])) {
            $pageStaticData['metadata']['publishedUnixTimestamp'] = strtotime(str_replace('T', ' ', $_PATCH['page_static_published_timestamp']));
            $changedFields[] = 'published_timestamp';
          }

          // ============================================================
          // ФЛАГ ЮРИДИЧЕСКОГО ДОКУМЕНТА (152-ФЗ)
          // ============================================================
          $pageStaticIsLegalDocument = ($_PATCH['page_static_is_legal_document_status'] ?? 'off') === 'on';
          
          if (!array_key_exists('metadata', $pageStaticData)) $pageStaticData['metadata'] = [];
          $pageStaticData['metadata']['isLegalDocument'] = $pageStaticIsLegalDocument;

          // Если происходит публикация страницы, то необходимо удостовериться, что
          // в странице присутствует стандартная локализация
          if ($pageStaticIsPublished) {
            $CMSBaseLocale = $CMSCore->getCMSLocale();
            $CMSBaseLocaleName = $CMSBaseLocale->getName();

            $pageStatic->initData(['texts', 'metadata']);

            $pageStaticTitle = $pageStatic->getTitle($CMSBaseLocaleName);
            $pageStaticDescription = $pageStatic->getDescription($CMSBaseLocaleName);
            $pageStaticContent = $pageStatic->getContent($CMSBaseLocaleName);
            $pageStaticData['metadata']['publishedUnixTimestamp'] = time();

            if (empty($pageStaticTitle) || empty($pageStaticDescription) || empty($pageStaticContent)) {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_PAGE_STATIC_EMPTY_LOCALE_DEFAULT_PUBLISHED_ERROR'), $CMSBaseLocaleName);
              $handlerStatusCode = $handlerStatusCode ?? 0;
            } else {
              $pageStaticIsUpdated = $pageStatic->update($pageStaticData);
            }
          } else {
            $pageStaticIsUpdated = $pageStatic->update($pageStaticData);
          }

          $pageStaticIsUpdated = $pageStaticIsUpdated ?? false;

          if ($pageStaticIsUpdated) {
            // ============================================================
            // ЛОГИРОВАНИЕ ОБНОВЛЕНИЯ СТАТИЧЕСКОЙ СТРАНИЦЫ (152-ФЗ)
            // ============================================================
            $pageStatic->initData(['name', 'texts']);
            $pageTitle = $pageStatic->getTitle($CMSCore->locale->getName());
            
            CMSReport::create(
              $CMSCore,
              CMSReport::REPORT_TYPE_ID_AP_PAGE_EDITED,
              [
                'pageID' => $pageStaticID,
                'pageName' => $pageStatic->getName(),
                'pageTitle' => $pageTitle,
                'updatedByID' => $clientUser->getID(),
                'updatedByLogin' => $clientUser->getLogin(),
                'changedFields' => $changedFields,
                'ip' => $CMSCore->client->getIPAddress()
              ]
            );

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            if (empty($handlerMessage)) {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_PAGE_STATIC_NAME_ALREADY_EXISTS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_STATIC_PAGE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
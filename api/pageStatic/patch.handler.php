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
use \core\PHPLibrary\SystemCore\FileConverter as FileConverter;
use \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat as FileConverterEnumFileFormat;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    if (isset($_PATCH['page_static_id'])) {
      $pageStaticID = $_PATCH['page_static_id'] ?? 0;
      $pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

      if (PageStatic::existsByID($CMSCore, $pageStaticID)) {
        $pageStatic = new PageStatic($CMSCore, $pageStaticID);
        $pageStaticData = [];

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
            if (isset($_PATCH['page_static_is_published'])) $pageStaticData['metadata']['isPublished'] = $_PATCH['page_static_is_published'];

            if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH) || array_key_exists($textareaContentName, $_PATCH)) {
              if (!array_key_exists('texts', $pageStaticData)) $pageStaticData['texts'] = [];
              if (!array_key_exists($CMSLocaleName, $pageStaticData['texts'])) $pageStaticData['texts'][$CMSLocaleName] = [];

              if (array_key_exists($inputTitleName, $_PATCH)) {
                $inputValue = $_PATCH[$inputTitleName];
                $inputValue = strip_tags($inputValue);
                $inputValue = str_replace('\'', '"', $inputValue);
                
                $pageStaticData['texts'][$CMSLocaleName]['title'] = $inputValue;
              }

              if (array_key_exists($inputSEOTitleName, $_PATCH)) {
                $inputValue = $_PATCH[$inputSEOTitleName];
                $inputValue = strip_tags($inputValue);
                $inputValue = str_replace('\'', '"', $inputValue);
    
                $entryData['texts'][$CMSLocaleName]['SEOTitle'] = $inputValue;
              }

              if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                $textareaValue = $_PATCH[$textareaDescriptionName];
                $textareaValue = strip_tags($textareaValue);
                $textareaValue = str_replace('\'', '"', $textareaValue);

                $pageStaticData['texts'][$CMSLocaleName]['description'] = $textareaValue;
              }
    
              if (array_key_exists($textareaSEODescriptionName, $_PATCH)) {
                $textareaValue = $_PATCH[$textareaSEODescriptionName];
                $textareaValue = strip_tags($textareaValue);
                $textareaValue = str_replace('\'', '"', $textareaValue);
    
                $entryData['texts'][$CMSLocaleName]['SEODescription'] = $textareaValue;
              }

              if (array_key_exists($textareaContentName, $_PATCH)) {
                $textareaValue = $_PATCH[$textareaContentName];
                $textareaValue = strip_tags($textareaValue, '<table><tr><td><th><b><u><i><hr>');
                $textareaValue = str_replace('\'', '"', $textareaValue);

                $pageStaticData['texts'][$CMSLocaleName]['content'] = $textareaValue;
              }

              if (array_key_exists($textareaKeywordsName, $_PATCH)) {
                $textareaValue = $_PATCH[$textareaKeywordsName];
                $textareaValue = strip_tags($textareaValue);
                $textareaValue = str_replace('\'', '"', $textareaValue);
                
                $pageStaticData['texts'][$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
              }
            }
          }
        }

        if (isset($_PATCH['page_static_name'])) $pageStaticData['name'] = urlencode(htmlentities($_PATCH['page_static_name']));
        if (isset($_PATCH['page_static_preview'])) {
          $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
          $fileConverter = new FileConverter($CMSCore);
          $fileConverted = $fileConverter->convert($_PATCH['page_static_preview'], $fileDirectoryPath, FileConverterEnumFileFormat::WEBP, true);
          
          if (is_array($fileConverted)) {
            if (!array_key_exists('metadata', $pageStaticData)) $pageStaticData['metadata'] = [];
            $pageStaticData['metadata']['previewURL'] = '/uploads/media/' . $fileConverted['fileName'];
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
          }

          if ($name === 'page_static_template_path') {
            $pageStaticData['metadata']['personalTemplatePath'] = htmlspecialchars(str_replace('\'', '"', trim($value)));
          }
        }

        $pageStaticIsPublished = $pageStaticData['metadata']['isPublished'] ?? 0;

        // Если происходит публикация страницы, то необходимо удостовериться, что
        // в странице присутствует стандартная локализация, в противном случае
        // система не даст сохранить ее.
        if ($pageStaticIsPublished) {
          $CMSBaseLocale = $CMSCore->getCMSLocale();
          $CMSBaseLocaleName = $CMSBaseLocale->getName();

          $pageStatic->initData(['texts', 'metadata']);

          /** @var string Заголовок записи */
          $pageStaticTitle = $pageStatic->getTitle($CMSBaseLocaleName);
          /** @var string описание записи */
          $pageStaticDescription = $pageStatic->getDescription($CMSBaseLocaleName);
          /** @var string содержимое записи */
          $pageStaticContent = $pageStatic->getContent($CMSBaseLocaleName);
          /** @var int дата обновления страницы в формате UNIX */
          $pageStaticData['metadata']['publishedUnixTimestamp'] = time();

          // Если заголовок, описание или содержимое стандартной локализации не задано, то
          // запись не будет обновлена.
          if (empty($pageStaticTitle) || empty($pageStaticDescription) || empty($pageStaticContent)) {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_PAGE_STATIC_EMPTY_LOCALE_DEFAULT_PUBLISHED_ERROR'), $CMSBaseLocaleName);
            $handlerStatusCode = $handlerStatusCode ?? 0;
          } else {
            /** @var bool Обновление записи */
            $pageStaticIsUpdated = $pageStatic->update($pageStaticData);
          }
        } else {
          /** @var bool Обновление записи */
          $pageStaticIsUpdated = $pageStatic->update($pageStaticData);
        }

        /** @var bool Костыль */
        $pageStaticIsUpdated = $pageStaticIsUpdated ?? false;

        if ($pageStaticIsUpdated) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_STATIC_PAGE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
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
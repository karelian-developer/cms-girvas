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

use \core\PHPLibrary\ContentBlock as ContentBlock;
use \core\PHPLibrary\Factories\Content as CMSContent;
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

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT)) {
    if (isset($_PATCH['content_block_id'])) {
      $contentBlockName = isset($_PATCH['content_block_name']) ? urlencode(htmlentities($_PATCH['content_block_name'])) : '';
      $contentBlockID = $_PATCH['entry_id'] ?? 0;
      $contentBlockID = is_numeric($contentBlockID) ? (int) $contentBlockID : 0;

      if (ContentBlock::existsByID($CMSCore, $contentBlockID)) {
        $contentBlock = CMSContent::create($CMSCore, 'contentBlock', ['id' => $contentBlockID]);
        $contentBlock->initData(['name']);
        $contentBlockData = [];

        if (
          !ContentBlock::existsByName($CMSCore, $contentBlockName) ||
          $contentBlockName === $contentBlock->getName()
        ) {
          $CMSLocalesNames = $CMSCore->getArrayLocalesNames();

          if (count($CMSLocalesNames) > 0) {
            foreach ($CMSLocalesNames as $index => $CMSLocaleName) {
              $CMSLocale = new CMSLocale($CMSCore, $CMSLocaleName);
              $CMSLocale->setTypeName('handler');
              $CMSLocale->initPathes();

              $CMSLocaleName = $CMSLocale->getName();

              $inputTitleName = 'content_block_title_' . $CMSLocale->getISO639(2);
              $textareaDescriptionName = 'content_block_description_' . $CMSLocale->getISO639(2);
              $textareaContentName = 'content_block_content_' . $CMSLocale->getISO639(2);

              if (!array_key_exists('metadata', $contentBlockData)) $contentBlockData['metadata'] = [];

              if (
                array_key_exists($inputTitleName, $_PATCH) ||
                array_key_exists($textareaDescriptionName, $_PATCH) ||
                array_key_exists($textareaContentName, $_PATCH)
              ) {
                if (!array_key_exists('texts', $contentBlockData)) $contentBlockData['texts'] = [];
                if (!array_key_exists($CMSLocaleName, $contentBlockData['texts'])) $contentBlockData['texts'][$CMSLocaleName] = [];

                if (array_key_exists($inputTitleName, $_PATCH)) {
                  $inputValue = $_PATCH[$inputTitleName];
                  $inputValue = str_replace('\'', '"', $inputValue);
      
                  $contentBlockData['texts'][$CMSLocaleName]['title'] = $inputValue;
                }
    
                if (array_key_exists($textareaDescriptionName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaDescriptionName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $contentBlockData['texts'][$CMSLocaleName]['description'] = $textareaValue;
                }
                
                if (array_key_exists($textareaContentName, $_PATCH)) {
                  $textareaValue = $_PATCH[$textareaContentName];
                  $textareaValue = str_replace('\'', '"', $textareaValue);
      
                  $contentBlockData['texts'][$CMSLocaleName]['content'] = $textareaValue;
                }
              }
            }
          }

          if (isset($_PATCH['content_block_name'])) $contentBlockData['name'] = urlencode(htmlentities($_PATCH['content_block_name']));
          if (isset($_PATCH['content_block_type_id'])) $contentBlockData['typeID'] = $_PATCH['content_block_type_id'];

          $isUpdated = $contentBlock->update($contentBlockData);
          $isUpdated = isset($isUpdated) ? $isUpdated : false;

          if ($isUpdated) {
            // Инициализация данных с текстом записи
            $contentBlock->initData(['texts']);
            
            /** @var CMSReport Новый отчет */
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED, [
              'clientIP' => $CMSCore->client->getIPAddress(),
              'contentBlockID' => $entcontentBlockry->getID()
            ]);

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONTENT_BLOCK_NAME_ALREADY_EXISTS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONTENT_BLOCK_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
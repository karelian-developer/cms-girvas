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
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($CMSCore->urlp->getPath(2) === null) {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT)) {
      $contentBlockName = isset($_PUT['content_block_name']) ? urlencode(htmlentities($_PUT['content_block_name'])) : '';

      if (!ContentBlock::existsByName($CMSCore, $contentBlockName)) {
        $contentBlockTypeID = $_PUT['content_block_type_id'] ?? 1;
        $contentBlockTypeID = is_numeric($contentBlockTypeID) ? (int) $contentBlockTypeID : 0;
        $texts = [];

        $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
        if (count($CMSLocalesNames) > 0) {
          foreach ($CMSLocalesNames as $index => $localeName) {
            $CMSLocale = new CMSLocale($CMSCore, $localeName);
            $CMSLocale->setTypeName('handler');
            $CMSLocale->initPathes();

            $CMSLocaleName = $CMSLocale->getName();

            $inputTitleName = 'content_block_title_' . $CMSLocale->getISO639(2);
            $textareaDescriptionName = 'content_block_description_' . $CMSLocale->getISO639(2);
            $textareaContentName = 'content_block_content_' . $CMSLocale->getISO639(2);

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
            }
          }
        }

        $clientSession = $CMSCore->client->getSession(2, ['userID']);
        $contentBlock = ContentBlock::create($CMSCore, $contentBlockName, $texts);
        if ($contentBlock !== null) {
          $contentBlock->initData(['texts']);

          // Обновление дополнительной информации
          $contentBlockData['typeID'] = $contentBlockTypeID;
          $contentBlock->update($contentBlockData);

          $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED, [
            'clientIP' => $CMSCore->client->getIPAddress(),
            'contentBlockID' => $contentBlock->getID()
          ]);
          
          $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;

          $handlerOutputData['contentBlock'] = [];
          $handlerOutputData['contentBlock']['id'] = $contentBlock->getID();

          $handlerOutputData['href'] = '/admin/contentBlock/' . $contentBlock->getID();
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONTENT_BLOCK_NAME_ALREADY_EXISTS');
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
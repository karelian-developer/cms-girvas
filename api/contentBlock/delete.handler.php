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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT)) {
    if (isset($_DELETE['content_block_id'])) {
      $contentBlockID = is_numeric($_DELETE['content_block_id']) ? (int) $_DELETE['content_block_id'] : 0;

      if (ContentBlock::existsByID($CMSCore, $contentBlockID)) {
        $contentBlock = CMSContent::create($CMSCore, 'contentBlock', ['id' => $contentBlockID]);

        $contentBlock->initData(['texts']);
        $contentBlockTitle = $contentBlock->getTitle();

        $isDeleted = $contentBlock->delete();

        if ($isDeleted) {
          /** @var CMSReport Новый отчет */
          $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED, [
            'clientIP' => $CMSCore->client->getIPAddress(),
            'contentBlockID' => $contentBlockID
          ]);

          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONTENT_BLOCK_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }

      $handler_output_data['modalClose'] = true;
      $handler_output_data['reload'] = true;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
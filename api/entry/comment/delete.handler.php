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

use \core\PHPLibrary\EntryComment as EntryComment;

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(1);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if (isset($_DELETE['comment_id'])) {
    $commentID = $_DELETE['comment_id'] ?? 0;
    $commentID = is_numeric($commentID) ? (int) $commentID : 0;

    if (EntryComment::existsByID($CMSCore, $commentID)) {
      $comment = new EntryComment($CMSCore, $commentID);
      $comment->initData(['authorID']);

      if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT) || $clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE) && $comment->getAuthorID() == $clientUser->getID()) {
        $commentIsDeleted = $comment->delete();

        if ($commentIsDeleted) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }

    $handlerOutputData['modalClose'] = true;
    $handlerOutputData['reload'] = true;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
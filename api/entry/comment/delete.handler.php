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

if ($CMSCore->client->is_logged(1) || $CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(1);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if (isset($_DELETE['comment_id'])) {
    $commentID = $_DELETE['comment_id'] ?? 0;
    $commentID = is_numeric($commentID) ? (int) $commentID : 0;

    if (EntryComment::exists_by_id($CMSCore, $commentID)) {
      $comment = new EntryComment($CMSCore, $commentID);
      $comment->init_data(['authorID']);

      if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT) || $clientUserGroup->permission_check($clientUserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE) && $comment->get_author_id() == $clientUser->get_id()) {
        $commentIsDeleted = $comment->delete();

        if ($commentIsDeleted) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_DELETE_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }

    $handlerOutputData['modalClose'] = true;
    $handlerOutputData['reload'] = true;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
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
use \core\PHPLibrary\EntryComments as EntryComments;

if ($CMSCore->client->is_logged(1) || $CMSCore->client->is_logged(2)) {
  $commentID = $CMSCore->urlp->get_path(3) ?? 0;
  $commentID = is_numeric($commentID) ? (int) $commentID : 0;

  if (EntryComment::exists_by_id($CMSCore, $commentID)) {
    $comment = new EntryComment($CMSCore, $commentID);
    $comment->init_data(['entryID', 'authorID', 'content', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    
    $handlerOutputData['comment'] = [];
    $handlerOutputData['comment']['id'] = $comment->get_id();
    $handlerOutputData['comment']['content'] = $comment->get_content();
    $handlerOutputData['comment']['authorID'] = $comment->get_author_id();
    $handlerOutputData['comment']['parentID'] = $comment->get_parent_id();
    $handlerOutputData['comment']['answersCount'] = $comment->get_answers_count();
    $handlerOutputData['comment']['isHidden'] = $comment->is_hidden();
    $handlerOutputData['comment']['hiddenReason'] = $comment->get_hidden_reason();
    $handlerOutputData['comment']['rating'] = $comment->get_rating();
    $handlerOutputData['comment']['ratingVoters'] = $comment->get_rating_voters();
    $handlerOutputData['comment']['createdUnixTimestamp'] = $comment->get_created_unix_timestamp();
    $handlerOutputData['comment']['updatedUnixTimestamp'] = $comment->get_updated_unix_timestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
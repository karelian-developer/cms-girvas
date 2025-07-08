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

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  $commentID = $CMSCore->urlp->getPath(3) ?? 0;
  $commentID = is_numeric($commentID) ? (int) $commentID : 0;

  if (EntryComment::existsByID($CMSCore, $commentID)) {
    $comment = new EntryComment($CMSCore, $commentID);
    $comment->initData(['entryID', 'authorID', 'content', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    
    $handlerOutputData['comment'] = [];
    $handlerOutputData['comment']['id'] = $comment->getID();
    $handlerOutputData['comment']['content'] = $comment->getContent();
    $handlerOutputData['comment']['authorID'] = $comment->getAuthorID();
    $handlerOutputData['comment']['parentID'] = $comment->getParentID();
    $handlerOutputData['comment']['answersCount'] = $comment->getAnswersCount();
    $handlerOutputData['comment']['isHidden'] = $comment->isHidden();
    $handlerOutputData['comment']['hiddenReason'] = $comment->getHiddenReason();
    $handlerOutputData['comment']['rating'] = $comment->getRating();
    $handlerOutputData['comment']['ratingVoters'] = $comment->getRatingVoters();
    $handlerOutputData['comment']['createdUnixTimestamp'] = $comment->getCreatedUnixTimestamp();
    $handlerOutputData['comment']['updatedUnixTimestamp'] = $comment->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
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
use \core\PHPLibrary\EntriesCategories as EntriesCategories;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Parsedown as Parsedown;

if (is_numeric($CMSCore->urlp->get_path(2))) {
  if ($CMSCore->urlp->get_path(3) === 'comments') {
    $entryID = $CMSCore->urlp->get_path(2) ?? 0;
    $entryID = is_numeric($entryID) ? (int) $entryID : 0;
    $entry = new Entry($CMSCore, $entryID);
    
    if (Entry::exists_by_id($CMSCore, $entryID)) {
      $commentsParamsData = [];

      if (isset($_GET['limit'])) {
        $limit = is_numeric($_GET['limit']) ? (int) $_GET['limit'] : 0;
        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int) $_GET['offset'] : 0;

        $commentsParamsData['limit'] = [$limit, $offset];
      }

      if (isset($_GET['sortColumn']) && isset($_GET['sortType'])) {
        $commentsParamsData['order_by'] = [
          'column' => $_GET['sortColumn'],
          'sort' => $_GET['sortType']
        ];
      }

      if (isset($_GET['parentID'])) {
        if (is_numeric($_GET['parentID'])) {
          $commentsParamsData['parentID'] = (int) $_GET['parentID'];
        }
      }

      if (!isset($commentsParamsData['parentID'])) {
        $commentsParamsData['parentID'] = 0;
      }
      
      $entryComments = $entry->get_comments($commentsParamsData);

      $handlerOutputData['comments'] = [];
      foreach ($entryComments as $comment) {
        $comment->init_data(['entryID', 'authorID', 'content', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

        $commentData = [];
        $commentData['id'] = $comment->get_id();
        $commentData['content'] = $comment->get_content();
        $commentData['answersCount'] = $comment->get_answers_count();
        $commentData['authorID'] = $comment->get_author_id();
        $commentData['isHidden'] = $comment->is_hidden();
        $commentData['hiddenReason'] = $comment->get_hidden_reason();
        $commentData['rating'] = $comment->get_rating();
        $commentData['ratingVoters'] = $comment->get_rating_voters();
        $commentData['createdUnixTimestamp'] = $comment->get_created_unix_timestamp();
        $commentData['updatedUnixTimestamp'] = $comment->get_updated_unix_timestamp();

        $handlerOutputData['comments'][] = $commentData;
      }

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $entryID = $CMSCore->urlp->get_path(2) ?? 0;
    $entryID = is_numeric($entryID) ? (int) $entryID : 0;

    if (Entry::exists_by_id($CMSCore, $entryID)) {
      $entry = new Entry($CMSCore, $entryID);
      $entry->init_data(['name', 'authorID', 'categoryID', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $entryLocale = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

      $parsedown = new Parsedown();
      $parsedown->setSafeMode(true);
      $parsedown->setMarkupEscaped(true);

      $handlerOutputData['entry'] = [];
      $handlerOutputData['entry']['id'] = $entry->get_id();
      $handlerOutputData['entry']['name'] = $entry->get_name();
      $handlerOutputData['entry']['title'] = $entry->get_title($entryLocale);
      $handlerOutputData['entry']['description'] = $entry->get_description($entryLocale);
      $handlerOutputData['entry']['content'] = $entry->get_content($entryLocale);
      $handlerOutputData['entry']['keywords'] = $entry->get_keywords($entryLocale);
      $handlerOutputData['entry']['authorID'] = $entry->get_author_id();
      $handlerOutputData['entry']['categoryID'] = $entry->get_category_id();
      $handlerOutputData['entry']['previewURL'] = $entry->get_preview_url();
      $handlerOutputData['entry']['isPublished'] = $entry->is_published();
      $handlerOutputData['entry']['createdUnixTimestamp'] = $entry->get_created_unix_timestamp();
      $handlerOutputData['entry']['updatedUnixTimestamp'] = $entry->get_updated_unix_timestamp();

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else if ($CMSCore->urlp->get_path(2) === 'category') {
  $entriesCategoryID = $CMSCore->urlp->get_path(3) ?? 0;
  $entriesCategoryID = is_numeric($entriesCategoryID) ? (int) $entriesCategoryID : 0;

  $entriesCategory = EntryCategory::exists_by_id($CMSCore, $entriesCategoryID) ? new EntryCategory($CMSCore, $entriesCategoryID) : null;

  if (!is_null($entriesCategory)) {
    $entriesCategory->init_data(['id', 'texts', 'metadata', 'name', 'parentID', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $entriesCategoryLocale = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

    $handlerOutputData['entriesCategory'] = [];
    $handlerOutputData['entriesCategory']['id'] = $entriesCategory->get_id();
    $handlerOutputData['entriesCategory']['name'] = $entriesCategory->get_name();
    $handlerOutputData['entriesCategory']['title'] = $entriesCategory->get_title($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['description'] = $entriesCategory->get_description($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['parentID'] = $entriesCategory->get_parent_id();
    $handlerOutputData['entriesCategory']['createdUnixTimestamp'] = $entriesCategory->get_created_unix_timestamp();
    $handlerOutputData['entriesCategory']['updatedUnixTimestamp'] = $entriesCategory->get_updated_unix_timestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else if ($CMSCore->urlp->get_path(2) === 'categories') {
  $entriesCategories = (new EntriesCategories($CMSCore))->get_all();
  $entriesCategoriesLocale = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

  $handlerOutputData['entriesCategories'] = [];
  if (count($entriesCategories) > 0) {
    foreach ($entriesCategories as $category) {
      $category->init_data(['id', 'texts', 'metadata', 'name', 'parentID', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      
      array_push($handlerOutputData['entriesCategories'], [
        'id' => $category->get_id(),
        'name' => $category->get_name(),
        'title' => $category->get_title($entriesCategoriesLocale),
        'description' => $category->get_description($entriesCategoriesLocale),
        'parentID' => $category->get_parent_id(),
        'createdUnixTimestamp' => $category->get_created_unix_timestamp(),
        'updatedUnixTimestamp' => $category->get_updated_unix_timestamp()
      ]);
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

?>
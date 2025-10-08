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
use \core\PHPLibrary\NadvoParse as NadvoParse;

if (is_numeric($CMSCore->urlp->getPath(2))) {
  if ($CMSCore->urlp->getPath(3) === 'comments') {
    $entryID = $CMSCore->urlp->getPath(2) ?? 0;
    $entryID = is_numeric($entryID) ? (int) $entryID : 0;
    $entry = new Entry($CMSCore, $entryID);
    
    if (Entry::existsByID($CMSCore, $entryID)) {
      $commentsParamsData = [];

      if (isset($_GET['limit'])) {
        $limit = is_numeric($_GET['limit']) ? (int) $_GET['limit'] : 0;
        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int) $_GET['offset'] : 0;

        $commentsParamsData['limit'] = [$limit, $offset];
      }

      if (isset($_GET['sortColumn']) && isset($_GET['sortType'])) {
        $commentsParamsData['orderBy'] = [
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
      
      $entryComments = $entry->getComments($commentsParamsData);

      $handlerOutputData['comments'] = [];
      foreach ($entryComments as $comment) {
        $comment->initData(['entryID', 'authorID', 'content', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

        $commentData = [];
        $commentData['id'] = $comment->getID();
        $commentData['content'] = $comment->getContent();
        $commentData['answersCount'] = $comment->getAnswersCount();
        $commentData['authorID'] = $comment->getAuthorID();
        $commentData['isHidden'] = $comment->isHidden();
        $commentData['hiddenReason'] = $comment->getHiddenReason();
        $commentData['rating'] = $comment->getRating();
        $commentData['ratingVoters'] = $comment->getRatingVoters();
        $commentData['createdUnixTimestamp'] = $comment->getCreatedUnixTimestamp();
        $commentData['updatedUnixTimestamp'] = $comment->getUpdatedUnixTimestamp();

        $handlerOutputData['comments'][] = $commentData;
      }

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $entryID = $CMSCore->urlp->getPath(2) ?? 0;
    $entryID = is_numeric($entryID) ? (int) $entryID : 0;

    if (Entry::existsByID($CMSCore, $entryID)) {
      $entry = new Entry($CMSCore, $entryID);
      $entry->initData(['name', 'authorID', 'categoryID', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $entryLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

      $handlerOutputData['entry'] = [];
      $handlerOutputData['entry']['id'] = $entry->getID();
      $handlerOutputData['entry']['name'] = $entry->getName();
      $handlerOutputData['entry']['title'] = $entry->getTitle($entryLocale);
      $handlerOutputData['entry']['SEOTitle'] = $entry->getSEOTitle($entryLocale);
      $handlerOutputData['entry']['description'] = $entry->getDescription($entryLocale);
      $handlerOutputData['entry']['SEODescription'] = $entry->getSEODescription($entryLocale);
      $handlerOutputData['entry']['content'] = $entry->getContent($entryLocale);
      $handlerOutputData['entry']['keywords'] = $entry->getKeywords($entryLocale);
      $handlerOutputData['entry']['authorID'] = $entry->getAuthorID();
      $handlerOutputData['entry']['categoryID'] = $entry->getCategoryID();
      $handlerOutputData['entry']['previewURL'] = $entry->getPreviewURL();
      $handlerOutputData['entry']['isPublished'] = $entry->isPublished();
      $handlerOutputData['entry']['createdUnixTimestamp'] = $entry->getCreatedUnixTimestamp();
      $handlerOutputData['entry']['updatedUnixTimestamp'] = $entry->getUpdatedUnixTimestamp();

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else if ($CMSCore->urlp->getPath(2) === 'category') {
  $entriesCategoryID = $CMSCore->urlp->getPath(3) ?? 0;
  $entriesCategoryID = is_numeric($entriesCategoryID) ? (int) $entriesCategoryID : 0;

  $entriesCategory = EntryCategory::existsByID($CMSCore, $entriesCategoryID) ? new EntryCategory($CMSCore, $entriesCategoryID) : null;

  if (!is_null($entriesCategory)) {
    $entriesCategory->initData(['id', 'texts', 'metadata', 'name', 'parentID', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $entriesCategoryLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['entriesCategory'] = [];
    $handlerOutputData['entriesCategory']['id'] = $entriesCategory->getID();
    $handlerOutputData['entriesCategory']['name'] = $entriesCategory->getName();
    $handlerOutputData['entriesCategory']['title'] = $entriesCategory->getTitle($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['SEOTitle'] = $entriesCategory->getSEOTitle($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['description'] = $entriesCategory->getDescription($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['SEODescription'] = $entriesCategory->getSEODescription($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['keywords'] = $entriesCategory->getKeywords($entriesCategoryLocale);
    $handlerOutputData['entriesCategory']['parentID'] = $entriesCategory->getParentID();
    $handlerOutputData['entriesCategory']['createdUnixTimestamp'] = $entriesCategory->getCreatedUnixTimestamp();
    $handlerOutputData['entriesCategory']['updatedUnixTimestamp'] = $entriesCategory->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else if ($CMSCore->urlp->getPath(2) === 'categories') {
  $entriesCategories = (new EntriesCategories($CMSCore))->getAll();
  $entriesCategoriesLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

  $handlerOutputData['entriesCategories'] = [];
  if (count($entriesCategories) > 0) {
    foreach ($entriesCategories as $category) {
      $category->initData(['id', 'texts', 'metadata', 'name', 'parentID', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      
      $handlerOutputData['entriesCategories'][] = [
        'id' => $category->getID(),
        'name' => $category->getName(),
        'title' => $category->getTitle($entriesCategoriesLocale),
        'SEOTitle' => $category->getSEOTitle($entriesCategoriesLocale),
        'description' => $category->getDescription($entriesCategoriesLocale),
        'SEODescription' => $category->getSEODescription($entriesCategoriesLocale),
        'keywords' => $category->getKeywords($entriesCategoryLocale),
        'parentID' => $category->getParentID(),
        'createdUnixTimestamp' => $category->getCreatedUnixTimestamp(),
        'updatedUnixTimestamp' => $category->getUpdatedUnixTimestamp()
      ];
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}
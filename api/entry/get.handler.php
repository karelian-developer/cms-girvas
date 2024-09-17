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

if (is_numeric($system_core->urlp->get_path(2))) {
  if ($system_core->urlp->get_path(3) == 'comments') {
    $entry_id = (int)$system_core->urlp->get_path(2);
    $entry = new Entry($system_core, $entry_id);
    
    if (Entry::exists_by_id($system_core, $entry_id)) {
      $get_comments_params_data = [];
      if (isset($_GET['limit'])) {
        $limit = (is_numeric($_GET['limit'])) ? (int)$_GET['limit'] : 0;
        if (isset($_GET['offset'])) {
          $offset = (is_numeric($_GET['offset'])) ? (int)$_GET['offset'] : 0;
        } else {
          $offset = 0;
        }

        $get_comments_params_data['limit'] = [$limit, $offset];
      }

      if (isset($_GET['sortColumn']) && isset($_GET['sortType'])) {
        $get_comments_params_data['order_by'] = [
          'column' => $_GET['sortColumn'],
          'sort' => $_GET['sortType']
        ];
      }

      if (isset($_GET['parentID'])) {
        if (is_numeric($_GET['parentID'])) {
          $get_comments_params_data['parent_id'] = (int)$_GET['parentID'];
        }
      }

      if (!isset($get_comments_params_data['parent_id'])) $get_comments_params_data['parent_id'] = 0;
      
      $entry_comments = $entry->get_comments($get_comments_params_data);

      $handler_output_data['comments'] = [];
      foreach ($entry_comments as $comment) {
        $comment->init_data(['entry_id', 'author_id', 'content', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);

        $comment_data = [];
        $comment_data['id'] = $comment->get_id();
        $comment_data['content'] = $comment->get_content();
        $comment_data['answersCount'] = $comment->get_answers_count();
        $comment_data['authorID'] = $comment->get_author_id();
        $comment_data['isHidden'] = $comment->is_hidden();
        $comment_data['hiddenReason'] = $comment->get_hidden_reason();
        $comment_data['rating'] = $comment->get_rating();
        $comment_data['ratingVoters'] = $comment->get_rating_voters();
        $comment_data['createdUnixTimestamp'] = $comment->get_created_unix_timestamp();
        $comment_data['updatedUnixTimestamp'] = $comment->get_updated_unix_timestamp();

        $handler_output_data['comments'][] = $comment_data;
      }

      $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
      $handler_status_code = 1;
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND'));
      $handler_status_code = 0;
    }
  } else {
    $entry_id = (is_numeric($system_core->urlp->get_path(2))) ? (int)$system_core->urlp->get_path(2) : 0;

    if (Entry::exists_by_id($system_core, $entry_id)) {
      $entry = new Entry($system_core, $entry_id);
      $entry->init_data(['name', 'author_id', 'category_id', 'texts', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);
      $entry_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

      $parsedown = new Parsedown();
      $parsedown->setSafeMode(true);
      $parsedown->setMarkupEscaped(true);

      $handler_output_data['entry'] = [];
      $handler_output_data['entry']['id'] = $entry->get_id();
      $handler_output_data['entry']['name'] = $entry->get_name();
      $handler_output_data['entry']['title'] = $entry->get_title($entry_locale);
      $handler_output_data['entry']['description'] = $entry->get_description($entry_locale);
      $handler_output_data['entry']['content'] = $entry->get_content($entry_locale);
      $handler_output_data['entry']['keywords'] = $entry->get_keywords($entry_locale);
      $handler_output_data['entry']['authorID'] = $entry->get_author_id();
      $handler_output_data['entry']['categoryID'] = $entry->get_category_id();
      $handler_output_data['entry']['previewURL'] = $entry->get_preview_url();
      $handler_output_data['entry']['isPublished'] = $entry->is_published();
      $handler_output_data['entry']['createdUnixTimestamp'] = $entry->get_created_unix_timestamp();
      $handler_output_data['entry']['updatedUnixTimestamp'] = $entry->get_updated_unix_timestamp();

      $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
      $handler_status_code = 1;
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND'));
      $handler_status_code = 0;
    }
  }
} else if ($system_core->urlp->get_path(2) == 'category') {
  $entries_category_id = (is_numeric($system_core->urlp->get_path(3))) ? (int)$system_core->urlp->get_path(3) : 0;
  $entries_category = (EntryCategory::exists_by_id($system_core, $entries_category_id)) ? new EntryCategory($system_core, $entries_category_id) : null;

  if (!is_null($entries_category)) {
    $entries_category->init_data(['id', 'texts', 'metadata', 'name', 'parent_id', 'created_unix_timestamp', 'updated_unix_timestamp']);
    $entries_category_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

    $handler_output_data['entriesCategory'] = [];
    $handler_output_data['entriesCategory']['id'] = $entries_category->get_id();
    $handler_output_data['entriesCategory']['name'] = $entries_category->get_name();
    $handler_output_data['entriesCategory']['title'] = $entries_category->get_title($entries_category_locale);
    $handler_output_data['entriesCategory']['description'] = $entries_category->get_description($entries_category_locale);
    $handler_output_data['entriesCategory']['parentID'] = $entries_category->get_parent_id();
    $handler_output_data['entriesCategory']['createdUnixTimestamp'] = $entries_category->get_created_unix_timestamp();
    $handler_output_data['entriesCategory']['updatedUnixTimestamp'] = $entries_category->get_updated_unix_timestamp();

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
} else if ($system_core->urlp->get_path(2) == 'categories') {
  $entries_categories = (new EntriesCategories($system_core))->get_all();
  $entries_categories_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

  $handler_output_data['entriesCategories'] = [];
  if (count($entries_categories) > 0) {
    foreach ($entries_categories as $entries_category) {
      $entries_category->init_data(['id', 'texts', 'metadata', 'name', 'parent_id', 'created_unix_timestamp', 'updated_unix_timestamp']);
      array_push($handler_output_data['entriesCategories'], [
        'id' => $entries_category->get_id(),
        'name' => $entries_category->get_name(),
        'title' => $entries_category->get_title($entries_categories_locale),
        'description' => $entries_category->get_description($entries_categories_locale),
        'parentID' => $entries_category->get_parent_id(),
        'createdUnixTimestamp' => $entries_category->get_created_unix_timestamp(),
        'updatedUnixTimestamp' => $entries_category->get_updated_unix_timestamp()
      ]);
    }

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
}

?>
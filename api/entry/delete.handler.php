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
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Entries as Entries;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($system_core->urlp->get_path(2) == 'category') {
    if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      $entries_category_id = (is_numeric($_DELETE['entries_category_id'])) ? (int)$_DELETE['entries_category_id'] : 0;
      $entries = new Entries($system_core);

      if (EntryCategory::exists_by_id($system_core, $entries_category_id)) {
        if ($entries->get_count_by_category_id($entries_category_id) == 0) {
          $entries_category = new EntryCategory($system_core, $entries_category_id);
          $entries_category_is_deleted = $entries_category->delete();

          if ($entries_category_is_deleted) {
            $handler_message = 'Категория записей успешно удалена.';
            $handler_status_code = 1;
          } else {
            $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
            $handler_status_code = 0;
          }
        } else {
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_DELETION_EXISTS_ENTRIES'));
          $handler_status_code = 0;
        }
      }
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
      $handler_status_code = 0;
    }
  } else {
    if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_DELETE['entry_id'])) {
        $entry_id = (is_numeric($_DELETE['entry_id'])) ? (int)$_DELETE['entry_id'] : 0;

        if (Entry::exists_by_id($system_core, $entry_id)) {
          $entry = new Entry($system_core, $entry_id);

          $entry->init_data(['texts']);
          $entry_title = $entry->get_title();

          $entry_is_deleted = $entry->delete();

          if ($entry_is_deleted) {
            $sc_report = \core\PHPLibrary\SystemCore\Report::create($system_core, \core\PHPLibrary\SystemCore\Report::REPORT_TYPE_ID_AP_ENTRY_DELETED, [
              'clientIP' => $system_core->client->get_ip_address(),
              'entryTitle' => $entry_title,
              'date' => date('Y/m/d H:i:s', time())
            ]);

            $handler_message = $system_core->locale->get_single_value_by_key('API_DELETE_DATA_SUCCESS');
            $handler_status_code = 1;
          } else {
            $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
            $handler_status_code = 0;
          }
        } else {
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND'));
          $handler_status_code = 0;
        }

        $handler_output_data['modalClose'] = true;
        $handler_output_data['reload'] = true;
      }
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
      $handler_status_code = 0;
    }
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
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

if ($system_core->client->is_logged(2)) {
  $handler_output_data['dom'] = [];

  $media_files_path = sprintf('%s/uploads/media', $system_core->get_cms_path());
  $media_files = array_diff(scandir($media_files_path), ['.', '..']);

  $files = [];

  foreach ($media_files as $file) {
    $file_path = sprintf('%s/%s', $media_files_path, $file);
    $file_url = $file;
    
    array_push($files, [
      'file_url' => $file_url,
      'created_unix_timestamp' => filemtime($file_path)
    ]);
  }

  usort($files, function($a, $b) {
    if ($a['created_unix_timestamp'] == $b['created_unix_timestamp']) {
      return 0;
    }

    return ($a['created_unix_timestamp'] > $b['created_unix_timestamp']) ? -1 : 1;
  });

  $media_files = [];
  foreach ($files as $file) {
    error_log(date('Y-m-d H:i:s', $file['created_unix_timestamp']));
    array_push($media_files, $file['file_url']);
  }

  $media_files_transformed = [];

  foreach ($media_files as $media_file) {
    array_push($media_files_transformed, sprintf('/uploads/media/%s', $media_file));
  }

  $handler_output_data['items'] = $media_files_transformed;

  if (!empty($media_files_transformed)) {
    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_FILES_ERROR_NOT_FOUND')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
} else {
  http_response_code(401);
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
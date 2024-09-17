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
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) {
    $template_name = $_POST['template_name'];
    $template_url = 'https://repository.cms-girvas.ru/templates/' . $_POST['template_name'];

    $ch = curl_init($template_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $curl_exucute_result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($curl_exucute_result['outputData'])) {
      $template_dir_path = sprintf('%s/templates/%s', CMS_ROOT_DIRECTORY, $_POST['template_name']);
      $template_archive_path = sprintf('%s/templates/%s.zip', CMS_ROOT_DIRECTORY, $_POST['template_name']);

      $ch_archive = curl_init();
      curl_setopt($ch_archive, CURLOPT_URL, $curl_exucute_result['outputData']['download_url']);
      curl_setopt($ch_archive, CURLOPT_RETURNTRANSFER, 1);
      $curl_archive_exucute_result = curl_exec($ch_archive);
      curl_close($ch_archive);
      
      $file = fopen($template_archive_path, "w+");
      fputs($file, $curl_archive_exucute_result);
      fclose($file);

      if (file_exists($template_archive_path)) {
        $zip = new ZipArchive();

        if ($zip->open($template_archive_path) === true) {
          mkdir($template_dir_path);

          $zip->extractTo($template_dir_path);
          $zip->close();

          unlink($template_archive_path);

          $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_TEMPLATE_UPLOADED') : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNZIPPING_NOT_POSSIBLE')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_TEMPLATE_ERROR_REPOSITORY_DATA_NOT_GETTED')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
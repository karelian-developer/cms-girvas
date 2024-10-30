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

use \core\PHPLibrary\Metrics as Metrics;
use \core\PHPLibrary\Metrics\Session as MetricsSession;

$client_ip = $system_core->client->get_ip_address();
$metrics = new Metrics($system_core);

if (isset($_GET['time'])) {
  $metrics_timestamp = (is_numeric($_GET['time'])) ? strtotime(date('Y/m/d', $_GET['time'])) : strtotime(date('Y/m/d', time()));

  if (MetricsSession::exists_by_timestamp($system_core, $metrics, $metrics_timestamp)) {
    $metrics_session = MetricsSession::get_by_timestamp($system_core, $metrics, $metrics_timestamp);

    if (!is_null($metrics_session)) {
      $metrics_session->init_data(['data']);

      $handler_output_data['data'] = $metrics_session->get_data();
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
} elseif (isset($_GET['timeStart']) && isset($_GET['timeEnd'])) {
  $metrics_timestamp_start = (is_numeric($_GET['timeStart'])) ? strtotime(date('Y/m/d', $_GET['timeStart'])) : strtotime(date('Y/m/d', time()));
  $metrics_timestamp_end = (is_numeric($_GET['timeEnd'])) ? strtotime(date('Y/m/d', $_GET['timeEnd'])) : strtotime(date('Y/m/d', time()));

  $handler_output_data['data'] = [];

  $metrics_sessions = $metrics->get_sessions_by_timestamp_range($metrics_timestamp_start, $metrics_timestamp_end);
  if (!empty($metrics_sessions)) {
    foreach ($metrics_sessions as $session) {
      $session->init_data(['data']);

      array_push($handler_output_data['data'], $session->get_data());
    }

    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  } {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
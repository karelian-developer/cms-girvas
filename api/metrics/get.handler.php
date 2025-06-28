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

$clientIP = $CMSCore->client->getIPAddress();
$metrics = new Metrics($CMSCore);

if (isset($_GET['time'])) {
  $metricsTimestamp = (is_numeric($_GET['time'])) ? strtotime(date('Y/m/d', $_GET['time'])) : strtotime(date('Y/m/d', time()));

  if (MetricsSession::existsByTimestamp($CMSCore, $metrics, $metricsTimestamp)) {
    $metricsSession = MetricsSession::getByTimestamp($CMSCore, $metrics, $metricsTimestamp);

    if (!is_null($metricsSession)) {
      $metricsSession->initData(['data']);

      $handlerOutputData['data'] = $metricsSession->getData();
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} elseif (isset($_GET['timeStart']) && isset($_GET['timeEnd'])) {
  $metricsTimestampStart = (is_numeric($_GET['timeStart'])) ? strtotime(date('Y/m/d', $_GET['timeStart'])) : strtotime(date('Y/m/d', time()));
  $metricsTimestampEnd = (is_numeric($_GET['timeEnd'])) ? strtotime(date('Y/m/d', $_GET['timeEnd'])) : strtotime(date('Y/m/d', time()));

  $handlerOutputData['data'] = [];

  $metricsSessions = $metrics->getSessionsByTimestampRange($metricsTimestampStart, $metricsTimestampEnd);
  if (!empty($metricsSessions)) {
    foreach ($metricsSessions as $session) {
      $session->initData(['data']);

      array_push($handlerOutputData['data'], $session->getData());
    }

    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
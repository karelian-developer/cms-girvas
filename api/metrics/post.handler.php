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

if (array_key_exists('Metrics-Token', $handlerHeaders)) {
  $clientIP = $CMSCore->client->get_ip_address();
  $metricsToken = $handlerHeaders['Metrics-Token'];
  $metricsTimestamp = (is_numeric($_POST['time'])) ? strtotime(date('Y/m/d', $_POST['time'])) : strtotime(date('Y/m/d', time()));
  $metricsCurrentURL = strip_tags(str_replace('\'', '', $_POST['current_url']));
  $metricsReferrerURL = strip_tags(str_replace('\'', '', $_POST['referrer_url']));
  $metricsIsNewVisit = (bool)$_POST['is_visited_new'];

  $metrics = new Metrics($CMSCore);
  $metrics->set_timestamp($metricsTimestamp);

  if (!MetricsSession::exists_by_timestamp($CMSCore, $metrics, $metricsTimestamp)) {
    $metricsSession = MetricsSession::create($CMSCore, $metrics);
  } else {
    $metricsSession = MetricsSession::get_by_timestamp($CMSCore, $metrics, $metricsTimestamp);
  }

  if (!is_null($metricsSession)) {
    $metricsSession->init_data(['data']);
    
    $metricsData = [];
    $metricsDataSort = $metricsSession->get_data();

    if (isset($metricsDataSort['metrics']['views'][$metricsToken])) {
      if ($metricsReferrerURL != $metricsCurrentURL) {
        array_push($metricsDataSort['metrics']['views'][$metricsToken]['url_transfers'], [
          $metricsCurrentURL => [
            'referral' => $metricsReferrerURL,
            'isVisitedNew' => $metricsIsNewVisit,
            'time' => time()
          ]
        ]);
      }
      
      $metricsDataSort['metrics']['time'] = $metricsTimestamp;
      $metricsDataSort['metrics']['views'][$metricsToken] = [
        'ip' => $clientIP,
        'time' => $metricsTimestamp,
        'URLTransfers' => $metricsDataSort['metrics']['views'][$metricsToken]['url_transfers'],
        'URLs' => $metricsDataSort['metrics']['views'][$metricsToken]['urls']
      ];

      if (array_key_exists($metricsCurrentURL, $metricsDataSort['metrics']['views'][$metricsToken]['urls'])) {
        $URLValue = $metricsDataSort['metrics']['views'][$metricsToken]['urls'][$metricsCurrentURL];
        $metricsDataSort['metrics']['views'][$metricsToken]['urls'][$metricsCurrentURL] = $URLValue + 1;
      } else {
        $metricsDataSort['metrics']['views'][$metricsToken]['urls'][$metricsCurrentURL] = 1;
      }
    } else {
      $metricsDataSort['metrics']['time'] = $metricsTimestamp;
      $metricsDataSort['metrics']['views'][$metricsToken] = [
        'ip' => $clientIP,
        'time' => $metricsTimestamp,
        'URLTransfers' => [
          [$metricsCurrentURL => [
            'referral' => $metricsReferrerURL,
            'isVisitedNew' => $metricsIsNewVisit,
            'time' => time()
          ]]
        ],
        'urls' => [$metricsCurrentURL => 1]
      ];
    }

    $metricsData['data'] = $metricsDataSort;

    $metricsSession->update($metricsData);
  }
}

?>
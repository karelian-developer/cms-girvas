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

if (array_key_exists('Metrics-Token', $handler_headers)) {
  $client_ip = $system_core->client->get_ip_address();
  $metrics_token = $handler_headers['Metrics-Token'];
  $metrics_timestamp = (is_numeric($_POST['time'])) ? strtotime(date('Y/m/d', $_POST['time'])) : strtotime(date('Y/m/d', time()));
  $metrics_current_url = $_POST['current_url'];
  $metrics_referrer_url = $_POST['referrer_url'];
  $metrics_is_visited_new = (bool)$_POST['is_visited_new'];

  $metrics = new Metrics($system_core);
  $metrics->set_timestamp($metrics_timestamp);

  if (!MetricsSession::exists_by_timestamp($system_core, $metrics, $metrics_timestamp)) {
    $metrics_session = MetricsSession::create($system_core, $metrics);
  } else {
    $metrics_session = MetricsSession::get_by_timestamp($system_core, $metrics, $metrics_timestamp);
  }

  if (!is_null($metrics_session)) {
    $metrics_session->init_data(['data']);
    
    $metrics_data = [];
    $metrics_data_ = $metrics_session->get_data();

    if (isset($metrics_data_['metrics']['views'][$metrics_token])) {
      if ($metrics_referrer_url != $metrics_current_url) {
        array_push($metrics_data_['metrics']['views'][$metrics_token]['url_transfers'], [
          $metrics_current_url => [
            'referral' => $metrics_referrer_url,
            'is_visited_new' => $metrics_is_visited_new,
            'time' => time()
          ]
        ]);
      }
      
      $metrics_data_['metrics']['time'] = $metrics_timestamp;
      $metrics_data_['metrics']['views'][$metrics_token] = [
        'ip' => $client_ip,
        'time' => $metrics_timestamp,
        'url_transfers' => $metrics_data_['metrics']['views'][$metrics_token]['url_transfers'],
        'urls' => $metrics_data_['metrics']['views'][$metrics_token]['urls']
      ];

      if (array_key_exists($metrics_current_url, $metrics_data_['metrics']['views'][$metrics_token]['urls'])) {
        $url_value = $metrics_data_['metrics']['views'][$metrics_token]['urls'][$metrics_current_url];
        $metrics_data_['metrics']['views'][$metrics_token]['urls'][$metrics_current_url] = $url_value + 1;
      } else {
        $metrics_data_['metrics']['views'][$metrics_token]['urls'][$metrics_current_url] = 1;
      }
    } else {
      $metrics_data_['metrics']['time'] = $metrics_timestamp;
      $metrics_data_['metrics']['views'][$metrics_token] = [
        'ip' => $client_ip,
        'time' => $metrics_timestamp,
        'url_transfers' => [
          [$metrics_current_url => [
            'referral' => $metrics_referrer_url,
            'is_visited_new' => $metrics_is_visited_new,
            'time' => time()
          ]]
        ],
        'urls' => [$metrics_current_url => 1]
      ];
    }

    $metrics_data['data'] = $metrics_data_;

    $metrics_session->update($metrics_data);
  }
}

?>
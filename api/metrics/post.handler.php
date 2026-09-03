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
  $clientIP = $CMSCore->client->getIPAddress();
  $metricsToken = $handlerHeaders['Metrics-Token'];
  $metricsTimestamp = (is_numeric($_POST['time'])) ? strtotime(date('Y/m/d', $_POST['time'])) : strtotime(date('Y/m/d', time()));
  $metricsCurrentURL = strip_tags(str_replace('\'', '', $_POST['current_url']));
  $metricsReferrerURL = strip_tags(str_replace('\'', '', $_POST['referrer_url']));
  $metricsIsNewVisit = (bool) $_POST['is_visited_new'];

  $metrics = new Metrics($CMSCore);
  $metrics->setTimestamp($metricsTimestamp);

  if (!MetricsSession::existsByTimestamp($CMSCore, $metrics, $metricsTimestamp)) {
    $metricsSession = MetricsSession::create($CMSCore, $metrics);
  } else {
    $metricsSession = MetricsSession::getByTimestamp($CMSCore, $metrics, $metricsTimestamp);
  }

  if (!is_null($metricsSession)) {
    $metricsSession->initData(['data']);
    
    $metricsData = [];
    $metricsDataSort = $metricsSession->getData();

    if (isset($metricsDataSort['metrics']['views'][$metricsToken])) {
      // Добавляем переход, если это не тот же URL
      if ($metricsReferrerURL !== $metricsCurrentURL) {
        array_push($metricsDataSort['metrics']['views'][$metricsToken]['URLTransfers'], [
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
        'URLTransfers' => $metricsDataSort['metrics']['views'][$metricsToken]['URLTransfers'],
        'urls' => $metricsDataSort['metrics']['views'][$metricsToken]['urls']
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

    // ==========================================
    // ПЕРЕСЧЁТ visits0 И visits1
    // ==========================================
    $visits0 = [];
    $visits1 = [];

    foreach ($metricsDataSort['metrics']['views'] as $token => $viewData) {
      // Проверяем переходы
      if (isset($viewData['URLTransfers']) && !empty($viewData['URLTransfers'])) {
        foreach ($viewData['URLTransfers'] as $transfer) {
          foreach ($transfer as $url => $data) {
            $referral = $data['referral'] ?? '';
            
            // visits0: уникальные токены с переходами (не прямые заходы)
            if (!empty($referral) && $referral !== $url) {
              if (!in_array($token, $visits0)) {
                $visits0[] = $token;
              }
            }
            
            // visits1: новые посещения (isVisitedNew = true)
            if (isset($data['isVisitedNew']) && $data['isVisitedNew'] === true) {
              if (!in_array($token, $visits1)) {
                $visits1[] = $token;
              }
            }
          }
        }
      }
    }

    // Сохраняем пересчитанные значения
    $metricsDataSort['metrics']['visits0'] = $visits0;
    $metricsDataSort['metrics']['visits1'] = $visits1;

    // ==========================================
    // ЛОГ ДЛЯ ОТЛАДКИ
    // ==========================================
    error_log(sprintf(
      '[Metrics] POST: token=%s, visits0=%d, visits1=%d, transfers=%d',
      substr($metricsToken, 0, 20) . '...',
      count($visits0),
      count($visits1),
      isset($metricsDataSort['metrics']['views'][$metricsToken]['URLTransfers']) ? count($metricsDataSort['metrics']['views'][$metricsToken]['URLTransfers']) : 0
    ));

    $metricsData['data'] = $metricsDataSort;
    $metricsSession->update($metricsData);
  }
}
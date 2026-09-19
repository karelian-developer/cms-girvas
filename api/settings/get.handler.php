<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas
 * @copyright   Copyright (c) 2022 - 2026, Andrey Shestakov & Garbalo
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

$allowedSettings = [
  'security_cookie_banner_status' => 'bool',
  'base_site_title'               => 'string',
  'base_locale'                   => 'string',
  'security_allowed_users_registration_status' => 'bool',
  'seo_site_keywords' => 'json',
];

$keysParam = $_GET['keys'] ?? '';
$requestedKeys = $keysParam !== ''
  ? array_filter(array_map('trim', explode(',', $keysParam)))
  : array_keys($allowedSettings);

$result = [];

foreach ($requestedKeys as $key) {
  if (!isset($allowedSettings[$key])) {
    continue;
  }

  $exists = $CMSCore->configurator->existsDatabaseEntryValue($key);
  $value = $exists ? $CMSCore->configurator->getDatabaseEntryValue($key) : null;

  $result[$key] = match ($allowedSettings[$key]) {
    'bool' => $value === 'on' || $value === true || $value === '1' || $value === 1,
    'int' => (int)$value,
    'json' => $value !== null ? json_decode($value, true) : null,
    default => (string)($value ?? '')
  };
}

$handlerOutputData['settings'] = $result;
$handlerMessage = $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
$handlerStatusCode = $handlerStatusCode ?? 1;
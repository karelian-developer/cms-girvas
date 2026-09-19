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

use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\SystemCore\Reports\Rotator as ReportsRotator;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if (!$CMSCore->client->isLogged(2)) {
  http_response_code(401);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = 0;
  return;
}

$clientUser = $CMSCore->client->getUser(2);
$clientUser->initData(['login', 'metadata']);
$clientUserGroup = $clientUser->getGroup();
$clientUserGroup->initData(['permissions']);

$hasAccess = $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)
  || $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SUPERUSER);

if (!$hasAccess) {
  http_response_code(403);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
  $handlerStatusCode = 0;
  return;
}

// Срок хранения (дней) — из настроек, по умолчанию 365
$retentionDays = 365;

if ($CMSCore->configurator->existsDatabaseEntryValue('security_reports_retention_days')) {
  $value = (int)$CMSCore->configurator->getDatabaseEntryValue('security_reports_retention_days');

  if ($value > 0) {
    $retentionDays = $value;
  }
}

// Запуск ротации
$result = ReportsRotator::rotate($CMSCore, $retentionDays, 1000);

$rotatedCount = $result['rotated'] ?? 0;

// Логирование
CMSReport::create(
  $CMSCore,
  CMSReport::REPORT_TYPE_ID_AP_REPORTS_ROTATED,
  [
    'rotatedCount' => $rotatedCount,
    'threshold' => $result['threshold'] ?? 0,
    'days' => $retentionDays,
    'initiatedByID' => $clientUser->getID(),
    'initiatedByLogin' => $clientUser->getLogin(),
    'ip' => $CMSCore->client::getRealIPAddress($CMSCore)
  ]
);

$handlerOutputData['rotatedCount'] = $rotatedCount;
$handlerOutputData['days'] = $retentionDays;

$handlerMessage = $CMSCore->locale->getSingleValueByKey('API_REPORTS_ROTATION_SUCCESS');
$handlerStatusCode = 1;
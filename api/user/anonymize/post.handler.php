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

use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\User\Anonymizer as UserAnonymizer;
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

$hasAccess = $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)
  || $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SUPERUSER);

if (!$hasAccess) {
  http_response_code(403);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
  $handlerStatusCode = 0;
  return;
}

$userID = (int)($_POST['user_id'] ?? 0);

if ($userID <= 0 || !User::existsByID($CMSCore, $userID)) {
  http_response_code(404);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
  $handlerStatusCode = 0;
  return;
}

$user = new User($CMSCore, $userID);
$user->initData(['login']);

// Нельзя обезличить супер-админа
if ($user->isSuperAdmin()) {
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ANONYMIZE_ERROR_SUPERADMIN');
  $handlerStatusCode = 0;
  return;
}

// Нельзя обезличить самого себя
if ($user->getID() === $clientUser->getID()) {
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ANONYMIZE_ERROR_SELF');
  $handlerStatusCode = 0;
  return;
}

$reason = trim((string)($_POST['reason'] ?? 'retention_expired'));

$anonymized = UserAnonymizer::anonymize($CMSCore, $userID, $reason, $clientUser->getID());

if ($anonymized) {
  CMSReport::create(
    $CMSCore,
    CMSReport::REPORT_TYPE_ID_AP_USER_ANONYMIZED,
    [
      'targetUserID' => $userID,
      'targetUserLoginBefore' => $user->getLogin(),
      'anonymizedByID' => $clientUser->getID(),
      'anonymizedByLogin' => $clientUser->getLogin(),
      'reason' => $reason,
      'ip' => $CMSCore->client::getRealIPAddress($CMSCore)
    ]
  );

  $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_ANONYMIZE_SUCCESS');
  $handlerStatusCode = 1;
} else {
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
  $handlerStatusCode = 0;
}
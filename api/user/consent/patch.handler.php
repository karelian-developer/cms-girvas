<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2026, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\User\Consent as UserConsent;
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\SystemCore\Report as Report;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login', 'metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  $hasAccess = $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_CONSENTS_MANAGEMENT)
  || $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SUPERUSER);

  if (!$hasAccess) {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
    return;
  }

  // ID согласия: либо из path /handler/user/consent/<id>, либо из PATCH-параметра
  $consentID = 0;

  if ($CMSCore->urlp->getPath(3) !== null && is_numeric($CMSCore->urlp->getPath(3))) {
    $consentID = (int) $CMSCore->urlp->getPath(3);
  } elseif (isset($_PATCH['consent_id']) && is_numeric($_PATCH['consent_id'])) {
    $consentID = (int) $_PATCH['consent_id'];
  }

  $revokeReason = trim((string)($_PATCH['revoke_reason'] ?? ''));

  if ($consentID <= 0) {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
    $handlerStatusCode = $handlerStatusCode ?? 0;
    return;
  }

  if (empty($revokeReason)) {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONSENT_REVOKE_ERROR_REASON_REQUIRED');
    $handlerStatusCode = $handlerStatusCode ?? 0;
    return;
  }

  // Проверяем, что согласие существует и активно
  $consent = new UserConsent($CMSCore, $consentID);
  $consent->initData();

  if ($consent->getID() !== $consentID || $consent->isRevoked()) {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONSENT_REVOKE_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
    return;
  }

  // Отзываем — передаём ID админа
  $revoked = UserConsent::revoke($CMSCore, $consentID, $revokeReason, $clientUser->getID());

  if ($revoked) {
    // Собираем заголовки документа
    $documentTitle = '';
    $documentTitles = [];

    if ($consent->getPageStaticID() > 0) {
      $pageStatic = new PageStatic($CMSCore, $consent->getPageStaticID());
      if ($pageStatic !== null) {
        $pageStatic->initData(['texts', 'name']);
        $documentTitle = $pageStatic->getTitle($CMSCore->locale->getName());

        foreach ($CMSCore->getArrayLocalesNames() as $loc) {
          $documentTitles[$loc] = $pageStatic->getTitle($loc);
        }
      }
    }

    // Логируем отзыв админом
    Report::create(
      $CMSCore,
      Report::REPORT_TYPE_ID_BASE_CONSENT_REVOKED,
      [
        'userID' => $consent->getUserID(),
        'consentID' => $consentID,
        'pageStaticID' => $consent->getPageStaticID(),
        'documentTitle' => $documentTitle,
        'documentTitles' => $documentTitles,
        'documentVersion' => $consent->getDocumentVersion(),
        'locale' => $consent->getLocale(),
        'revokeReason' => $revokeReason,
        'revokeSource' => 'admin',
        'revokedByID' => $clientUser->getID(),
        'revokedByLogin' => $clientUser->getLogin(),
        'ip' => $CMSCore->client->getIPAddress()
      ]
    );

    $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_CONSENT_REVOKED_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
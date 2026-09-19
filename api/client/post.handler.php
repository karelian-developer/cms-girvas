<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Client\Session as ClientSession;
use \core\PHPLibrary\User\Consent as UserConsent;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if ($CMSCore->urlp->getPath(2) === 'consent-cookie') {
  $pageStaticID = (int) ($_POST['pageStaticID'] ?? 0);
  $documentVersion = trim((string) ($_POST['documentVersion'] ?? ''));
  $locale = trim((string) ($_POST['locale'] ?? $CMSCore->locale->getName()));

  if ($pageStaticID <= 0 || empty($documentVersion)) {
    http_response_code(400);
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
    $handlerStatusCode = 0;
    return;
  }

  $clientIP = $CMSCore->client::getRealIPAddress($CMSCore);
  $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

  if ($CMSCore->client->isLogged(1)) {
    $user = $CMSCore->client->getUser(1);
    $user->initData(['login', 'metadata']);

    $consent = UserConsent::give(
      $CMSCore,
      $user->getID(),
      0,
      0,
      $pageStaticID,
      $documentVersion,
      $locale,
      $clientIP,
      $userAgent,
      'cookie_banner'
    );

    if ($consent !== null) {
      CMSReport::create(
        $CMSCore,
        CMSReport::REPORT_TYPE_ID_BASE_CONSENT_GIVEN,
        [
          'userID' => $user->getID(),
          'consentID' => $consent->getID(),
          'pageStaticID' => $pageStaticID,
          'documentVersion' => $documentVersion,
          'locale' => $locale,
          'ip' => $clientIP,
          'source' => 'cookie_banner'
        ]
      );

      $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_CONSENT_COOKIE_SUCCESS');
      $handlerStatusCode = 1;
    } else {
      $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = 0;
    }
  } else {
    $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_CONSENT_COOKIE_ANONYMOUS_SUCCESS');
    $handlerStatusCode = 1;
  }
}

if ($CMSCore->urlp->getPath(2) === 'session-end') {
  $sessionLevel = $CMSCore->urlp->getParam('level');
  $sessionLevel = is_numeric($sessionLevel) ? (int) $sessionLevel : 0;
  $session = $CMSCore->client->getSession($sessionLevel, ['userID']);
  $sessionUserID = $session->getUserID();

  if ($session !== null && $sessionLevel !== 0) {
    // Получаем данные пользователя до удаления сессии
    $user = null;
    if ($sessionUserID > 0) {
      $user = new \core\PHPLibrary\User($CMSCore, $sessionUserID);
      $user->initData(['login']);
    }

    $clientIP = $CMSCore->client->getIPAddress();

    // ============================================================
    // ЛОГИРОВАНИЕ ВЫХОДА ИЗ СИСТЕМЫ (152-ФЗ)
    // ============================================================
    if ($user !== null) {
      $reportType = $sessionLevel === 2
        ? CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL
        : CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL;

      CMSReport::create(
        $CMSCore,
        $reportType,
        [
          'userID' => $user->getID(),
          'ip' => $clientIP,
          'typeID' => $sessionLevel,
          'action' => 'logout'
        ]
      );
    }

    // Удаляем сессию
    $session->delete();

    // Проверяем, что сессия удалена
    if (!ClientSession::existsByIPAndUserID($CMSCore, $clientIP, $sessionUserID, $sessionLevel)) {
      $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;

      $handlerOutputData['result'] = true;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_SESSION_NOT_DELETED');
      $handlerStatusCode = $handlerStatusCode ?? 0;

      $handlerOutputData['result'] = false;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_SESSION_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;

    $handlerOutputData['result'] = false;
  }
}
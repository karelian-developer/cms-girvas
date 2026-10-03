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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login', 'metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  $hasAccess = $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_CONSENTS_MANAGEMENT)
    || $clientUserGroup->getID() === UserGroup::GROUP_SUPER_ID;

  if (!$hasAccess) {
    http_response_code(403);
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
    return;
  }

  // ============================================================
  // ЭКСПОРТ CSV
  // ============================================================
  if (($_POST['export'] ?? '') === 'csv') {
    $consents = UserConsent::getAll($CMSCore, 100000, 0);
    $localeName = $CMSCore->locale->getName();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="consents_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
      'ID',
      'Логин',
      'Email',
      'Документ',
      'Версия',
      'Локаль',
      'Источник',
      'Статус',
      'Дата согласия',
      'IP',
      'User-Agent',
      'Дата отзыва',
      'Причина отзыва',
      'Кто отозвал'
    ], ';');

    foreach ($consents as $consent) {
      $consent->initData();

      $userLogin = '';
      $userEmail = '';
      if ($consent->getUserID() > 0 && User::existsByID($CMSCore, $consent->getUserID())) {
        $userObject = new User($CMSCore, $consent->getUserID());
        $userObject->initData(['login', 'email']);
        $userLogin = $userObject->getLogin();
        $userEmail = $userObject->getEmail();
      }

      $documentTitle = '';
      if ($consent->getPageStaticID() > 0) {
        $pageStatic = new PageStatic($CMSCore, $consent->getPageStaticID());
        if ($pageStatic !== null) {
          $pageStatic->initData(['texts', 'name']);
          $documentTitle = $pageStatic->getTitle($localeName) ?: $pageStatic->getName();
        }
      }

      $revokedByLogin = '';
      if ($consent->getRevokedByID() > 0 && User::existsByID($CMSCore, $consent->getRevokedByID())) {
        $revokerObject = new User($CMSCore, $consent->getRevokedByID());
        $revokerObject->initData(['login']);
        $revokedByLogin = $revokerObject->getLogin();
      }

      fputcsv($output, [
        $consent->getID(),
        $userLogin,
        $userEmail,
        $documentTitle,
        $consent->getDocumentVersion(),
        $consent->getLocale(),
        $consent->getSource(),
        $consent->isRevoked() ? 'Отозвано' : 'Активно',
        date('d.m.Y H:i:s', $consent->getConsentedAt()),
        $consent->getIP(),
        $consent->getUserAgent(),
        $consent->isRevoked() ? date('d.m.Y H:i:s', $consent->getRevokedAt()) : '',
        $consent->getRevokeReason(),
        $revokedByLogin
      ], ';');
    }

    fclose($output);
    exit;  // ← ранний выход, чтобы не рендерить JSON
  }

  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
  $handlerStatusCode = $handlerStatusCode ?? 0;
} else {
  http_response_code(401);
  $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
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

if ($CMSCore->client->isLogged(2) && $CMSCore->urlp->getPath(2) === 'secret-codes') {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) {
    $chars = 'qwertyuiopasdfghjklzxcvbnm123456789';

    for ($codeIndex = 0; $codeIndex < 4; $codeIndex++) {
      $codeChars = [];
      for ($charIndex = 0; $charIndex < 4; $charIndex++) {
        array_push($codeChars, $chars[rand(0, strlen($chars) - 1)]);
      }

      switch ($codeIndex) {
        case 0: $codeChar = 'a'; break;
        case 1: $codeChar = 'b'; break;
        case 2: $codeChar = 'c'; break;
        case 3: $codeChar = 'd'; break;
      }

      $CMSCore->configurator->updateDatabaseEntryValue(
        'security_admin_code_' . $codeChar,
        password_hash(implode($codeChars), PASSWORD_ARGON2ID)
      );

      unset($codeChars);
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_UTILS_SECRET_CODES_GENERATED_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
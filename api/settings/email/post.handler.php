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

use \core\PHPLibrary\Mail\SMTPClient as SMTPClient;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata', 'email']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  // Проверка прав пользователя на доступ к данному действию
  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) {
    ini_set('mail.log', CMS_ROOT_DIRECTORY . '/logs/mail.log');
    ini_set('mail.add_x_header', 'On');

    $handlerEvent = $_POST['event'] ?? '';

    if ($handlerEvent === 'testSend') {
      $SMTPConfiguration = $CMSCore->configurator->getOtherCollection('smtp');
      
      if (!empty($SMTPConfiguration)) {
        $CMSEmail = 'no-reply@' . $SMTPConfiguration['domain'];

        try {
          $SMTPClient = new SMTPClient(
            $SMTPConfiguration['host'],
            $SMTPConfiguration['port'],
            $SMTPConfiguration['username'],
            $SMTPConfiguration['password']
          );

          $SMTPClient->connect();
          $SMTPClient->login();

          $SMTPClient->sendEmail(
            $CMSEmail,
            $clientUser->getEmail(),
            'Test Subject',
            'This is test message'
          );

          $SMTPClient->disconnect();
        } catch (Exception $exception) {
          $handlerMessage = 'API ERROR: ' . $exception;
          $handlerStatusCode = 0;
        }
      } else {
        $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_SMTP_NOT_CONFIGRED');
        $handlerStatusCode = 0;
      }
    }
  } else {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
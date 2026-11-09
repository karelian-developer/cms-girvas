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
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata','email']);
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
        $CMSEmail = null;

        try {
          $CMSEmail = SMTPClient::resolveFromEmail(
            $SMTPConfiguration,
            $CMSCore->configurator->get('domainCookies') ?? null
          );
        } catch (Exception $exception) {
          error_log('[testSend] resolveFromEmail failed: ' . $exception->getMessage());
          $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = 0;
        }

        if ($CMSEmail !== null) {
          // Локаль: ?localeMessage / ?locale, fallback — базовая локаль сайта
          $requestedLocaleName = $_GET['localeMessage'] ?? $_GET['locale'] ?? null;
          if ($requestedLocaleName === null || trim((string) $requestedLocaleName) === '') {
            $requestedLocaleName = $CMSCore->getCMSLocale()->getName();
          }

          $emailTextsRaw = $CMSCore->configurator->getDatabaseEntryValue('smtp_email_texts');
          $emailTexts = [];

          if (is_array($emailTextsRaw)) {
            $emailTexts = $emailTextsRaw;
          } elseif (is_string($emailTextsRaw) && trim($emailTextsRaw) !== '') {
            $decoded = json_decode($emailTextsRaw, true);
            if (is_array($decoded)) $emailTexts = $decoded;
          }

          $localeEmailTexts = SMTPClient::pickLocaleTexts(
            $emailTexts,
            $requestedLocaleName,
            $CMSCore->getCMSLocale()->getName()
          );

          $mailTitleTemplate     = $localeEmailTexts['testTitle'] ?? '';
          $mailContentTemplate   = $localeEmailTexts['testContent'] ?? '';
          $mailCopyrightTemplate = $localeEmailTexts['testCopyright'] ?? '';

          if (trim($mailTitleTemplate) === '')   $mailTitleTemplate = 'Test Subject';
          if (trim($mailContentTemplate) === '') $mailContentTemplate = 'This is test message';

          $replacements = [
            'LOGIN'      => $clientUser->getLogin(),
            'USER_EMAIL' => $clientUser->getEmail(),
            'SITE_URL'   => $CMSCore->getSiteURL(),
            'YEAR'       => date('Y'),
          ];

          $mailTitle = SMTPClient::renderTemplate($mailTitleTemplate, $replacements);
          $mailContentHtml = SMTPClient::renderMarkdown(
            SMTPClient::renderTemplate($mailContentTemplate, $replacements)
          );
          $mailCopyright = SMTPClient::renderTemplate($mailCopyrightTemplate, $replacements);

          try {
            $SMTPClient = new SMTPClient(
              $SMTPConfiguration['host'],
              $SMTPConfiguration['port'],
              $SMTPConfiguration['username'],
              $SMTPConfiguration['password']
            );

            $SMTPClient->connect();
            $SMTPClient->login();
            $themeBaseName = $CMSCore->configurator->existsDatabaseEntryValue('base_template')
              ? $CMSCore->configurator->getDatabaseEntryValue('base_template')
              : 'default';
            $theme = new Theme($CMSCore, $themeBaseName);

            $mailContent = ThemeCollector::assemblyFileContent($theme, 'templates/email/default.tpl', [
              'EMAIL_TITLE'     => $mailTitle,
              'EMAIL_CONTENT'   => $mailContentHtml,
              'EMAIL_COPYRIGHT' => $mailCopyright,
            ]);

            $SMTPClient->sendEmail($CMSEmail, $clientUser->getEmail(), $mailTitle, $mailContent, true);
            $SMTPClient->disconnect();

            CMSReport::create(
              $CMSCore,
              CMSReport::REPORT_TYPE_ID_AP_SETTINGS_EDITED,
              [
                'changedFields' => array_keys($_POST),
                'userID'    => $clientUser->getID(),
                'userLogin' => $clientUser->getLogin(),
                'ip'        => $CMSCore->client->getIPAddress()
              ]
            );

            $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_UTILS_SETTINGS_TEST_EMAIL_SENDED_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } catch (Exception $exception) {
            error_log('[testSend] SMTP send failed: ' . $exception->getMessage());
            $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
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
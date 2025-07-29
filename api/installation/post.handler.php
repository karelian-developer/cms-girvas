<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\SystemCore\DatabaseConnector as CMSDatabaseConnector;
use \core\PHPLibrary\Client as Client;
use \core\PHPLibrary\User as User;
use \DOMDocument as DOMDocument;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (!file_exists(CMS_ROOT_DIRECTORY . '/INSTALLED')) {
  if ($CMSCore->urlp->getPath(2) === 'set-locales-and-timezone') {
    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');
    
    $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);

    if ($CMSCore->configurator->existsDatabaseEntryValue('base_locale')) {
      $CMSCore->configurator->updateDatabaseEntryValue('base_locale', $_POST['setting_base_locale']);
    } else {
      $CMSCore->configurator->insertDatabaseEntryValue('base_locale', $_POST['setting_base_locale']);
    }

    if ($CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale')) {
      $CMSCore->configurator->updateDatabaseEntryValue('base_admin_locale', $_POST['setting_admin_locale']);
    } else {
      $CMSCore->configurator->insertDatabaseEntryValue('base_admin_locale', $_POST['setting_admin_locale']);
    }

    if ($CMSCore->configurator->existsDatabaseEntryValue('base_timezone')) {
      $CMSCore->configurator->updateDatabaseEntryValue('base_timezone', $_POST['setting_base_timezone']);
    } else {
      $CMSCore->configurator->insertDatabaseEntryValue('base_timezone', $_POST['setting_base_timezone']);
    }

    $tipBlockElement->setAttribute('class', 'tip tip_green');
    $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');

    $document->appendChild($tipBlockElement);

    $handlerOutputData['html'] = $document->saveHTML();
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($CMSCore->urlp->getPath(2) === 'set-metadata') {
    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');

    $siteTitle = trim($_POST['site_title']);
    $siteTitle = strip_tags($siteTitle);
    $siteTitle = str_replace('\'', '"', $siteTitle);

    $siteDescription = trim($_POST['site_description']);
    $siteDescription = strip_tags($siteDescription);
    $siteDescription = str_replace('\'', '"', $siteDescription);

    $siteKeywords = trim($_POST['site_keywords']);
    $siteKeywords = strip_tags($siteKeywords);
    $siteKeywords = str_replace('\'', '"', $siteKeywords);
    $siteKeywords = preg_split('/\s*\,\s*/', $siteKeywords);
    
    if (!empty($siteTitle) && !empty($siteDescription)) {
      $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);

      if ($CMSCore->configurator->existsDatabaseEntryValue('base_site_title')) {
        $CMSCore->configurator->updateDatabaseEntryValue('base_site_title', $siteTitle);
      } else {
        $CMSCore->configurator->insertDatabaseEntryValue('base_site_title', $siteTitle);
      }

      if ($CMSCore->configurator->existsDatabaseEntryValue('seo_site_description')) {
        $CMSCore->configurator->updateDatabaseEntryValue('seo_site_description', $siteDescription);
      } else {
        $CMSCore->configurator->insertDatabaseEntryValue('seo_site_description', $siteDescription);
      }

      if ($CMSCore->configurator->existsDatabaseEntryValue('seo_site_keywords')) {
        $CMSCore->configurator->updateDatabaseEntryValue('seo_site_keywords', json_encode($siteKeywords));
      } else {
        $CMSCore->configurator->insertDatabaseEntryValue('seo_site_keywords', json_encode($siteKeywords));
      }

      $tipBlockElement->setAttribute('class', 'tip tip_green');
      $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $tipBlockElement->setAttribute('class', 'tip tip_red');
      $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_SITE_TITLE_OR_DESCRIPTION_IS_EMPTY');

      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_SITE_TITLE_OR_DESCRIPTION_IS_EMPTY');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }

    $document->appendChild($tipBlockElement);

    $handlerOutputData['html'] = $document->saveHTML();
  }

  if ($CMSCore->urlp->getPath(2) === 'create-admin') {
    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');
    
    $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);

    $adminLogin = $_POST['admin_login'] ?? '';
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPassword = $_POST['admin_password'] ?? '';
    $adminPasswordRepeat = $_POST['admin_password_repeat'] ?? '';

    if (preg_match('/^[a-z0-9\_]{4,}$/i', $adminLogin)) {
      if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        if (preg_match('/^[a-z0-9\_\$\%\&\#\@\?]{8,}$/i', $adminPassword)) {
          if ($adminPassword == $adminPasswordRepeat) {
            if (!User::existsByLogin($CMSCore, $adminLogin)) {
              if (!User::existsByEmail($CMSCore, $adminEmail)) {
                $admin = User::create($CMSCore, $adminLogin, $adminEmail, $adminPassword);

                if (!is_null($admin)) {
                  $admin->update(['emailIsSubmitted' => true, 'metadata' => ['groupID' => 1]]);

                  $tipBlockElement->setAttribute('class', 'tip tip_green');
                  $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');

                  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');
                  $handlerStatusCode = $handlerStatusCode ?? 1;
                } else {
                  $tipBlockElement->setAttribute('class', 'tip tip_red');
                  $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
                }
              } else {
                $tipBlockElement->setAttribute('class', 'tip tip_red');
                $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_EMAIL_ALREADY_EXISTS');
              }
            } else {
              $tipBlockElement->setAttribute('class', 'tip tip_red');
              $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_LOGIN_ALREADY_EXISTS');
            }
          } else {
            $tipBlockElement->setAttribute('class', 'tip tip_red');
            $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_INVALID_REPEAT_PASSWORD');
          }
        } else {
          $tipBlockElement->setAttribute('class', 'tip tip_red');
          $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_INVALID_PASSWORD');
        }
      } else {
        $tipBlockElement->setAttribute('class', 'tip tip_red');
        $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_INVALID_EMAIL');
      }
    } else {
      $tipBlockElement->setAttribute('class', 'tip tip_red');
      $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ADMIN_ACCOUNT_CREATED_ERROR_INVALID_LOGIN');
    }

    $document->appendChild($tipBlockElement);
    $handlerOutputData['html'] = $document->saveHTML();
  }

  if ($CMSCore->urlp->getPath(2) === 'generate-secret-key') {
    $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);
    
    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');

    $chars = strtoupper('qwertyuiopasdfghjklzxcvbnm123456789');
    $codes = [];

    for ($codeIndex = 0; $codeIndex < 4; $codeIndex++) {
      $codeChars = [];

      for ($char_index = 0; $char_index < 4; $char_index++) {
        array_push($codeChars, $chars[rand(0, strlen($chars) - 1)]);
      }

      switch ($codeIndex) {
        case 0: $codeChar = 'a'; break;
        case 1: $codeChar = 'b'; break;
        case 2: $codeChar = 'c'; break;
        case 3: $codeChar = 'd'; break;
      }

      $codeFinal = implode($codeChars);
      array_push($codes, $codeFinal);

      if ($CMSCore->configurator->existsDatabaseEntryValue('security_admin_code_' . $codeChar)) {
        $CMSCore->configurator->updateDatabaseEntryValue('security_admin_code_' . $codeChar, password_hash($codeFinal, PASSWORD_ARGON2ID));
      } else {
        $CMSCore->configurator->insertDatabaseEntryValue('security_admin_code_' . $codeChar, password_hash($codeFinal, PASSWORD_ARGON2ID));
      }

      unset($codeChars);
    }

    $tipBlockElement->setAttribute('class', 'tip tip_green');
    $tipBlockElement->nodeValue = sprintf('%s: ' . implode('-', $codes), $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_SECRET_KEY_LABEL'));

    $document->appendChild($tipBlockElement);
    $handlerOutputData['html'] = $document->saveHTML();
  }

  if ($CMSCore->urlp->getPath(2) === 'finish') {
    $fileInstalledPath = sprintf('%s/INSTALLED', CMS_ROOT_DIRECTORY);

    $clientIP = Client::getRealIPAddress();

    $externalIP = @file_get_contents('https://api.ipify.org');
    if ($externalIP === false) {
      $externalIP = $_SERVER['SERVER_ADDR'];
    }

    $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);
    $CMSCore->client = new Client($CMSCore);

    $clientUser = new User($CMSCore, 1);
    $clientUser->initData(['email']);

    $clientUserEmail = $clientUser->getEmail();

    if (!file_exists($fileInstalledPath)) {
      $file = fopen($fileInstalledPath, 'w');
      fclose($file);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"https://cms-girvas.ru/feedback.php?type=install");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'ip' => $clientIP,
        'domain' => $CMSCore->configurator->get('domain'),
        'system_salt' => $CMSCore->configurator->get('salt'),
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'admin_email' => $clientUserEmail
      ]));

      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Installation-Hash: ' . hash('sha256', sprintf('{GIRVAS:HASH$INSTALL$%s$%s$%s}', $clientIP, $CMSCore->configurator->get('domain'), $CMSCore->configurator->get('salt')))
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $server_output = curl_exec($ch);
      curl_close($ch);
    }
  }
}
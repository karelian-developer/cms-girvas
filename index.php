<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\Manifest as ThemeManifest;
use \core\PHPLibrary\Mail\SMTPClient as SMTPClient;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore as CMSCore;

// Абсолютный путь до корневой директории CMS
define('CMS_ROOT_DIRECTORY', preg_replace('/[\/]*$/', '', $_SERVER['DOCUMENT_ROOT']));

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', CMS_ROOT_DIRECTORY . '/logs/girvas-error.log');

// ‿︵‿ヽ(°□° )ノ︵‿︵
define('IS_NOT_HACKED', true);

if (PHP_VERSION_ID < 80200) {
  die(sprintf('PHP version is too old (you have %s). CMS "GIRVAS" works on PHP version 8.2.0 and higher.', phpversion()));
}

$startTime = microtime(true);

require_once CMS_ROOT_DIRECTORY . '/core/PHPLibrary/core.interface.php';
require_once CMS_ROOT_DIRECTORY . '/core/PHPLibrary/systemCore.class.php';

$CMSCore = new CMSCore();
$CMSURLP = $CMSCore->urlp;

$CMSURLPathes = [];
$CMSURLPathes[] = $CMSURLP->getPath(0);

if ($CMSURLPathes[0] === 'handler') {

  include_once CMS_ROOT_DIRECTORY . '/handler.php';

} else if ($CMSURLPathes[0] === 'sitemap') {

  include_once CMS_ROOT_DIRECTORY . '/sitemap.php';

} else if ($CMSURLPathes[0] === 'feed') {

  include_once CMS_ROOT_DIRECTORY . '/feed.php';

} else if ($CMSURLPathes[0] === 'manifest') {

  $theme = $CMSCore->getTheme();

  $manifest = new ThemeManifest($CMSCore->configurator, $theme->core);
  foreach([256, 128, 96, 64, 48, 32, 16] as $faviconWidth) {
    $faviconSizesLabel = $faviconWidth . 'x' . $faviconWidth;
    $faviconURL = '/favicons/favicon-' . $faviconSizesLabel . '.png';
    $manifest->addIcon($faviconURL, [$faviconWidth, $faviconWidth], 'image/png');
  }

  header('Content-Type: application/json');
  echo $manifest->getJSON();

} else if ($CMSURLPathes[0] === 'password-reset') {

  $CMSCore->locale->setTypeName('handler');
  $CMSCore->locale->initPathes();

  $queryBuilder = new DatabaseQueryBuilder($CMSCore);
  $queryBuilder->setStatementSelect();
  $queryBuilder->statement->addSelections(['id']);
  $queryBuilder->statement->setClauseFrom();
  $queryBuilder->statement->clauseFrom->addTable('users');
  $queryBuilder->statement->clauseFrom->assembly();
  $queryBuilder->statement->setClauseWhere();
  $queryBuilder->statement->clauseWhere->addCondition(sprintf('metadata::jsonb->>\'passwordResetToken\' = \'%s\'', $CMSURLP->getParam('token')));
  $queryBuilder->statement->clauseWhere->assembly();
  $queryBuilder->statement->setClauseLimit(1);
  $queryBuilder->statement->assembly();

  $databaseConnection = $CMSCore->databaseConnector->database->connection;
  $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
  $databaseQuery->execute();

  $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);

  $user = $result ? new User($CMSCore, (int)$result['id']) : null;

  if ($user !== null) {
    $user->initData(['login', 'email', 'metadata', 'securityHash']);
    
    if ($user->getPasswordResetCreatedUnixTimestamp() + 600 > time()) {
      $userPasswordNew = random_int(10000000, 99999999);
      $user->update(['passwordHash' => User::passwordHash($CMSCore, $user->getSecurityHash(), $userPasswordNew), 'metadata' => ['passwordResetToken' => '', 'passwordResetTokenCreatedTimestamp' => 0]]);
      
      $themeBaseName = $CMSCore->configurator->existsDatabaseEntryValue('base_template')
        ? $CMSCore->configurator->getDatabaseEntryValue('base_template')
        : 'default';
      $theme = new Theme($CMSCore, $themeBaseName);

      $siteTitle = empty($CMSCore->configurator->getMetaTitle())
        ? $CMSCore->configurator->getSiteTitle()
        : $CMSCore->configurator->getMetaTitle();
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

          $mailTitle = $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_PASSWORD_RESETED_EMAIL_TITLE');
          $mailContentText = $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_PASSWORD_RESETED_EMAIL_CONTENT');
          $mailContent = ThemeCollector::assemblyFileContent($theme, 'templates/email/default.tpl', [
            'EMAIL_TITLE' => $mailTitle,
            'EMAIL_CONTENT' => sprintf(
              $mailContentText,
              $user->getLogin(),
              $userPasswordNew
            ),
            'EMAIL_COPYRIGHT' => $CMSCore->locale->getSingleValueByKey('API_USER_REQUEST_PASSWORD_RESET_EMAIL_COPYRIGHT')
          ]);

          $SMTPClient->sendEmail($CMSEmail, $user->getEmail(), $mailTitle, $mailContent, true);
          $SMTPClient->disconnect();

          echo $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_PASSWORD_RESETED_SUCCESS');
        } catch (Exception $exception) {
          echo $CMSCore->locale->getSingleValueByKey('API_ERROR_EXCEPTION') . $exception;
        }
      }
    } else {
      echo $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_PASSWORD_RESETED_TOKEN_IS_NOT_ACTIVE');
    }
  } else {
    echo $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
  }
} else {
  if ($CMSURLP->getParam('mode') !== 'install' && file_exists(CMS_ROOT_DIRECTORY . '/INSTALLED')) {
    if ($CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip_status') === 'on' && $CMSURLPathes[0] === 'admin') {
      /** @var array Массив разрешенных IP-адресов */
      $allowedIPs = json_decode($CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip'), true);
      
      if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        http_response_code(503);
        die('An attempted hacker attack has been detected.');
      }
    }
  }

  $theme = $CMSCore->getTheme();
  $theme->assemblyGlobalVariables();

  $loadTime = microtime(true) - $startTime; // Конечное время
  header('X-Load-Time: ' . round($loadTime, 3) . 's');

  echo $theme->core->assembled;
}
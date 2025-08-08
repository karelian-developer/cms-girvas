<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\EmailSender as EmailSender;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\Manifest as ThemeManifest;
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
      
      $themeBaseName = $CMSCore->configurator->existsDatabaseEntryValue('base_template') ? $CMSCore->configurator->getDatabaseEntryValue('base_template') : 'default';

      $theme = new Theme($CMSCore, $themeBaseName);

      $emailSender = new EmailSender($CMSCore);
      $emailSender->setFromUser('CMS GIRVAS', 'support@garbalo.com');
      $emailSender->setToUserEmail($user->getEmail());
      $emailSender->addHeader(sprintf("From: %s <%s>", 'CMS GIRVAS', 'support@garbalo.com'));
      $emailSender->addHeader(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
      $emailSender->addHeader("\r\nMIME-Version: 1.0");
      $emailSender->addHeader("\r\nContent-type: text/html; charset=UTF-8");
      $emailSender->addHeader("\r\n");

      $resetPasswordCreatedUnixTimestamp = time();
      $resetPasswordToken = md5($resetPasswordCreatedUnixTimestamp . $CMSCore::CMS_VERSION);

      $emailSender->setSubject('Новый пароль');
      $emailSender->setContent(ThemeCollector::assemblyFileContent($theme, 'templates/email/default.tpl', [
        'EMAIL_TITLE' => 'Новый пароль',
        'EMAIL_CONTENT' => sprintf('%s, здравствуйте! Используйте свой новый пароль для авторизации: <b>%d</b>. После авторизации рекомендуем сразу же его сменить.', $user->getLogin(), $userPasswordNew),
        'EMAIL_COPYRIGHT' => 'С уважением, администрация сайта.'
      ]));

      $emailSender->send();

      echo 'Your password reseted!';
    } else {
      echo 'Application is out of date!';
    }
  } else {
    echo 'Request is not exists!';
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
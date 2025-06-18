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
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore as CMSCore;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

// Абсолютный путь до корневой директории CMS
define('CMS_ROOT_DIRECTORY', preg_replace('/[\/]*$/', '', $_SERVER['DOCUMENT_ROOT']));
// ‿︵‿ヽ(°□° )ノ︵‿︵
define('IS_NOT_HACKED', true);

if (PHP_VERSION_ID < 80200) {
  die(sprintf('PHP version is too old (you have %s). CMS "GIRVAS" works on PHP version 8.2.0 and higher.', phpversion()));
}

require_once CMS_ROOT_DIRECTORY . '/core/PHPLibrary/systemCore.class.php';

$CMSCore = new CMSCore();

if ($CMSCore->urlp->get_path(0) === 'handler') {

  include_once CMS_ROOT_DIRECTORY . '/handler.php';

} else if ($CMSCore->urlp->get_path(0) === 'sitemap') {

  include_once CMS_ROOT_DIRECTORY . '/sitemap.php';

} else if ($CMSCore->urlp->get_path(0) === 'rss') {

  include_once CMS_ROOT_DIRECTORY . '/rss.php';

} else if ($CMSCore->urlp->get_path(0) === 'feed') {

  include_once CMS_ROOT_DIRECTORY . '/feed.php';

} else if ($CMSCore->urlp->get_path(0) === 'password-reset') {

  $queryBuilder = new DatabaseQueryBuilder($CMSCore);
  $queryBuilder->set_statement_select();
  $queryBuilder->statement->add_selections(['id']);
  $queryBuilder->statement->set_clause_from();
  $queryBuilder->statement->clauseFrom->add_table('users');
  $queryBuilder->statement->clauseFrom->assembly();
  $queryBuilder->statement->set_clause_where();
  $queryBuilder->statement->clauseWhere->add_condition(sprintf('metadata::jsonb->>\'passwordResetToken\' = \'%s\'', $CMSCore->urlp->get_param('token')));
  $queryBuilder->statement->clauseWhere->assembly();
  $queryBuilder->statement->set_clause_limit(1);
  $queryBuilder->statement->assembly();

  $databaseConnection = $CMSCore->databaseConnector->database->connection;
  $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
  $databaseQuery->execute();

  $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);

  $user = $result ? new User($CMSCore, (int)$result['id']) : null;

  if ($user !== null) {
    $user->init_data(['login', 'email', 'metadata', 'securityHash']);
    
    if ($user->get_password_reset_created_unix_timestamp() + 600 > time()) {
      $userPasswordNew = random_int(10000000, 99999999);
      $user->update(['passwordHash' => User::password_hash($CMSCore, $user->get_security_hash(), $userPasswordNew), 'metadata' => ['passwordResetToken' => '', 'passwordResetTokenCreatedTimestamp' => 0]]);
      
      $themeBaseName = $CMSCore->configurator->exists_database_entry_value('base_template') ? $CMSCore->configurator->get_database_entry_value('base_template') : 'default';

      $theme = new Theme($CMSCore, $themeBaseName);

      $emailSender = new EmailSender($CMSCore);
      $emailSender->set_from_user('CMS GIRVAS', 'support@garbalo.com');
      $emailSender->set_to_user_email($user->get_email());
      $emailSender->add_header(sprintf("From: %s <%s>", 'CMS GIRVAS', 'support@garbalo.com'));
      $emailSender->add_header(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
      $emailSender->add_header("\r\nMIME-Version: 1.0");
      $emailSender->add_header("\r\nContent-type: text/html; charset=UTF-8");
      $emailSender->add_header("\r\n");

      $resetPasswordCreatedUnixTimestamp = time();
      $resetPasswordToken = md5($resetPasswordCreatedUnixTimestamp . $CMSCore::CMS_VERSION);

      $emailSender->set_subject('Новый пароль');
      $emailSender->set_content(ThemeCollector::assembly_file_content($theme, 'templates/email/default.tpl', [
        'EMAIL_TITLE' => 'Новый пароль',
        'EMAIL_CONTENT' => sprintf('%s, здравствуйте! Используйте свой новый пароль для авторизации: <b>%d</b>. После авторизации рекомендуем сразу же его сменить.', $user->get_login(), $userPasswordNew),
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
  if ($CMSCore->urlp->get_param('mode') !== 'install' && file_exists(CMS_ROOT_DIRECTORY . '/INSTALLED')) {
    if ($CMSCore->configurator->get_database_entry_value('security_allowed_admin_ip_status') === 'on' && $CMSCore->urlp->get_path(0) === 'admin') {
      /** @var array Массив разрешенных IP-адресов */
      $allowedIPs = json_decode($CMSCore->configurator->get_database_entry_value('security_allowed_admin_ip'), true);
      
      if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        http_response_code(503);
        die('An attempted hacker attack has been detected.');
      }
    }
  }

  $theme = $CMSCore->get_template();
  $theme->assembly_global_variables();
  echo $theme->core->assembled;
}

?>
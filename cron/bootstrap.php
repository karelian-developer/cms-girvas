<?php

/**
 * CMS «ГИРВАС» — CLI bootstrap
 * 
 * Минимальный bootstrap для cron-скриптов.
 */

// Корневая директория — на уровень выше cron/
define('CMS_ROOT_DIRECTORY', dirname(__DIR__));

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', CMS_ROOT_DIRECTORY . '/logs/girvas-cron.log');

define('IS_NOT_HACKED', true);

if (PHP_VERSION_ID < 80200) {
  die(sprintf('PHP version is too old (you have %s). CMS "GIRVAS" works on PHP version 8.2.0 and higher.', phpversion()));
}

// Минимальные $_SERVER-значения для совместимости
$_SERVER['DOCUMENT_ROOT'] = CMS_ROOT_DIRECTORY;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'CMS GIRVAS Cron';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['HTTPS'] = '';

require_once CMS_ROOT_DIRECTORY . '/core/PHPLibrary/core.interface.php';
require_once CMS_ROOT_DIRECTORY . '/core/PHPLibrary/systemCore.class.php';

/**
 * Получить инициализированный SystemCore
 *
 * @return \core\PHPLibrary\SystemCore
 */
function cmsCronBootstrap() : \core\PHPLibrary\SystemCore
{
  return new \core\PHPLibrary\SystemCore();
}
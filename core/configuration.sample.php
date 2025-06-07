<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

/**
 * ВНИМАНИЕ! Файл "configuration.sample.php" является образцом конфигурационного файла.
 * Вы можете сюда подставить Ваши данные и переименовать файл в "configuration.php".
 * 
 * РЕКОМЕНДАЦИЯ: Не следует удалять файл "configuration.sample.php", поскольку конфигурационный
 * файл всегда можно будет вернуть в исходное состояние.
 */

$configuration = [
  'domain' => 'example.com',
  'domain_aliases' => ['127.0.0.1', 'localhost'],
  'domain_email' => 'example.com',
  'domain_cookies' => 'example.com',
  'database' => ['host' => '', 'user' => '', 'password' => '', 'name' => '', 'scheme' => '', 'prefix' => ''],
  // Системная соль (необходима для хеширования некоторых данных)
  // Пример: ?d7R(TF1f30br7tl=!PeIrk) <== (НЕ ИСПОЛЬЗУЙТЕ ЭТУ СОЛЬ)
  'system_salt' => '',
  // Алгоритм хеширования пароля (PASSWORD_DEFAULT, PASSWORD_BCRYPT, PASSWORD_ARGON2I, PASSWORD_ARGON2ID)
  // Подробнее: https://www.php.net/manual/en/function.password-hash.php
  'password_hashing_algorithm' => PASSWORD_DEFAULT,
  'session_expires' => 86400,
  'session_admin_expires' => 86400,
  'www_perm_redirect' => false,
  'ssl_csp' => [
    'default-src \'self\'',
    'style-src \'unsafe-inline\' {DOMAIN} {DOMAIN_ALIASES}',
    'script-src \'unsafe-inline\' \'nonce-{SCRIPT_HASH}\'',
    'script-src-elem {DOMAIN} {DOMAIN_ALIASES}',
    'manifest-src \'self\'',
    'img-src \'self\' data:'
  ],
  'ssl_perm_redirect' => false,
  'ssl_hsts_max_age' => 63072000,
  'ssl_hsts_include_subdomains' => false,
  'ssl_hsts_preload' => false
];

?>
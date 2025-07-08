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
<<<<<<< HEAD
  'domain_aliases' => ['127.0.0.1', 'localhost'],
  'domain_email' => 'example.com',
  'domain_cookies' => 'example.com',
=======
  'domainAliases' => ['127.0.0.1', 'localhost'],
  'domainEmail' => 'example.com',
  'domainCookies' => 'example.com',
  'SSLIsEnabled' => false,
>>>>>>> develop
  'database' => ['host' => '', 'user' => '', 'password' => '', 'name' => '', 'scheme' => '', 'prefix' => ''],
  // Системная соль (необходима для хеширования некоторых данных)
  // Пример: ?d7R(TF1f30br7tl=!PeIrk) <== (НЕ ИСПОЛЬЗУЙТЕ ЭТУ СОЛЬ)
  'salt' => '',
  // Алгоритм хеширования пароля (PASSWORD_DEFAULT, PASSWORD_BCRYPT, PASSWORD_ARGON2I, PASSWORD_ARGON2ID)
  // Подробнее: https://www.php.net/manual/en/function.password-hash.php
  'passwordHashingAlgorithm' => PASSWORD_DEFAULT,
  'sessionExpires' => 86400,
  'sessionAdminExpires' => 86400,
  'WWWPermRedirect' => false,
  'SSLCSP' => [
    'default-src \'self\'',
    'style-src \'unsafe-inline\' {DOMAIN} {DOMAIN_ALIASES}',
    'script-src \'unsafe-inline\' \'nonce-{SCRIPT_HASH}\'',
    'script-src-elem {DOMAIN} {DOMAIN_ALIASES}',
    'manifest-src \'self\'',
    'img-src \'self\' data:'
  ],
  'SSLPermRedirect' => false,
  'SSLHSTSMaxAge' => 63072000,
  'SSLHSTSIncludeSubdomains' => false,
  'SSLHSTSPreload' => false
];
<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

/**
 * ВНИМАНИЕ! Файл "configuration.sample.php" является образцом конфигурационного файла.
 * Вы можете сюда подставить Ваши данные и переименовать файл в "configuration.php".
 * 
 * РЕКОМЕНДАЦИЯ: Не следует удалять файл "configuration.sample.php", поскольку конфигурационный
 * файл всегда можно будет вернуть в исходное состояние.
 */

$configuration = [
  'domain' => 'example.ru',
  'domainAliases' => ['127.0.0.1', 'localhost'],
  'domainEmail' => 'example.ru',
  'domainCookies' => 'example.ru',
  'SSLIsEnabled' => false,
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
  'SSLHSTSPreload' => false,
  'notifierKeys' => [
    'telegram' => '',
    'max' => '',
    'vk' => '',
    'ok' => ''
  ]
];
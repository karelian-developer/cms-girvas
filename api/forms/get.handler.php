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

if ($CMSCore->urlp->getPath(2) === 'methods') {
  $handlerOutputData['methods'] = [
    ['id' => 1, 'name' => 'GET'],
    ['id' => 2, 'name' => 'POST'],
    ['id' => 3, 'name' => 'PUT'],
    ['id' => 4, 'name' => 'DELETE'],
    ['id' => 5, 'name' => 'PATCH']
  ];
}
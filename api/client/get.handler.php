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

// Проверка авторизации клиента
if ($CMSCore->urlp->getPath(2) === 'is-logged') {
  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;

  $handlerOutputData['result'] = $CMSCore->client->isLogged(1);
}

// Получение IP-адреса клиента
if ($CMSCore->urlp->getPath(2) === 'ip-address') {
  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;

  $handlerOutputData['result'] = $CMSCore->client->getIPAddress();
}
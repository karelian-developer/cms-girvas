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

// Получение версии CMS
if ($CMSCore->urlp->get_path(2) === 'cms-version') {
  $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;

  $handlerOutputData['result'] = $CMSCore::CMS_VERSION;
}

// Получение наименования CMS
if ($CMSCore->urlp->get_path(2) === 'cms-title') {
  $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;

  $handlerOutputData['result'] = $CMSCore::CMS_TITLE;
}

?>
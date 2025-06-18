<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Client\Session as ClientSession;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if ($CMSCore->urlp->get_path(2) === 'session-end') {
  $sessionLevel = $CMSCore->urlp->get_param('level');
  $sessionLevel = is_numeric($sessionLevel) ? (int) $sessionLevel : 0;
  $session = $CMSCore->client->get_session($sessionLevel, ['user_id']);
  $sessionUserID = $session->get_user_id();

  if ($session !== null && $sessionLevel !== 0) {
    $session->delete();

    if (!ClientSession::exists_by_ip_and_user_id($CMSCore, $CMSCore->client->get_ip_address(), $sessionUserID, $sessionLevel)) {
      $handlerMessage = $CMSCore->locale->get_single_value_by_key('API_POST_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;

      $handlerOutputData['result'] = true;
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_SESSION_NOT_DELETED');
      $handlerStatusCode = $handlerStatusCode ?? 0;

      $handlerOutputData['result'] = false;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_SESSION_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;

    $handlerOutputData['result'] = false;
  }
}

?>
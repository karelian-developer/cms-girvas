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

use \core\PHPLibrary\PageStatic as PageStatic;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    if (isset($_DELETE['page_static_id'])) {
      $pageStaticID = $_DELETE['page_static_id'] ?? 0;
      $pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

      if (PageStatic::existsByID($CMSCore, $pageStaticID)) {
        $pageStatic = new PageStatic($CMSCore, $pageStaticID);
        $pageStaticIsDeleted = $pageStatic->delete();

        if ($pageStaticIsDeleted) {
          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_STATIC_PAGE_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }

      $handlerOutputData['modalClose'] = true;
      $handlerOutputData['reload'] = true;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
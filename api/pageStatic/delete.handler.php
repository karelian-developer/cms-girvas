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
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    if (isset($_DELETE['page_static_id'])) {
      $pageStaticID = $_DELETE['page_static_id'] ?? 0;
      $pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

      if (PageStatic::existsByID($CMSCore, $pageStaticID)) {
        $pageStatic = new PageStatic($CMSCore, $pageStaticID);
        $pageStatic->initData(['name', 'texts']);

        // Получаем все языковые версии заголовка
        $pageTitles = [];
        $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
        foreach ($CMSLocalesNames as $localeName) {
          $pageTitles[$localeName] = $pageStatic->getTitle($localeName);
        }
        $pageName = $pageStatic->getName();

        // Логируем удаление страницы (152-ФЗ)
        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED,
          [
            'pageID' => $pageStaticID,
            'pageName' => $pageName,
            'pageTitles' => $pageTitles,
            'deletedByID' => $clientUser->getID(),
            'deletedByLogin' => $clientUser->getLogin(),
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );
        
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
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
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
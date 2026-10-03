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

use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
    /** @var int ID выборки */
    $sampleID = $CMSCore->urlp->getPath(3) ?? 0;
    $sampleID = is_numeric($sampleID) ? (int) $sampleID : 0;

    if (EntriesSample::existsByID($CMSCore, $sampleID)) {
      $sample = new EntriesSample($CMSCore, $sampleID);
      
      // Получаем данные выборки перед удалением
      $localeName = $CMSCore->locale->getName();
      $sample->initData(['name', 'texts', 'metadata']);
      $sampleTitle = $sample->getTitle($localeName);
      $sampleName = $sample->getName();
      
      // ============================================================
      // ЛОГИРОВАНИЕ УДАЛЕНИЯ ВЫБОРКИ (152-ФЗ)
      // ============================================================
      CMSReport::create(
        $CMSCore,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED,
        [
          'sampleID' => $sample->getID(),
          'sampleName' => $sampleName,
          'sampleTitle' => $sampleTitle,
          'deletedByID' => $clientUser->getID(),
          'deletedByLogin' => $clientUser->getLogin(),
          'ip' => $CMSCore->client->getIPAddress()
        ]
      );

      $isDeleted = $sample->delete();

      if ($isDeleted) {
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRIES_SAMPLE_ERROR_NOT_FOUND');
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
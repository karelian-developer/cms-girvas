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
use \core\PHPLibrary\PageStatic\Version as PageStaticVersion;

$pageStaticID = $CMSCore->urlp->getPath(2) ?? 0;
$pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

$pageStaticExists = $pageStaticID > 0 && PageStatic::existsByID($CMSCore, $pageStaticID);
$pageStaticIsLegalDocument = false;

if ($pageStaticExists) {
  $pageStaticTemp = new PageStatic($CMSCore, $pageStaticID);
  $pageStaticTemp->initData(['metadata']);
  $pageStaticIsLegalDocument = $pageStaticTemp->isLegalDocument();
}

$hasAccess = $CMSCore->client->isLogged(2) || $pageStaticIsLegalDocument;

if ($hasAccess) {
  if ($pageStaticExists) {
    $pageStatic = new PageStatic($CMSCore, $pageStaticID);
    $pageStatic->initData(['name', 'authorID', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $pageStaticLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['pageStatic'] = [];
    $handlerOutputData['pageStatic']['id'] = $pageStatic->getID();
    $handlerOutputData['pageStatic']['name'] = $pageStatic->getName();
    $handlerOutputData['pageStatic']['title'] = $pageStatic->getTitle($pageStaticLocale);
    $handlerOutputData['pageStatic']['SEOTitle'] = $pageStatic->getSEOTitle($pageStaticLocale);
    $handlerOutputData['pageStatic']['description'] = $pageStatic->getDescription($pageStaticLocale);
    $handlerOutputData['pageStatic']['SEODescription'] = $pageStatic->getSEODescription($pageStaticLocale);
    $handlerOutputData['pageStatic']['content'] = $pageStatic->getContent($pageStaticLocale);
    $handlerOutputData['pageStatic']['keywords'] = $pageStatic->getKeywords($pageStaticLocale);
    $handlerOutputData['pageStatic']['authorID'] = $pageStatic->getAuthorID();
    $handlerOutputData['pageStatic']['previewURL'] = $pageStatic->getPreviewURL();
    $handlerOutputData['pageStatic']['isPublished'] = $pageStatic->isPublished();
    $handlerOutputData['pageStatic']['createdUnixTimestamp'] = $pageStatic->getCreatedUnixTimestamp();
    $handlerOutputData['pageStatic']['updatedUnixTimestamp'] = $pageStatic->getUpdatedUnixTimestamp();
    $handlerOutputData['pageStatic']['isLegalDocument'] = $pageStatic->isLegalDocument();
    $handlerOutputData['pageStatic']['currentVersion'] = '';

    if ($pageStatic->isLegalDocument()) {
      $currentVersion = $pageStatic->getCurrentVersion($pageStaticLocale);
      if ($currentVersion !== null) {
        $currentVersion->initData(['version']);
        $handlerOutputData['pageStatic']['currentVersion'] = $currentVersion->getVersion();
      }
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_STATIC_PAGE_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
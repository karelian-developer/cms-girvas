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
use \core\PHPLibrary\EntriesSamples as EntriesSamples;

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  /** @var int ID выборки */
  $sampleID = $CMSCore->urlp->getPath(3) ?? 0;
  $sampleID = is_numeric($sampleID) ? (int) $sampleID : 0;

  if (EntriesSample::existsByID($CMSCore, $sampleID)) {
    $sample = new EntriesSample($CMSCore, $sampleID);
    $sample->initData(['texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    
    $localeName = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['entriesSample'] = [];
    $handlerOutputData['entriesSample']['id'] = $sample->getID();
    $handlerOutputData['entriesSample']['title'] = $sample->getTitle($localeName);
    $handlerOutputData['entriesSample']['description'] = $sample->getDescription($localeName);
    $handlerOutputData['entriesSample']['createdUnixTimestamp'] = $sample->getCreatedUnixTimestamp();
    $handlerOutputData['entriesSample']['updatedUnixTimestamp'] = $sample->getUpdatedUnixTimestamp();
    $handlerOutputData['entriesSample']['limitCount'] = $sample->getLimitCount();
    $handlerOutputData['entriesSample']['sortTypeID'] = $sample->getSortTypeID();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
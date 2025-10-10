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
    $sample->initData(['metadata']);
    
    $localeName = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['entriesSample'] = ['categories' => []];
    $handlerOutputData['entriesSample']['id'] = $sample->getID();

    $sampleCategoriesIDs = $sample->getCategoriesIDs();
    $sampleCategories = $sample->getCategories();
    
    if (count($sampleCategories) > 0) {
      foreach ($sampleCategories as $category) {
        $category->initData(['texts']);
        $isSelected = array_search($category->getID(), $sampleCategoriesIDs) !== false ? true : false;

        $handlerOutputData['entriesSample']['categories'][] = [
          'id' => $category->getID(),
          'title' => $category->getTitle($localeName),
          'isSelected' => $isSelected
        ];
      }
    }

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
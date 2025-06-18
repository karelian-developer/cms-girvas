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

if ($CMSCore->client->is_logged(1) || $CMSCore->client->is_logged(2)) {
  /** @var int ID выборки */
  $sampleID = $CMSCore->urlp->get_path(3) ?? 0;
  $sampleID = is_numeric($sampleID) ? (int) $sampleID : 0;
  
  if (EntriesSample::exists_by_id($CMSCore, $sampleID)) {
    $sample = new EntriesSample($CMSCore, $sampleID);
    $sample->init_data(['metadata']);
    
    $localeName = $CMSCore->urlp->get_param('locale') ?? $CMSCore->configurator->get_database_entry_value('base_locale');

    $handlerOutputData['entriesSample'] = ['categories' => []];
    $handlerOutputData['entriesSample']['id'] = $sample->get_id();

    $sampleCategoriesIDs = $sample->get_categories_ids();
    $sampleCategories = $sample->get_categories();
    if (count($sampleCategories) > 0) {
      foreach ($sampleCategories as $category) {
        $category->init_data(['texts']);
        $isSelected = array_search($category->get_id(), $sampleCategoriesIDs) !== false ? true : false;

        $handlerOutputData['entriesSample']['categories'][] = [
          'id' => $category->get_id(),
          'title' => $category->get_title($localeName),
          'isSelected' => $isSelected
        ];
      }
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
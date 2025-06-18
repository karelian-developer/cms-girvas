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

if ($CMSCore->client->is_logged(2)) {
  $pageStaticID = $CMSCore->urlp->get_path(2) ?? 0;
  $pageStaticID = is_numeric($pageStaticID) ? (int) $pageStaticID : 0;

  if (PageStatic::exists_by_id($CMSCore, $pageStaticID)) {
    $pageStatic = new PageStatic($CMSCore, $pageStaticID);
    $pageStatic->init_data(['name', 'author_id', 'texts', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);
    $pageStaticLocale = (!is_null($CMSCore->urlp->get_param('locale'))) ? $CMSCore->urlp->get_param('locale') : $CMSCore->configurator->get_database_entry_value('base_locale');

    $handlerOutputData['pageStatic'] = [];
    $handlerOutputData['pageStatic']['id'] = $pageStatic->get_id();
    $handlerOutputData['pageStatic']['name'] = $pageStatic->get_name();
    $handlerOutputData['pageStatic']['title'] = $pageStatic->get_title($pageStaticLocale);
    $handlerOutputData['pageStatic']['description'] = $pageStatic->get_description($pageStaticLocale);
    $handlerOutputData['pageStatic']['content'] = $pageStatic->get_content($pageStaticLocale);
    $handlerOutputData['pageStatic']['keywords'] = $pageStatic->get_keywords($pageStaticLocale);
    $handlerOutputData['pageStatic']['authorID'] = $pageStatic->get_author_id();
    $handlerOutputData['pageStatic']['previewURL'] = $pageStatic->get_preview_url();
    $handlerOutputData['pageStatic']['isPublished'] = $pageStatic->is_published();
    $handlerOutputData['pageStatic']['createdUnixTimestamp'] = $pageStatic->get_created_unix_timestamp();
    $handlerOutputData['pageStatic']['updatedUnixTimestamp'] = $pageStatic->get_updated_unix_timestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_STATIC_PAGE_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
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

if ($system_core->client->is_logged(2)) {
  $page_static_id = (is_numeric($system_core->urlp->get_path(2))) ? (int)$system_core->urlp->get_path(2) : 0;

  if (PageStatic::exists_by_id($system_core, $page_static_id)) {
    $page_static = new PageStatic($system_core, $page_static_id);
    $page_static->init_data(['name', 'author_id', 'texts', 'metadata', 'created_unix_timestamp', 'updated_unix_timestamp']);
    $page_static_locale = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $system_core->configurator->get_database_entry_value('base_locale');

    $handler_output_data['pageStatic'] = [];
    $handler_output_data['pageStatic']['id'] = $page_static->get_id();
    $handler_output_data['pageStatic']['name'] = $page_static->get_name();
    $handler_output_data['pageStatic']['title'] = $page_static->get_title($page_static_locale);
    $handler_output_data['pageStatic']['description'] = $page_static->get_description($page_static_locale);
    $handler_output_data['pageStatic']['content'] = $page_static->get_content($page_static_locale);
    $handler_output_data['pageStatic']['keywords'] = $page_static->get_keywords($page_static_locale);
    $handler_output_data['pageStatic']['authorID'] = $page_static->get_author_id();
    $handler_output_data['pageStatic']['previewURL'] = $page_static->get_preview_url();
    $handler_output_data['pageStatic']['isPublished'] = $page_static->is_published();
    $handler_output_data['pageStatic']['createdUnixTimestamp'] = $page_static->get_created_unix_timestamp();
    $handler_output_data['pageStatic']['updatedUnixTimestamp'] = $page_static->get_updated_unix_timestamp();

    $handler_message = $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS');
    $handler_status_code = 1;
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_STATIC_PAGE_ERROR_NOT_FOUND'));
    $handler_status_code = 0;
  }
} else {
  http_response_code(401);
  $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
  $handler_status_code = 0;
}

?>
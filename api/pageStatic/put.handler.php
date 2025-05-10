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
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    $page_static_name = isset($_PUT['page_static_name']) ? urlencode(htmlentities($_PUT['page_static_name'])) : '';
    $page_creation_allowed = true;
    $texts = [];

    $cms_locales_names = $system_core->get_array_locales_names();
    if (count($cms_locales_names) > 0) {
      foreach ($cms_locales_names as $index => $cms_locale_name) {
        $cms_locale = new Locale($system_core, $cms_locale_name);

        $title_input_name = sprintf('page_static_title_%s', $cms_locale->get_iso_639_2());
        $description_textarea_name = sprintf('page_static_description_%s', $cms_locale->get_iso_639_2());
        $content_textarea_name = sprintf('page_static_content_%s', $cms_locale->get_iso_639_2());
        $keywords_textarea_name = sprintf('page_static_keywords_%s', $cms_locale->get_iso_639_2());

        if (array_key_exists($title_input_name, $_PUT) || array_key_exists($description_textarea_name, $_PUT) || array_key_exists($content_textarea_name, $_PUT)) {
          if (!array_key_exists($cms_locale->get_name(), $texts)) $texts[$cms_locale->get_name()] = [];

          if (array_key_exists($title_input_name, $_PUT)) {
            $input_value = $_PUT[$title_input_name];
            $input_value = strip_tags($input_value);
            $input_value = str_replace('\'', '"', $input_value);

            $texts[$cms_locale->get_name()]['title'] = $input_value;
          }

          if (array_key_exists($description_textarea_name, $_PUT)) {
            $textarea_value = $_PUT[$description_textarea_name];
            $textarea_value = strip_tags($textarea_value);
            $textarea_value = str_replace('\'', '"', $textarea_value);

            $texts[$cms_locale->get_name()]['description'] = $textarea_value;
          }

          if (array_key_exists($content_textarea_name, $_PUT)) {
            $textarea_value = $_PUT[$content_textarea_name];
            $textarea_value = strip_tags($textarea_value, '<table><tr><td><th><b><u><i><hr>');
            $textarea_value = str_replace('\'', '"', $textarea_value);

            $texts[$cms_locale->get_name()]['content'] = $textarea_value;
          }

          if (array_key_exists($keywords_textarea_name, $_PUT)) {
            $textarea_value = $_PUT[$keywords_textarea_name];
            $textarea_value = strip_tags($textarea_value);
            $textarea_value = str_replace('\'', '"', $textarea_value);
            
            $texts[$cms_locale->get_name()]['keywords'] = preg_split('/\h*[\,]+\h*/', $textarea_value, -1, PREG_SPLIT_NO_EMPTY);
          }
        }
      }
    }

    if (empty($page_static_name)) {
      $handler_message = (!isset($handler_message)) ? 'Произошла внутренняя ошибка. Техническое наименование страницы не может быть пустым.' : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      $page_creation_allowed = false;
    }

    foreach ($_PUT as $key => $value) {
      if (preg_match('/^page_static\_additional\_field\_([a-z0-9\_]+)$/i', $key, $key_matches, PREG_OFFSET_CAPTURE) && !empty($value)) {
        if (!isset($page_static_data)) $page_static_data = [];
        if (!isset($page_static_data['metadata'])) $page_static_data['metadata'] = [];
        if (!isset($page_static_data['metadata']['additionalFields'])) $page_static_data['metadata']['additionalFields'] = [];
        
        $value_name_parts = explode('_', $key_matches[1][0]);
        foreach ($value_name_parts as $part_index => $part) {
          if ($part_index > 0) {
            $value_name_parts[$part_index] = ucfirst($part);
          }
        }

        if (is_bool($value)) $value = (int)$value;

        $page_static_data['metadata']['additionalFields'][implode($value_name_parts)] = htmlspecialchars(str_replace('\'', '"', $value));
      }
    }

    if ($page_creation_allowed) {
      $client_session = $system_core->client->get_session(2, ['user_id']);
      
      $page_static = PageStatic::create($system_core, $page_static_name, $client_session->get_user_id(), $texts);
      if (!is_null($page_static)) {
        $page_static->init_data(['*']);

        if (isset($page_static_data)) {
          $page_static->update($page_static_data);
        }

        $handler_output_data['pageStatic'] = [];
        $handler_output_data['pageStatic']['id'] = $page_static->get_id();

        $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS') : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  http_response_code(401);
  $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
  $handler_status_code = 0;
}

?>
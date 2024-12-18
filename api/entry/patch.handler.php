<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2023, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

 if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\SystemCore\FileConverter as FileConverter;
use \core\PHPLibrary\SystemCore\Locale as Locale;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($system_core->urlp->get_path(2) == 'category') {
    if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
      if (isset($_PATCH['entries_category_id'])) {
        $entries_category_id = (is_numeric($_PATCH['entries_category_id'])) ? (int)$_PATCH['entries_category_id'] : 0;

        if (EntryCategory::exists_by_id($system_core, $entries_category_id)) {
          $entries_category = new EntryCategory($system_core, $entries_category_id);
          $entries_category->init_data(['metadata']);
          $entries_category_data = [];

          $cms_locales_names = $system_core->get_array_locales_names();
          if (count($cms_locales_names) > 0) {
            foreach ($cms_locales_names as $index => $cms_locale_name) {
              $cms_locale = new Locale($system_core, $cms_locale_name);

              $entries_category_title_input_name = sprintf('entries_category_title_%s', $cms_locale->get_iso_639_2());
              $entries_category_description_textarea_name = sprintf('entries_category_description_%s', $cms_locale->get_iso_639_2());

              if (array_key_exists($entries_category_title_input_name, $_PATCH) || array_key_exists($entries_category_description_textarea_name, $_PATCH)) {
                if (!array_key_exists('texts', $entries_category_data)) $entries_category_data['texts'] = [];
                if (!array_key_exists($cms_locale->get_name(), $entries_category_data['texts'])) $entries_category_data['texts'][$cms_locale->get_name()] = [];

                if (array_key_exists($entries_category_title_input_name, $_PATCH)) {
                  $input_value = $_PATCH[$entries_category_title_input_name];
                  $input_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $input_value);
                  $input_value = str_replace('\'', '"', $input_value);
                  $input_value = htmlspecialchars($input_value);
      
                  $entries_category_data['texts'][$cms_locale->get_name()]['title'] = $input_value;
                }
    
                if (array_key_exists($entries_category_description_textarea_name, $_PATCH)) {
                  $input_value = $_PATCH[$entries_category_description_textarea_name];
                  $input_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $input_value);
                  $input_value = str_replace('\'', '"', $input_value);
                  $input_value = htmlspecialchars($input_value);
      
                  $entries_category_data['texts'][$cms_locale->get_name()]['description'] = $textarea_value;
                }
              }
            }
          }

          if (isset($_PATCH['entries_category_name'])) $entries_category_data['name'] = urlencode(htmlentities($_PATCH['entries_category_name']));
          if (isset($_PATCH['entries_category_parent_id'])) $entries_category_data['parent_id'] = $_PATCH['entries_category_parent_id'];
          
          if (isset($_PATCH['entries_category_show_index'])) {
            if (!isset($entries_category_data['metadata'])) $entries_category_data['metadata'] = [];
            $entries_category_data['metadata']['isShowedOnIndexPage'] = 1;
          } else {
            if (!isset($entries_category_data['metadata'])) $entries_category_data['metadata'] = [];
            $entries_category_data['metadata']['isShowedOnIndexPage'] = 0;
          }
          
          $entries_category_is_updated = $entries_category->update($entries_category_data);

          if ($entries_category_is_updated) {
            $handler_message = $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
            $handler_status_code = 1;
          } else {
            $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
            $handler_status_code = 0;
          }
        } else {
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_CATEGORY_ERROR_NOT_FOUND'));
          $handler_status_code = 0;
        }
      }
    }
  } else {
    $entry_title_length_max = 80;
    $entry_description_length_max = 600;

    if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_ENTRIES_EDIT)) {
      if (isset($_PATCH['entry_id'])) {
        $entry_id = (is_numeric($_PATCH['entry_id'])) ? (int)$_PATCH['entry_id'] : 0;

        if (Entry::exists_by_id($system_core, $entry_id)) {
          $entry = new Entry($system_core, $entry_id);
          $entry_data = [];

          $cms_locales_names = $system_core->get_array_locales_names();
          if (count($cms_locales_names) > 0) {
            foreach ($cms_locales_names as $index => $cms_locale_name) {
              $cms_locale = new Locale($system_core, $cms_locale_name);

              $entry_title_input_name = sprintf('entry_title_%s', $cms_locale->get_iso_639_2());
              $entry_description_textarea_name = sprintf('entry_description_%s', $cms_locale->get_iso_639_2());
              $entry_content_textarea_name = sprintf('entry_content_%s', $cms_locale->get_iso_639_2());
              $entry_keywords_textarea_name = sprintf('entry_keywords_%s', $cms_locale->get_iso_639_2());

              if (!array_key_exists('metadata', $entry_data)) $entry_data['metadata'] = [];
              if (isset($_PATCH['entry_is_published'])) $entry_data['metadata']['is_published'] = $_PATCH['entry_is_published'];

              if (array_key_exists($entry_title_input_name, $_PATCH) || array_key_exists($entry_description_textarea_name, $_PATCH) || array_key_exists($entry_content_textarea_name, $_PATCH)) {
                if (!array_key_exists('texts', $entry_data)) $entry_data['texts'] = [];
                if (!array_key_exists($cms_locale->get_name(), $entry_data['texts'])) $entry_data['texts'][$cms_locale->get_name()] = [];

                if (array_key_exists($entry_title_input_name, $_PATCH)) {
                  $input_value = $_PATCH[$entry_title_input_name];
                  $input_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $input_value);
                  $input_value = str_replace('\'', '"', $input_value);
      
                  $entry_data['texts'][$cms_locale->get_name()]['title'] = $input_value;
                }
    
                if (array_key_exists($entry_description_textarea_name, $_PATCH)) {
                  $textarea_value = $_PATCH[$entry_description_textarea_name];
                  $textarea_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $textarea_value);
                  $textarea_value = str_replace('\'', '"', $textarea_value);
      
                  $entry_data['texts'][$cms_locale->get_name()]['description'] = $textarea_value;
                }
                
                if (array_key_exists($entry_content_textarea_name, $_PATCH)) {
                  $textarea_value = $_PATCH[$entry_content_textarea_name];
                  $textarea_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $textarea_value);
                  $textarea_value = str_replace('\'', '"', $textarea_value);
      
                  $entry_data['texts'][$cms_locale->get_name()]['content'] = $textarea_value;
                }
    
                if (array_key_exists($entry_keywords_textarea_name, $_PATCH)) {
                  $textarea_value = $_PATCH[$entry_keywords_textarea_name];
                  $textarea_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $textarea_value);
                  $textarea_value = str_replace('\'', '"', $textarea_value);
    
                  $entry_data['texts'][$cms_locale->get_name()]['keywords'] = preg_split('/\h*[\,]+\h*/', $textarea_value, -1, PREG_SPLIT_NO_EMPTY);
                }
              }
            }
          }

          if (isset($_PATCH['entry_name'])) $entry_data['name'] = urlencode(htmlentities($_PATCH['entry_name']));
          if (isset($_PATCH['entry_category_id'])) $entry_data['category_id'] = $_PATCH['entry_category_id'];
          if (isset($_PATCH['entry_preview'])) {
            $file_uploaded_folder_path = sprintf('%s/uploads/media', CMS_ROOT_DIRECTORY);
            $file_converter = new FileConverter($system_core);
            $file_converted = $file_converter->convert($_PATCH['entry_preview'], $file_uploaded_folder_path, \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat::WEBP, true);
            
            if (is_array($file_converted)) {
              if (!array_key_exists('metadata', $entry_data)) $entry_data['metadata'] = [];
              $entry_data['metadata']['preview_url'] = sprintf('/uploads/media/%s', $file_converted['file_name']);
            }
          }

          $entry_is_published = isset($entry_data['metadata']['is_published']) ? $entry_data['metadata']['is_published'] : 0;

          // Если происходит публикация записи, то необходимо удостовериться, что
          // в записи присутствует стандартная локализация, в противном случае
          // система не даст сохранить ее.
          if ($entry_is_published) {
            /** @var \core\PHPLibrary\SystemCore\Locale */
            $base_locale = $system_core->get_cms_locale();
            /** @var string */
            $base_locale_name = $base_locale->get_name();

            $entry->init_data(['texts']);

            /** @var string Заголовок записи */
            $entry_title_default = $entry->get_title($base_locale_name);
            /** @var string описание записи */
            $entry_description_default = $entry->get_description($base_locale_name);
            /** @var string содержимое записи */
            $entry_content_default = $entry->get_content($base_locale_name);
            /** @var int дата обновления страницы в формате UNIX */
            $entry_data['metadata']['publishedUnixTimestamp'] = time();

            // Если заголовок, описание или содержимое стандартной локализации не задано, то
            // запись не будет обновлена.
            if (empty($entry_title_default) || empty($entry_description_default) || empty($entry_content_default)) {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_ENTRY_EMPTY_LOCALE_DEFAULT_PUBLISHED_ERROR'), $base_locale_name)) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            } else {
              /** @var bool Обновление записи */
              $entry_is_updated = $entry->update($entry_data);
            }
          } else {
            /** @var bool Обновление записи */
            $entry_is_updated = $entry->update($entry_data);
          }

          /** @var bool Костыль */
          $entry_is_updated = isset($entry_is_updated) ? $entry_is_updated : false;

          if ($entry_is_updated) {
            // Инициализация данных с текстом записи
            $entry->init_data(['texts']);
            
            /** @var \core\PHPLibrary\SystemCore\Report Новый отчет */
            $sc_report = \core\PHPLibrary\SystemCore\Report::create($system_core, \core\PHPLibrary\SystemCore\Report::REPORT_TYPE_ID_AP_ENTRY_EDITED, [
              'clientIP' => $system_core->client->get_ip_address(),
              'entryTitle' => $entry->get_title(),
              'date' => date('Y/m/d H:i:s', time())
            ]);

            $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS') : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
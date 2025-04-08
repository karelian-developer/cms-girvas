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

use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\EntriesSamples as EntriesSamples;
use \core\PHPLibrary\SystemCore\Locale as Locale;
use \core\PHPLibrary\EntriesSample\EnumSortTypeID as EnumSortTypeID;

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) {
    /** @var int ID выборки */
    //$sample_id = (is_numeric($system_core->urlp->get_path(3))) ? (int)$system_core->urlp->get_path(3) : 0;
    
    /** @var string Техническое наименование выборки */
    $sample_name = isset($_PUT['entries_sample_name']) ? $_PUT['entries_sample_name'] : '';
    $sample_name = trim($sample_name);
    $sample_name = strtolower($sample_name);

    /** @var int Лимит на количество записей в выборке */
    $sample_limit_count = isset($_PUT['entries_sample_limit_count']) ? $_PUT['entries_sample_limit_count'] : 0;
    $sample_limit_count = is_int($sample_limit_count) ? $sample_limit_count : 0;
    
    $sample_sort_type_id = isset($_PUT['entries_sample_sort_type_id']) ? $_PUT['entries_sample_sort_type_id'] : 0;
    $sample_sort_type_id = is_int($sample_sort_type_id) ? $sample_sort_type_id : 0;
    $sample_sort_type_id = match ($sample_sort_type_id) {
      1 => EnumSortTypeID::BY_DATE_OF_PUBLICATION->get_id(),
      2 => EnumSortTypeID::BY_DATE_OF_CREATION->get_id(),
      3 => EnumSortTypeID::BY_NUMBER_OF_VIEW->get_id(),
      4 => EnumSortTypeID::BY_NUMBER_OF_COMMENTS->get_id(),
      5 => EnumSortTypeID::BY_RELEVANCE->get_id(),
      default => EnumSortTypeID::BY_DATE_OF_PUBLICATION->get_id()
    };

    $sample_categories_ids = isset($_PUT['entries_sample_categories_id']) ? $_PUT['entries_sample_categories_id'] : [];
    
    /** @var array Текстовые значения для выборки */
    $sample_texts = [];

    $locales_names = $system_core->get_array_locales_names();
    if (count($locales_names) > 0) {
      foreach ($locales_names as $locale_index => $locale_name) {
        $locale = new Locale($system_core, $locale_name);
        $locale_iso_639_2 = $locale->get_iso_639_2();

        $input_sample_title_name = sprintf('entries_sample_title_%s', $locale_iso_639_2);
        $input_sample_description_name = sprintf('entries_sample_description_%s', $locale_iso_639_2);

        if (isset($_PUT[$input_sample_title_name]) || isset($_PUT[$input_sample_description_name])) {
          $sample_title = isset($_PUT[$input_sample_title_name]) ? $_PUT[$input_sample_title_name] : '';
          $sample_description = isset($_PUT[$input_sample_description_name]) ? $_PUT[$input_sample_description_name] : '';
          
          if (!isset($sample_texts[$locale_name])) $sample_texts[$locale_name] = [];

          if (preg_match('/\S/', $sample_title)) {
            $input_value = trim($sample_title);
            $input_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $sample_title);
            $input_value = str_replace('\'', '"', $sample_title);

            $sample_texts[$locale_name]['title'] = $input_value;
          }

          if (preg_match('/\S/', $sample_description)) {
            $textarea_value = trim($sample_description);
            $textarea_value = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $sample_description);
            $textarea_value = str_replace('\'', '"', $sample_description);

            $sample_texts[$locale_name]['description'] = $textarea_value;
          }
        }
      }
    }

    /** @var array Метаданные для выборки */
    $sample_metadata = [];

    $sample_metadata['limitCount'] = $sample_limit_count;
    $sample_metadata['sortTypeID'] = $sample_sort_type_id;

    if (!empty($sample_categories_ids)) {
      $sample_metadata['categoriesIDs'] = [];

      foreach ($sample_categories_ids as $sample_category_id) {
        if (EntryCategory::exists_by_id($system_core, $sample_category_id)) {
          array_push($sample_metadata['categoriesIDs'], $sample_category_id);
        }
      }
    }

    if (preg_match('/\S/', $sample_name)) {
      if (!EntriesSample::exists_by_name($system_core, $sample_name)) {
        $sample = EntriesSample::create($system_core, $sample_name, $sample_texts, $sample_metadata);

        if (!is_null($sample)) {
          $handler_output_data['entriesSample'] = [];
          $handler_output_data['entriesSample']['id'] = $sample->get_id();

          $handler_message = $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
          $handler_status_code = 1;
        } else {
          $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
          $handler_status_code = 0;
        }
      } else {
        $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_SAMPLE_ERROR_NAME_EXISTS'));
        $handler_status_code = 0;
      }
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRIES_SAMPLE_ERROR_NAME_EMPTY'));
      $handler_status_code = 0;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
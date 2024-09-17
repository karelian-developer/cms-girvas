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

use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryComment as EntryComment;

if ($system_core->client->is_logged(1)) {
  $client_user = $system_core->client->get_user(1);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  $comment_parent_id = isset($_PUT['comment_parent_id']) ? (int)$_PUT['comment_parent_id'] : 0;
  $comment_entry_id = isset($_PUT['comment_entry_id']) ? (int)$_PUT['comment_entry_id'] : 0;
  $comment_content = isset($_PUT['comment_content']) ? $_PUT['comment_content'] : '';

  $comment_content_length_min = 16;
  $comment_content_length_max = 400;

  if ($client_user_group->permission_check($client_user_group::PERMISSION_BASE_ENTRY_COMMENT_CREATE)) {
    if (!empty($comment_content)) {
      if (strlen($comment_content) >= $comment_content_length_min) {
        if (strlen($comment_content) <= $comment_content_length_max) {
          if (Entry::exists_by_id($system_core, $comment_entry_id)) {
            $comment = EntryComment::create($system_core, $comment_entry_id, $client_user->get_id(), $comment_content);
            
            if (!is_null($comment)) {
              // Система премодерации не будет проигнорирована, если пользователь является первичным
              // или его группа не является административной или модеративной
              if ($client_user_group->get_id() != 1 && $client_user_group->get_id() != 2 && $client_user->get_id() != 1) {
                $comment_risk_factors_detected = [];
                $comment_risk_factors = [
                  0 => '{LANG:COMMENT_PREMODERATION_BANNED_WORDS_DETECTED}',
                  1 => '{LANG:COMMENT_PREMODERATION_EXTERNAL_LINKS_DETECTED}',
                  2 => '{LANG:COMMENT_PREMODERATION_MANDATORY_PREMODERATION}',
                ];

                $setting_security_premoderation_create_status = $system_core->configurator->get_database_entry_value('security_premoderation_create_status');
                $setting_security_premoderation_words_filter_status = $system_core->configurator->get_database_entry_value('security_premoderation_words_filter_status');
                
                if ($setting_security_premoderation_words_filter_status == 'on' && $setting_security_premoderation_create_status != 'on') {
                  $setting_security_premoderation_words_filter_list = $system_core->configurator->get_database_entry_value('security_premoderation_words_filter_list');
                  
                  if (!empty($setting_security_premoderation_words_filter_list)) {
                    $words_array = json_decode($setting_security_premoderation_words_filter_list, true);
                    
                    $comment->init_data(['content']);
                    $comment_content = $comment->get_content();

                    foreach ($words_array as $word) {
                      $regex_pattern = sprintf('/%s/ui', $word);
                      if (preg_match($regex_pattern, $comment_content)) {
                        array_push($comment_risk_factors_detected, $comment_risk_factors[0]);
                        break;
                      }
                    }
                  }
                }
        
                $setting_security_premoderation_links_filter_status = $system_core->configurator->get_database_entry_value('security_premoderation_links_filter_status');
                if ($setting_security_premoderation_links_filter_status == 'on' && $setting_security_premoderation_create_status != 'on') {
                  $comment->init_data(['content']);
                  $comment_content = $comment->get_content();

                  $regex_pattern = '/(?:http(?:s)?\:\/\/)?((?:[\w\-]+\.)?(?:[\w\-]+)(?:\.[\w\-]+))/ui';
                  if (preg_match($regex_pattern, $comment_content, $regex_matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($regex_matches as $regex_match) {
                      $regex_pattern_domain = sprintf('/%s/ui', $system_core->configurator->get('domain'));
                      if (!preg_match($regex_pattern_domain, $regex_match[0])) {
                        array_push($comment_risk_factors_detected, $comment_risk_factors[1]);
                        break;
                      }
                    }
                  }
                }

                if ($setting_security_premoderation_create_status == 'on') {
                  array_push($comment_risk_factors_detected, $comment_risk_factors[2]);
                }

                if (!empty($comment_risk_factors_detected)) {
                  $comment_data['metadata']['is_hidden'] = true;
                  $comment_data['metadata']['hidden_reason'] = sprintf('{LANG:COMMENT_DETECTED_FROM_PREMODERATION_FILTER} (%s).', implode(', ', $comment_risk_factors_detected));
                }
              }

              $comment_data['metadata']['parent_id'] = $comment_parent_id;

              if (isset($comment_data['metadata'])) {
                if (count($comment_data['metadata']) > 0) {
                  $comment->update($comment_data);
                }
              }

              $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS') : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;

              $handler_output_data['comment'] = [];
              $handler_output_data['comment']['id'] = $comment->get_id();
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_ERROR_NOT_FOUND')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_MAX_CHARACTERS')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_FEW_CHARACTERS')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_EMPTY')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
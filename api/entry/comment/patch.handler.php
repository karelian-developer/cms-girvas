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

use \core\PHPLibrary\EntryComment as EntryComment;

if ($system_core->client->is_logged(1) || $system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(1);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  $comment_data = [];

  if (isset($_PATCH['comment_id'])) {
    $comment_id = (is_numeric($_PATCH['comment_id'])) ? (int)$_PATCH['comment_id'] : 0;

    $comment_content_length_min = 16;
    $comment_content_length_max = 400;

    if (EntryComment::exists_by_id($system_core, $comment_id)) {
      $comment = new EntryComment($system_core, $comment_id);
      $comment->init_data(['metadata', 'author_id']);

      $comment_patching_is_allowed = false;

      if (isset($_PATCH['comment_content']) || isset($_PATCH['comment_parent_id'])) {
        $comment_content = $_PATCH['comment_content'];

        $comment_parent_id = (isset($_PATCH['comment_parent_id'])) ? $_PATCH['comment_parent_id'] : 0;
        $comment_parent_id = (is_numeric($comment_parent_id)) ? (int)$comment_parent_id : 0;

        if (strlen($comment_content) >= $comment_content_length_min) {
          if (strlen($comment_content) <= $comment_content_length_max) {
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

            if ($client_user_group->permission_check($client_user_group::PERMISSION_BASE_ENTRY_COMMENT_CHANGE) && $comment->get_author_id() == $client_user->get_id()) {
              $comment_data['content'] = $comment_content;
              $comment_data['metadata']['parentID'] = $comment_parent_id;
              $comment_patching_is_allowed = true;
            } elseif ($client_user_group->permission_check($client_user_group::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) {
              $comment_data['content'] = $comment_content;
              $comment_data['metadata']['parentID'] = $comment_parent_id;
              $comment_patching_is_allowed = true;
            }
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_MAX_CHARACTERS')) : $handler_message;
            $comment_patching_is_allowed = false;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_FEW_CHARACTERS')) : $handler_message;
          $comment_patching_is_allowed = false;
        }
      }

      if (isset($_PATCH['comment_is_hidden']) || isset($_PATCH['comment_hidden_reason'])) {
        $comment_is_hidden = $_PATCH['comment_is_hidden'];
        $comment_hidden_reason = (isset($_PATCH['comment_hidden_reason'])) ? $_PATCH['comment_hidden_reason'] : '';
        $comment_hidden_reason = htmlspecialchars(strip_tags($comment_hidden_reason));

        if ($client_user_group->permission_check($client_user_group::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) {
          $comment_data['metadata']['is_hidden'] = ($comment_is_hidden == 'on') ? true : false;
          $comment_data['metadata']['hidden_reason'] = $comment_hidden_reason;
          $comment_patching_is_allowed = true;
        }
      }

      if (isset($_PATCH['comment_rating_vote'])) {
        $comment_rating_vote = $_PATCH['comment_rating_vote'];

        if ($client_user_group->permission_check($client_user_group::PERMISSION_BASE_ENTRY_COMMENT_RATE) && $comment->get_author_id() != $client_user->get_id()) {
          $comment_rating_voters = $comment->get_rating_voters();
          $client_user_id = $client_user->get_id();
          $client_user_id_s = (string)$client_user_id;

          $allow_voting = false;
          if (isset($comment_rating_voters[$client_user_id_s])) {
            if ($comment_rating_voters[$client_user_id_s] != $comment_rating_vote) {
              $allow_voting = true;
            } else {
              $allow_voting = false;

              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_REPEAT_VOTE')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          } else {
            $allow_voting = true;
          }

          if ($allow_voting) {
            $comment_data['metadata']['rating_vote'] = [
              'voter_id' => $client_user_id,
              'vote' => $comment_rating_vote
            ];
          }

          if ($allow_voting) {
            $comment_patching_is_allowed = true;
          }
        }
      }
      
      if ($comment_patching_is_allowed) {
        $comment_is_updated = (!empty($comment_data)) ? $comment->update($comment_data) : false;

        if ($comment_is_updated) {
          $comment = new EntryComment($system_core, $comment_id);

          $comment_init_data = ['metadata'];
          
          if (isset($_PATCH['comment_content'])) array_push($comment_init_data, 'content');

          $comment->init_data($comment_init_data);
          $handler_output_data['comment']['id'] = $comment->get_id();
          $handler_output_data['comment']['rating'] = $comment->get_rating();
          if (isset($_PATCH['comment_content'])) $handler_output_data['comment']['content'] = $comment->get_content();

          $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS') : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ENTRY_COMMENT_ERROR_NOT_FOUND')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  }
} else {
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>
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

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(1);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  $commentData = [];

  if (isset($_PATCH['comment_id'])) {
    $commentID = $_PATCH['comment_id'] ?? 0;
    $commentID = is_numeric($commentID) ? (int) $commentID : 0;

    $commentContentLengthMin = 16;
    $commentContentLengthMax = 400;

    if (EntryComment::existsByID($CMSCore, $commentID)) {
      $comment = new EntryComment($CMSCore, $commentID);
      $comment->initData(['metadata', 'authorID']);

      $commentPatchingIsAllowed = false;

      if (isset($_PATCH['comment_content']) || isset($_PATCH['comment_parent_id'])) {
        $commentContent = $_PATCH['comment_content'] ?? '';
        $commentContent = trim($commentContent);
        
        $commentParentID = $_PATCH['comment_parent_id'] ?? 0;
        $commentParentID = is_numeric($commentParentID) ? (int) $commentParentID : 0;

        if (strlen($commentContent) >= $commentContentLengthMin) {
          if (strlen($commentContent) <= $commentContentLengthMax) {
            // Система премодерации не будет проигнорирована, если пользователь является первичным
            // или его группа не является административной или модеративной
            if ($clientUserGroup->getID() != 1 && $clientUserGroup->getID() != 2 && $clientUser->getID() != 1) {
              $commentRiskFactorsDetected = [];
              $commentRiskFactors = [
                0 => '{LANG:COMMENT_PREMODERATION_BANNED_WORDS_DETECTED}',
                1 => '{LANG:COMMENT_PREMODERATION_EXTERNAL_LINKS_DETECTED}',
                2 => '{LANG:COMMENT_PREMODERATION_MANDATORY_PREMODERATION}',
              ];

              $settingSecurityPremoderationCreateStatus = $CMSCore->configurator->getDatabaseEntryValue('security_premoderation_create_status');
              $settingSecurityPremoderationWordsFilterStatus = $CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_status');
              
              if ($settingSecurityPremoderationWordsFilterStatus === 'on' && $settingSecurityPremoderationCreateStatus !== 'on') {
                $settingSecurityPremoderationWordsFilterList = $CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_list');
                
                if (!empty($settingSecurityPremoderationWordsFilterList)) {
                  $words = json_decode($settingSecurityPremoderationWordsFilterList, true);
                  
                  $comment->initData(['content']);
                  $commentContent = $comment->getContent();

                  foreach ($words as $word) {
                    $wordRegexPattern = '/' . $word . '/ui';

                    if (preg_match($wordRegexPattern, $commentContent)) {
                      array_push($commentRiskFactorsDetected, $commentRiskFactors[0]);
                      break;
                    }
                  }
                }
              }
      
              $settingSecurityPremoderationLinksFilterStatus = $CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status');
              if ($settingSecurityPremoderationLinksFilterStatus === 'on' && $settingSecurityPremoderationCreateStatus !== 'on') {
                $comment->initData(['content']);
                $commentContent = $comment->getContent();

                $linkRegexPattern = '/(?:http(?:s)?\:\/\/)?((?:[\w\-]+\.)?(?:[\w\-]+)(?:\.[\w\-]+))/ui';
                if (preg_match($linkRegexPattern, $commentContent, $regexMatches, PREG_OFFSET_CAPTURE)) {
                  $CMSConfigDomain = $CMSCore->configurator->get('domain');

                  foreach ($regexMatches as $regexMatch) {
                    $domainRegexPattern = '/' . $CMSConfigDomain . '/ui';

                    if (!preg_match($domainRegexPattern, $regexMatch[0])) {
                      array_push($commentRiskFactorsDetected, $commentRiskFactors[1]);
                      break;
                    }
                  }
                }
              }

              if ($settingSecurityPremoderationCreateStatus == 'on') {
                array_push($commentRiskFactorsDetected, $commentRiskFactors[2]);
              }

              if (!empty($commentRiskFactorsDetected)) {
                $commentData['metadata']['isHidden'] = true;
                $commentData['metadata']['hiddenReason'] = sprintf('{LANG:COMMENT_DETECTED_FROM_PREMODERATION_FILTER} (%s).', implode(', ', $commentRiskFactorsDetected));
              }
            }

            if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE) && $comment->getAuthorID() === $clientUser->getID()) {
              $commentData['content'] = $commentContent;
              $commentData['metadata']['parentID'] = $commentParentID;
              $commentPatchingIsAllowed = true;
            } elseif ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) {
              $commentData['content'] = $commentContent;
              $commentData['metadata']['parentID'] = $commentParentID;
              $commentPatchingIsAllowed = true;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_MAX_CHARACTERS'));
            $commentPatchingIsAllowed = false;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_FEW_CHARACTERS'));
          $commentPatchingIsAllowed = false;
        }
      }

      if (isset($_PATCH['comment_is_hidden']) || isset($_PATCH['comment_hidden_reason'])) {
        $commentIsHidden = $_PATCH['comment_is_hidden'];
        $commentHiddenReason = $_PATCH['comment_hidden_reason'] ?? '';
        $commentHiddenReason = htmlspecialchars(strip_tags($commentHiddenReason));

        if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) {
          $commentData['metadata']['isHidden'] = $commentIsHidden === 'on' ? true : false;
          $commentData['metadata']['hiddenReason'] = $commentHiddenReason;
          $commentPatchingIsAllowed = true;
        }
      }

      if (isset($_PATCH['comment_rating_vote'])) {
        $commentRatingVote = $_PATCH['comment_rating_vote'];

        if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE) && $comment->getAuthorID() !== $clientUser->getID()) {
          $commentRatingVoters = $comment->getRatingVoters();
          $clientUserID = $clientUser->getID();
          $clientUserIDs = (string) $clientUserID;

          $allowVoting = false;
          if (isset($commentRatingVoters[$clientUserIDs])) {
            if ($commentRatingVoters[$clientUserIDs] !== $commentRatingVote) {
              $allowVoting = true;
            } else {
              $allowVoting = false;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_REPEAT_VOTE');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $allowVoting = true;
          }

          if ($allowVoting) {
            $commentData['metadata']['rating_vote'] = [
              'voter_id' => $clientUserID,
              'vote' => $commentRatingVote
            ];
          }

          if ($allowVoting) {
            $commentPatchingIsAllowed = true;
          }
        }
      }
      
      if ($commentPatchingIsAllowed) {
        $isUpdated = (!empty($commentData)) ? $comment->update($commentData) : false;

        if ($isUpdated) {
          $comment = new EntryComment($CMSCore, $commentID);

          $commentInitData = ['metadata'];
          
          if (isset($_PATCH['comment_content'])) array_push($commentInitData, 'content');

          $comment->initData($commentInitData);
          $handlerOutputData['comment']['id'] = $comment->getID();
          $handlerOutputData['comment']['rating'] = $comment->getRating();
          if (isset($_PATCH['comment_content'])) $handlerOutputData['comment']['content'] = $comment->getContent();

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
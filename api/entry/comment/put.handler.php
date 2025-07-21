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

if ($CMSCore->client->isLogged(1)) {
  $clientUser = $CMSCore->client->getUser(1);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  $commentParentID = $_PUT['comment_parent_id'] ?? 0;
  $commentParentID = is_numeric($commentParentID) ? (int) $commentParentID : 0;

  $commentEntryID = $_PUT['comment_entry_id'] ?? 0;
  $commentEntryID = is_numeric($commentEntryID) ? (int) $commentEntryID : 0;

  $commentContent = $_PUT['comment_content'] ?? '';
  $commentContent = trim($commentContent);

  $commentContentLengthMin = 16;
  $commentContentLengthMax = 400;

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE)) {
    if (!empty($commentContent)) {
      if (strlen($commentContent) >= $commentContentLengthMin) {
        if (strlen($commentContent) <= $commentContentLengthMax) {
          if (Entry::existsByID($CMSCore, $commentEntryID)) {
            $comment = EntryComment::create($CMSCore, $commentEntryID, $clientUser->getID(), $commentContent);
            
            if (!is_null($comment)) {
              // Система премодерации не будет проигнорирована, если пользователь является первичным
              // или его группа не является административной или модеративной
              if (!in_array($clientUserGroup->getID(), [1, 2]) && $clientUser->getID() !== 1) {
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
        
                $setting_security_premoderation_links_filter_status = $CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status');
                if ($setting_security_premoderation_links_filter_status === 'on' && $settingSecurityPremoderationCreateStatus !== 'on') {
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

                if ($settingSecurityPremoderationCreateStatus === 'on') {
                  array_push($commentRiskFactorsDetected, $commentRiskFactors[2]);
                }

                if (!empty($commentRiskFactorsDetected)) {
                  $commentData['metadata']['isHidden'] = true;
                  $commentData['metadata']['hiddenReason'] = sprintf('{LANG:COMMENT_DETECTED_FROM_PREMODERATION_FILTER} (%s).', implode(', ', $commentRiskFactorsDetected));
                }
              }

              $commentData['metadata']['parentID'] = $commentParentID;

              if (isset($commentData['metadata'])) {
                if (count($commentData['metadata']) > 0) {
                  $comment->update($commentData);
                }
              }

              $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 1;

              $handlerOutputData['comment'] = [];
              $handlerOutputData['comment']['id'] = $comment->getID();
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_ERROR_NOT_FOUND');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_MAX_CHARACTERS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_FEW_CHARACTERS');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ENTRY_COMMENT_ERROR_EMPTY');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
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

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    $pageStaticName = isset($_PUT['page_static_name']) ? urlencode(htmlentities($_PUT['page_static_name'])) : '';
    $pageStaticCreationAllowed = true;
    $texts = [];

    $CMSLocalesNames = $CMSCore->get_array_locales_names();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $localeName) {
        $CMSLocale = new Locale($CMSCore, $localeName);

        $inputTitleName = 'page_static_title_' . $CMSLocale->get_iso_639_2();
        $textareaDescriptionName = 'page_static_description_' . $CMSLocale->get_iso_639_2();
        $textareaContentName = 'page_static_content_' . $CMSLocale->get_iso_639_2();
        $textareaKeywordsName = 'page_static_keywords_' . $CMSLocale->get_iso_639_2();

        if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT) || array_key_exists($textareaContentName, $_PUT)) {
          if (!array_key_exists($CMSLocale->get_name(), $texts)) $texts[$CMSLocale->get_name()] = [];

          if (array_key_exists($inputTitleName, $_PUT)) {
            $inputValue = $_PUT[$inputTitleName];
            $inputValue = strip_tags($inputValue);
            $inputValue = str_replace('\'', '"', $inputValue);

            $texts[$CMSLocale->get_name()]['title'] = $inputValue;
          }

          if (array_key_exists($textareaDescriptionName, $_PUT)) {
            $textareaValue = $_PUT[$textareaDescriptionName];
            $textareaValue = strip_tags($textareaValue);
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $texts[$CMSLocale->get_name()]['description'] = $textareaValue;
          }

          if (array_key_exists($textareaContentName, $_PUT)) {
            $textareaValue = $_PUT[$textareaContentName];
            $textareaValue = strip_tags($textareaValue, '<table><tr><td><th><b><u><i><hr>');
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $texts[$CMSLocale->get_name()]['content'] = $textareaValue;
          }

          if (array_key_exists($textareaKeywordsName, $_PUT)) {
            $textareaValue = $_PUT[$textareaKeywordsName];
            $textareaValue = strip_tags($textareaValue);
            $textareaValue = str_replace('\'', '"', $textareaValue);
            
            $texts[$CMSLocale->get_name()]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
          }
        }
      }
    }

    if (empty($pageStaticName)) {
      $handlerMessage = $handlerMessage ?? 'Произошла внутренняя ошибка. Техническое наименование страницы не может быть пустым.';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      $pageStaticCreationAllowed = false;
    }

    foreach ($_PUT as $key => $value) {
      if (preg_match('/^page_static\_additional\_field\_([a-z0-9\_]+)$/i', $key, $key_matches, PREG_OFFSET_CAPTURE) && !empty($value)) {
        if (!isset($pageStaticData)) $pageStaticData = [];
        if (!isset($pageStaticData['metadata'])) $pageStaticData['metadata'] = [];
        if (!isset($pageStaticData['metadata']['additionalFields'])) $pageStaticData['metadata']['additionalFields'] = [];
        
        $valueNameParts = explode('_', $key_matches[1][0]);
        foreach ($valueNameParts as $index => $part) {
          if ($index > 0) {
            $valueNameParts[$index] = ucfirst($part);
          }
        }

        if (is_bool($value)) $value = (int)$value;

        $pageStaticData['metadata']['additionalFields'][implode($valueNameParts)] = htmlspecialchars(str_replace('\'', '"', $value));
      }

      if ($key === 'page_static_template_path') {
        $pageStaticData['metadata']['personalTemplatePath'] = htmlspecialchars(str_replace('\'', '"', trim($value)));
      }
    }

    if ($pageStaticCreationAllowed) {
      $clientSession = $CMSCore->client->get_session(2, ['userID']);
      
      $pageStatic = PageStatic::create($CMSCore, $pageStaticName, $clientSession->get_user_id(), $texts);
      if (!is_null($pageStatic)) {
        $pageStatic->init_data(['*']);

        if (isset($pageStaticData)) {
          $pageStatic->update($pageStaticData);
        }

        $handlerOutputData['pageStatic'] = [];
        $handlerOutputData['pageStatic']['id'] = $pageStatic->get_id();

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>
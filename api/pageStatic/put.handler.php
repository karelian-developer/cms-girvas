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
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) {
    $pageStaticName = isset($_PUT['page_static_name']) ? urlencode(htmlentities($_PUT['page_static_name'])) : '';
    $pageStaticCreationAllowed = true;
    $texts = [];

    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $localeName) {
        $CMSLocale = new CMSLocale($CMSCore, $localeName);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();
        
        $CMSLocaleName = $CMSLocale->getName();

        $inputTitleName = 'page_static_title_' . $CMSLocale->getISO639(2);
        $inputSEOTitleName = 'page_static_seo_title_' . $CMSLocale->getISO639(2);
        $textareaDescriptionName = 'page_static_description_' . $CMSLocale->getISO639(2);
        $textareaSEODescriptionName = 'page_static_seo_description_' . $CMSLocale->getISO639(2);
        $textareaContentName = 'page_static_content_' . $CMSLocale->getISO639(2);
        $textareaKeywordsName = 'page_static_keywords_' . $CMSLocale->getISO639(2);

        if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT) || array_key_exists($textareaContentName, $_PUT)) {
          if (!array_key_exists($CMSLocaleName, $texts)) $texts[$CMSLocaleName] = [];

          if (array_key_exists($inputTitleName, $_PUT)) {
            $inputValue = $_PUT[$inputTitleName];
            $inputValue = strip_tags($inputValue);
            $inputValue = str_replace('\'', '"', $inputValue);

            $texts[$CMSLocaleName]['title'] = $inputValue;
          }

          if (array_key_exists($inputSEOTitleName, $_PUT)) {
            $inputValue = $_PUT[$inputSEOTitleName];
            $inputValue = str_replace('\'', '"', $inputValue);

            $texts[$CMSLocaleName]['SEOTitle'] = $inputValue;
          }

          if (array_key_exists($textareaDescriptionName, $_PUT)) {
            $textareaValue = $_PUT[$textareaDescriptionName];
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $texts[$CMSLocaleName]['description'] = $textareaValue;
          }

          if (array_key_exists($textareaSEODescriptionName, $_PUT)) {
            $textareaValue = $_PUT[$textareaSEODescriptionName];
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $texts[$CMSLocaleName]['SEODescription'] = $textareaValue;
          }

          if (array_key_exists($textareaContentName, $_PUT)) {
            $textareaValue = $_PUT[$textareaContentName];
            $textareaValue = str_replace('\'', '"', $textareaValue);

            $texts[$CMSLocaleName]['content'] = $textareaValue;
          }

          if (array_key_exists($textareaKeywordsName, $_PUT)) {
            $textareaValue = $_PUT[$textareaKeywordsName];
            $textareaValue = str_replace('\'', '"', $textareaValue);
            
            $texts[$CMSLocaleName]['keywords'] = preg_split('/\h*[\,]+\h*/', $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
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
      $clientSession = $CMSCore->client->getSession(2, ['userID']);
      
      $pageStatic = PageStatic::create($CMSCore, $pageStaticName, $clientSession->getUserID(), $texts);
      if (!is_null($pageStatic)) {
        $pageStatic->initData(['*']);

        if (isset($_PUT['page_static_published_timestamp'])) {
          $pageStaticData['metadata']['publishedUnixTimestamp'] = strtotime(str_replace('T', ' ', $_PUT['page_static_published_timestamp']));
          $pageStaticData['metadata']['isPublished'] = 1;
        }

        if (isset($pageStaticData)) {
          $pageStatic->update($pageStaticData);
        }

        /** @var CMSReport Новый отчет */
        $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED, [
          'clientIP' => $CMSCore->client->getIPAddress(),
          'pageID' => $pageStatic->getID()
        ]);

        $handlerOutputData['pageStatic'] = [];
        $handlerOutputData['pageStatic']['id'] = $pageStatic->getID();

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
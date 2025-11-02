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

use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Form as Form;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT)) {
    $formName = (isset($_PUT['form_name'])) ? urlencode(htmlentities($_PUT['form_name'])) : '';
    
    $formMethodID = $_PUT['form_method_id'] ?? 0;
    $formMethodID = (is_numeric($_PUT['form_method_id'])) ? (int)$_PUT['form_method_id'] : 0;
    
    $formAction = $_PUT['form_action'] ?? '';

    $texts = [];

    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
    if (count($CMSLocalesNames) > 0) {
      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new  CMSLocale($CMSCore, $name);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();

        $CMSLocaleName = $CMSLocale->getName();

        $inputTitleName = 'form_title_' . $CMSLocale->getISO639(2);
        $textareaDescriptionName = 'form_description_' . $CMSLocale->getISO639(2);

        if (array_key_exists($inputTitleName, $_PUT) || array_key_exists($textareaDescriptionName, $_PUT)) {
          if (!array_key_exists($CMSLocaleName, $texts)) $texts[$CMSLocaleName] = [];

          if (array_key_exists($inputTitleName, $_PUT)) $texts[$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$inputTitleName]));
          if (array_key_exists($textareaDescriptionName, $_PUT)) $texts[$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$textareaDescriptionName]));
        }
      }
    }

    $metadata = [];
    $metadata['methodID'] = $formMethodID;
    $metadata['action'] = $formAction;

    $form = Form::create($CMSCore, $formName, $texts, $elements, $metadata);
    if (!is_null($form)) {
      $handlerOutputData['form'] = [];
      $handlerOutputData['form']['id'] = $form->getID();

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
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
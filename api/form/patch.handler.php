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

use \core\PHPLibrary\Form as Form;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT)) {
    $formID = (isset($_PATCH['form_id'])) ? $_PATCH['form_id'] : 0;
    $formID = (is_numeric($formID)) ? (int)$formID : 0;

    if (Form::existsByID($CMSCore, $formID)) {
      $form = new Form($CMSCore, $formID);
      $form->initData(['elements']);

      $formData = [];

      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $name) {
          $CMSLocale = new CMSLocale($CMSCore, $name);
          $CMSLocale->setTypeName('handler');
          $CMSLocale->initPathes();

          $CMSLocaleName = $CMSLocale->getName();

          $inputTitleName = 'form_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'form_description_' . $CMSLocale->getISO639(2);

          if (array_key_exists($inputTitleName, $_PATCH) || array_key_exists($textareaDescriptionName, $_PATCH)) {
            if (!array_key_exists('texts', $formData)) $formData['texts'] = [];
            if (!array_key_exists($CMSLocaleName, $formData['texts'])) $formData['texts'][$CMSLocaleName] = [];

            if (array_key_exists($inputTitleName, $_PATCH)) $formData['texts'][$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$inputTitleName]));
            if (array_key_exists($textareaDescriptionName, $_PATCH)) $formData['texts'][$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$textareaDescriptionName]));
          }

          $formElementTitles = isset($_PATCH['form_element_title']) ? $_PATCH['form_element_title'] : [];
          $formElementDescriptions = isset($_PATCH['form_element_description']) ? $_PATCH['form_element_description'] : [];
          $formElementPlaceholders = isset($_PATCH['form_element_placeholder']) ? $_PATCH['form_element_placeholder'] : [];
          $formElementTypes = isset($_PATCH['form_element_type']) ? $_PATCH['form_element_type'] : [];
          $formElementNames = isset($_PATCH['form_element_name']) ? $_PATCH['form_element_name'] : [];

          if (count($formElementTitles) > 0) {
            if (!array_key_exists('elements', $formData)) $formData['elements'] = $form->getElements();
            
            for ($i = 0; $i < count($formElementTitles); $i++) {
              $elements = $form;

              if (!isset($formData['elements'][$i])) $formData['elements'][$i] = [];
              if (!isset($formData['elements'][$i]['texts'][$CMSLocaleName])) $formData['elements'][$i]['texts'][$CMSLocaleName] = [];

              $formData['elements'][$i]['number'] = $i + 1;
              $formData['elements'][$i]['type'] = $formElementTypes[$i];
              $formData['elements'][$i]['name'] = $formElementNames[$i];
              $formData['elements'][$i]['texts'][$CMSLocaleName] = [
                'title' => $formElementTitles[$i],
                'description' => $formElementDescriptions[$i],
                'placeholder' => $formElementPlaceholders[$i]
              ];
            }
          }
        }
      }

      if (isset($_PATCH['form_name'])) $formData['name'] = urlencode(htmlentities($_PATCH['form_name']));
      if (isset($_PATCH['form_method_id'])) $formData['metadata']['methodID'] = $_PATCH['form_method_id'];

      $isUpdated = $form->update($formData);

      if ($isUpdated) {
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FEED_ERROR_NOT_FOUND');
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
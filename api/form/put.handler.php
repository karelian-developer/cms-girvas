<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
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

  if ($clientUserGroup->permissionCheck(
    $clientUserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT
  )) {
    
    $formName = isset($_PUT['form_name'])
      ? urlencode(htmlentities($_PUT['form_name']))
      : '';
    
    $formMethodID = $_PUT['form_method_id'] ?? 0;
    $formMethodID = is_numeric($_PUT['form_method_id'])
      ? (int)$_PUT['form_method_id']
      : 0;

    if (isset($_PUT['form_notification_telegram_chats_ids'])) {

      $formTelegramChatsIDs = explode(',', $_PUT['form_notification_telegram_chats_ids']);
      
      foreach ($formTelegramChatsIDs as $index => $id) {

        if (!is_numeric($id)) {
          unset($formTelegramChatsIDs[$index]);
          continue;
        }

        $formTelegramChatsIDs[$index] = trim($id);
      }
    }

    if (isset($_PUT['form_notification_max_chats_ids'])) {

      $formMaxChatsIDs = explode(',', $_PUT['form_notification_max_chats_ids']);
      
      foreach ($formMaxChatsIDs as $index => $id) {

        if (!is_numeric($id)) {
          unset($formMaxChatsIDs[$index]);
          continue;
        }

        $formMaxChatsIDs[$index] = trim($id);
      }
    }

    $formTelegramChatsIDs = $formTelegramChatsIDs ?? [];
    $formMaxChatsIDs = $formMaxChatsIDs ?? [];
    
    $formAction = $_PUT['form_action'] ?? '';

    $texts = [];
    $elements = [];

    if (array_key_exists('form_element_type', $_PUT)) {

      foreach ($_PUT['form_element_type'] as $elementIndex => $elementTypeName) {

        $elements[$elementIndex] = [];
        $elements[$elementIndex]['type'] = $elementTypeName;
        $elements[$elementIndex]['texts'] = [];

        $elementName = $_PUT['form_element_name'][$elementIndex] ?? '';
        $elementSequenceNumber = $_PUT['form_element_sequence_number'][$elementIndex] ?? 0;
        $elementRequired = $_PUT['form_element_required'][$elementIndex] ?? null;

        if ($elementTypeName === 'select') {
          $elements[$elementIndex]['options'] = [];
        }

        if ($elementName !== null) {
          $elements[$elementIndex]['name'] = trim($elementName);
        }

        if ($elementSequenceNumber !== null) {
          $elements[$elementIndex]['sequenceNumber'] = is_numeric($elementSequenceNumber) ? $elementSequenceNumber : 0;
        }

        $elements[$elementIndex]['required'] = $elementRequired !== null;
      }
    }

    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();

    if (count($CMSLocalesNames) > 0) {

      foreach ($CMSLocalesNames as $index => $name) {
        $CMSLocale = new  CMSLocale($CMSCore, $name);
        $CMSLocale->setTypeName('handler');
        $CMSLocale->initPathes();

        $CMSLocaleName = $CMSLocale->getName();
        $commonLocale = $_PUT['common_locale'];

        $inputTitleName = 'form_title_' . $CMSLocale->getISO639(2);
        $textareaDescriptionName = 'form_description_' . $CMSLocale->getISO639(2);

        if (
          array_key_exists($inputTitleName, $_PUT) ||
          array_key_exists($textareaDescriptionName, $_PUT)
        ) {
          if (!array_key_exists($commonLocale, $texts)) {
            $texts[$commonLocale] = [];
          }

          if (array_key_exists($inputTitleName, $_PUT)) {
            $texts[$commonLocale]['title'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$inputTitleName]));
          }

          if (array_key_exists($textareaDescriptionName, $_PUT)) {
            $texts[$commonLocale]['description'] = htmlspecialchars(str_replace('\'', '"', $_PUT[$textareaDescriptionName]));
          }
        }

        if (array_key_exists('form_element_type', $_PUT)) {

          foreach ($_PUT['form_element_type'] as $elementIndex => $elementTypeName) {

            $elementTitle = $_PUT['form_element_title'][$elementIndex] ?? null;
            $elementDescription = $_PUT['form_element_description'][$elementIndex] ?? null;
            $elementPlaceholder = $_PUT['form_element_placeholder'][$elementIndex] ?? null;

            if ($elementTitle !== null) {
              $elements[$elementIndex]['texts'][$commonLocale]['title'] = htmlspecialchars(str_replace('\'', '"', trim($_PUT['form_element_title'][$elementIndex])));
            }

            if ($elementDescription !== null) {
              $elements[$elementIndex]['texts'][$commonLocale]['description'] = htmlspecialchars(str_replace('\'', '"', trim($_PUT['form_element_description'][$elementIndex])));
            }

            if ($elementPlaceholder !== null) {
              $elements[$elementIndex]['texts'][$commonLocale]['placeholder'] = htmlspecialchars(str_replace('\'', '"', trim($_PUT['form_element_placeholder'][$elementIndex])));
            }

            $elements[$elementIndex]['options'] = [];
            $elementName = $elements[$elementIndex]['name'];

            foreach ($_PUT['form_element_select_' . $elementName . '_option_label'] as $optionIndex => $optionLabel) {
              $optionValue = $_PUT['form_element_select_' . $elementName . '_option_value'][$optionIndex];

              $elements[$elementIndex]['options'][$optionIndex] = [];
              $elements[$elementIndex]['options'][$optionIndex]['texts'] = [];
              $elements[$elementIndex]['options'][$optionIndex]['texts'][$commonLocale] = [];
              
              $elements[$elementIndex]['options'][$optionIndex]['texts'][$commonLocale]['label'] = $optionLabel;
              $elements[$elementIndex]['options'][$optionIndex]['value'] = $optionValue;
            }
          }
        }
      }
    }

    $metadata = [];
    $metadata['methodID'] = $formMethodID;
    $metadata['action'] = $formAction;
    $metadata['telegramChatsIDs'] = $formTelegramChatsIDs;
    $metadata['maxChatsIDs'] = $formMaxChatsIDs;

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
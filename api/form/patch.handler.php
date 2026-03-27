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

use \core\PHPLibrary\Form as Form;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck(
    $clientUserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT
  )) {
    $formID = isset($_PATCH['form_id']) ? $_PATCH['form_id'] : 0;
    $formID = is_numeric($formID) ? (int)$formID : 0;

    if (Form::existsByID($CMSCore, $formID)) {
      $form = new Form($CMSCore, $formID);
      $form->initData(['elements']);

      $formData = [];
      $formElements = $form->getElements();
      error_log(print_r($formElements, true));

      $formElements = array_filter($formElements, function($element) use ($_PATCH) {
        return isset($_PATCH['form_element_name']) && in_array($element['name'], $_PATCH['form_element_name']);
      });

      $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
      if (count($CMSLocalesNames) > 0) {
        foreach ($CMSLocalesNames as $index => $name) {
          $CMSLocale = new CMSLocale($CMSCore, $name);
          $CMSLocale->setTypeName('handler');
          $CMSLocale->initPathes();

          $CMSLocaleName = $CMSLocale->getName();
          $commonLocale = $_PATCH['common_locale'];

          $inputTitleName = 'form_title_' . $CMSLocale->getISO639(2);
          $textareaDescriptionName = 'form_description_' . $CMSLocale->getISO639(2);

          if (
            array_key_exists($inputTitleName, $_PATCH) ||
            array_key_exists($textareaDescriptionName, $_PATCH)
          ) {
            if (!array_key_exists('texts', $formData)) {
              $formData['texts'] = [];
            }

            if (!array_key_exists($CMSLocaleName, $formData['texts'])) {
              $formData['texts'][$CMSLocaleName] = [];
            }

            if (array_key_exists($inputTitleName, $_PATCH)) {
              $formData['texts'][$CMSLocaleName]['title'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$inputTitleName]));
            }

            if (array_key_exists($textareaDescriptionName, $_PATCH)) {
              $formData['texts'][$CMSLocaleName]['description'] = htmlspecialchars(str_replace('\'', '"', $_PATCH[$textareaDescriptionName]));
            }
          }

          $formElementTitles = isset($_PATCH['form_element_title'])
            ? $_PATCH['form_element_title']
            : [];

          $formElementDescriptions = isset($_PATCH['form_element_description'])
            ? $_PATCH['form_element_description']
            : [];

          $formElementPlaceholders = isset($_PATCH['form_element_placeholder'])
            ? $_PATCH['form_element_placeholder']
            : [];

          $formElementTypes = isset($_PATCH['form_element_type'])
            ? $_PATCH['form_element_type']
            : [];

          $formElementRequired = isset($_PATCH['form_element_required'])
            ? $_PATCH['form_element_required']
            : [];

          $formElementNames = isset($_PATCH['form_element_name'])
            ? $_PATCH['form_element_name']
            : [];
            
          $formElementSequenceNumbers = isset($_PATCH['form_element_sequence_number'])
            ? $_PATCH['form_element_sequence_number']
            : [];

          if (count($formElementTitles) > 0) {

            for ($i = 0; $i < count($formElementTitles); $i++) {
              $elements = $form;

              if (!isset($formElements[$i])) $formElements[$i] = [];
              if (!isset($formElements[$i]['texts'][$CMSLocaleName])) $formElements[$i]['texts'][$CMSLocaleName] = [];

              $formElements[$i]['number'] = $i + 1;
              $formElements[$i]['type'] = $formElementTypes[$i];
              $formElements[$i]['required'] = isset($formElementRequired[$i]) ? true : false;
              $formElements[$i]['name'] = trim($formElementNames[$i]);
              $formElements[$i]['sequenceNumber'] = is_numeric($formElementSequenceNumbers[$i])
                ? $formElementSequenceNumbers[$i]
                : 0;

              if ($formElements[$i]['type'] === 'select') {
                $formElements[$i]['options'] = [];
              }
              
              if ($CMSLocaleName === $commonLocale) {
                
                $formElementTitlesTrimmed = trim($formElementTitles[$i]);
                $formElementDescriptionsTrimmed = trim($formElementDescriptions[$i]);
                $formElementPlaceholdersTrimmed = trim($formElementPlaceholders[$i]);

                $formElements[$i]['texts'][$CMSLocaleName] = [
                  'title' => htmlspecialchars(str_replace('\'', '"', $formElementTitlesTrimmed)),
                  'description' => str_replace('\'', '"', $formElementDescriptionsTrimmed),
                  'placeholder' => str_replace('\'', '"', $formElementPlaceholdersTrimmed)
                ];
              }
              
              if (isset($formElements[$i]['options'])) {
                $elementName = $formElements[$i]['name'];
                
                foreach ($_PATCH['form_element_select_' . $elementName . '_option_label'] as $optionIndex => $optionLabel) {
                  $optionValue = $_PATCH['form_element_select_' . $elementName . '_option_value'][$optionIndex];
                  
                  if (!isset($formElements[$i]['options'][$optionIndex])) {
                    $formElements[$i]['options'][$optionIndex] = [];
                  }

                  if (!isset($formElements[$i]['options'][$optionIndex]['texts'])) {
                    $formElements[$i]['options'][$optionIndex]['texts'] = [];
                  }

                  $formElements[$i]['options'][$optionIndex]['texts'][$commonLocale] = [];
                  $formElements[$i]['options'][$optionIndex]['texts'][$commonLocale]['label'] = $optionLabel;
                  $formElements[$i]['options'][$optionIndex]['value'] = $optionValue;
                }
              }
            }
          }
        }
      }

      if (isset($_PATCH['form_name'])) {
        $formData['name'] = urlencode(htmlentities($_PATCH['form_name']));
      }

      if (isset($_PATCH['form_method_id'])) {
        $formData['metadata']['methodID'] = $_PATCH['form_method_id'];
      }

      if (isset($_PATCH['form_action'])) {
        $formData['metadata']['action'] = $_PATCH['form_action'];
      }

      if (isset($_PATCH['form_notification_telegram_chats_ids'])) {

        $formTelegramChatsIDs = explode(',', $_PATCH['form_notification_telegram_chats_ids']);
        
        foreach ($formTelegramChatsIDs as $index => $id) {

          if (!is_numeric($id)) {
            unset($formTelegramChatsIDs[$index]);
            continue;
          }

          $formTelegramChatsIDs[$index] = trim($id);
        }
      }

      if (isset($_PATCH['form_notification_max_chats_ids'])) {

        $formMaxChatsIDs = explode(',', $_PATCH['form_notification_max_chats_ids']);
        
        foreach ($formMaxChatsIDs as $index => $id) {

          if (!is_numeric($id)) {
            unset($formMaxChatsIDs[$index]);
            continue;
          }

          $formMaxChatsIDs[$index] = trim($id);
        }
      }

      $formData['metadata']['telegramChatsIDs'] = $formTelegramChatsIDs ?? [];
      $formData['metadata']['maxChatsIDs'] = $formMaxChatsIDs ?? [];

      $formData['elements'] = $formElements;
      $isUpdated = $form->update($formData);

      if ($isUpdated) {
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FORM_ERROR_NOT_FOUND');
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
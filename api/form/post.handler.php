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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
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
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\SystemCore\Notifier as CMSNotifier;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\User\Consent as UserConsent;

$formName = $CMSCore->urlp->getPath(2);

if (Form::existsByName($CMSCore, $formName)) {

  $form = Form::getByName($CMSCore, $formName);
  $form->initData(['name', 'metadata', 'elements', 'texts']);
  $formLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

  $formName = $form->getName();
  $formData = [];

  foreach($_POST as $POSTDataKey => $POSTData) {
    if (preg_match(
      '/^' . $formName . '_([a-z0-9_]+)$/',
      $POSTDataKey,
      $matches,
      PREG_OFFSET_CAPTURE
    )) {
      $formFieldName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $matches[1][0]))));
      $formData[$formFieldName] = $POSTData;
    }
  };

  $formSendedDatetime = date('Y-m-d H:i', time());
  $formSendedAuthorIP = $CMSCore->client->getRealIPAddress();

  $result = $form->saveData($formData);

  if ($result) {

    // ============================================================
    // ЛОГИРОВАНИЕ ОТПРАВКИ ФОРМЫ (152-ФЗ)
    // Фиксируем факт получения ПДн через форму.
    // ВАЖНО: создаём ДО согласий, чтобы получить $formReportID.
    // ============================================================
    $formTitle = $form->getTitle($formLocale);
    $formID = $form->getID();

    // Собираем названия полей (без значений ПДн!)
    $fieldNames = [];
    foreach ($formData as $fieldName => $value) {
      $fieldNames[] = $fieldName;
    }

    $formReport = CMSReport::create(
      $CMSCore,
      CMSReport::REPORT_TYPE_ID_AP_FORM_CREATED,
      [
        'formID' => $formID,
        'formName' => $formName,
        'formTitle' => $formTitle,
        'fields' => $fieldNames,
        'ip' => $formSendedAuthorIP
      ]
    );

    $formReportID = $formReport !== null ? $formReport->getID() : 0;

    // ============================================================
    // СОГЛАСИЯ (152-ФЗ)
    // Фиксируем факты согласия по каждому consent-элементу.
    // ============================================================
    $consentElements = [];
    foreach ($form->getElements() as $element) {
      if (($element['type'] ?? '') === 'consent') {
        $consentElements[] = $element;
      }
    }

    foreach ($consentElements as $element) {
      // Имя поля в $formData — camelCase (как формируется выше в парсере $_POST)
      $fieldName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $element['name']))));

      // Согласие фиксируем только если чекбокс отмечен
      if (empty($formData[$fieldName])) {
        continue;
      }

      $documentKey = $element['documentKey'] ?? '';
      if (empty($documentKey)) {
        continue;
      }

      $document = PageStatic::getByName($CMSCore, $documentKey);
      if ($document === null) {
        continue;
      }

      $document->initData(['id', 'name', 'texts', 'metadata']);
      if (!$document->isLegalDocument()) {
        continue;
      }

      $currentVersion = $document->getCurrentVersion($formLocale);
      if ($currentVersion === null) {
        continue;
      }

      UserConsent::give(
        $CMSCore,
        0,
        $form->getID(),
        $formReportID,
        $document->getID(),
        $currentVersion->getVersion(),
        $formLocale,
        $formSendedAuthorIP,
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        'form'
      );

      $documentTitles = [];
      foreach ($CMSCore->getArrayLocalesNames() as $localeName) {
        $documentTitles[$localeName] = $document->getTitle($localeName);
      }

      CMSReport::create(
        $CMSCore,
        CMSReport::REPORT_TYPE_ID_BASE_CONSENT_GIVEN,
        [
          'formID' => $form->getID(),
          'formReportID' => $formReportID,
          'pageStaticID' => $document->getID(),
          'documentKey' => $documentKey,
          'documentTitles' => $documentTitles,
          'documentVersion' => $currentVersion->getVersion(),
          'locale' => $formLocale,
          'ip' => $formSendedAuthorIP
        ]
      );
    }

    // ============================================================
    // УВЕДОМЛЕНИЯ (Telegram, Max)
    // ============================================================
    $notifierTelegramChatsIDs = $form->getTelegramChatsIDs();
    $notifierTelegramThreatsIDs = $form->getTelegramThreatsIDs();
    $notifierTelegramChannelsIDs = $form->getTelegramChannelsIDs();

    $notifierMaxChatsIDs = $form->getMaxChatsIDs();

    $notifierTelegramChatsCount = count($notifierTelegramChatsIDs);
    $notifierTelegramThreatsCount = count($notifierTelegramThreatsIDs);
    $notifierTelegramChannelsCount = count($notifierTelegramChannelsIDs);

    $notifierMaxChatsCount = count($notifierMaxChatsIDs);

    if (
      $notifierTelegramChatsCount > 0 ||
      $notifierTelegramThreatsCount > 0 ||
      $notifierTelegramChannelsCount > 0
    ) {

      $CMSTelegramNotifier = CMSNotifier::create($CMSCore, 'telegram');

      if ($notifierTelegramChatsCount > 0) {
        
        $formDataFormated = [];
        $formElements = $form->getElements();
        $formDataFromDb = $form->getData();
        $formTitle = $form->getTitle($formLocale);

        foreach($_POST as $POSTDataKey => $POSTData) {

          foreach ($formElements as $elementIndex => $elementData) {
            $elementName = $elementData['name'];
            
            if ($POSTDataKey === $formName . '_' . $elementName) {
              $elementTitle = isset($elementData['texts'][$formLocale]['title'])
                ? $elementData['texts'][$formLocale]['title']
                : $elementName;

              if ($elementData['type'] === 'select') {
                $selectedLabel = '';
                foreach ($elementData['options'] as $option) {
                  if ($option['value'] === $POSTData) {
                    $selectedLabel = isset($option['texts'][$formLocale]['label']) 
                      ? $option['texts'][$formLocale]['label'] 
                      : $option['value'];
                    break;
                  }
                }
                $elementValue = $selectedLabel;
              } else {
                $elementValue = $POSTData;
              }
              
              $formDataFormated[] = $elementTitle . ': ' . $elementValue;
            }
          }
        }

        $CMSTelegramNotifierMessage = "📊 *" . $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_SENDED_TITLE') . "*\n\n";
        $CMSTelegramNotifierMessage .= "*" . $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_LABEL') . ":* " . $formTitle . "\n";
        $CMSTelegramNotifierMessage .= "*" . $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_FROM_SITE_LABEL') . ":* " . $CMSCore->getSiteURL() . "\n\n";
        $CMSTelegramNotifierMessage .= implode("\n", $formDataFormated) . "\n\n";
        $CMSTelegramNotifierMessage .= "*". $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_DATE_LABEL') .":* " . $formSendedDatetime . "\n";
        $CMSTelegramNotifierMessage .= "*". $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_IP_LABEL') .":* " . $formSendedAuthorIP . "\n\n";
        $CMSTelegramNotifierMessage .= sprintf($CMSCore->locale->getSingleValueByKey('API_NOTIFIER_COPYRIGHT_LABEL'), $CMSCore::CMS_TITLE . ' ' . $CMSCore::CMS_VERSION);

        $CMSTelegramNotifier->setMessage($CMSTelegramNotifierMessage);
        $CMSTelegramNotifierKey = $CMSCore->configurator->getNotifierKey('telegram');

        foreach ($notifierTelegramChatsIDs as $index => $id) {
          $CMSTelegramNotifier->setChatID($id);
          $CMSTelegramNotifier->send($CMSTelegramNotifierKey);
          usleep(1000);
        }
      }
    }

    if ($notifierMaxChatsCount > 0) {

      $CMSMaxNotifier = CMSNotifier::create($CMSCore, 'max');

      if ($notifierMaxChatsCount > 0) {
        
        $formDataFormated = [];
        $formElements = $form->getElements();
        $formDataFromDb = $form->getData();
        $formTitle = $form->getTitle($formLocale);

        foreach($_POST as $POSTDataKey => $POSTData) {

          foreach ($formElements as $elementIndex => $elementData) {
            $elementName = $elementData['name'];
            
            if ($POSTDataKey === $formName . '_' . $elementName) {
              $elementTitle = isset($elementData['texts'][$formLocale]['title'])
                ? $elementData['texts'][$formLocale]['title']
                : $elementName;

              if ($elementData['type'] === 'select') {
                $selectedLabel = '';
                foreach ($elementData['options'] as $option) {
                  if ($option['value'] === $POSTData) {
                    $selectedLabel = isset($option['texts'][$formLocale]['label']) 
                      ? $option['texts'][$formLocale]['label'] 
                      : $option['value'];
                    break;
                  }
                }
                $elementValue = $selectedLabel;
              } else {
                $elementValue = $POSTData;
              }
              
              $formDataFormated[] = $elementTitle . ': ' . $elementValue;
            }
          }
        }

        $CMSMaxNotifierMessage = "📊 " . $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_SENDED_TITLE') . "\n\n";
        $CMSMaxNotifierMessage .= $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_LABEL') . ": " . $formTitle . "\n";
        $CMSMaxNotifierMessage .= $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_FROM_SITE_LABEL') . ": " . $CMSCore->getSiteURL() . "\n\n";
        $CMSMaxNotifierMessage .= implode("\n", $formDataFormated) . "\n\n";
        $CMSMaxNotifierMessage .= $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_DATE_LABEL') .": " . $formSendedDatetime . "\n";
        $CMSMaxNotifierMessage .= $CMSCore->locale->getSingleValueByKey('API_NOTIFIER_CUSTOM_FORM_IP_LABEL') .": " . $formSendedAuthorIP . "\n\n";
        $CMSMaxNotifierMessage .= sprintf($CMSCore->locale->getSingleValueByKey('API_NOTIFIER_COPYRIGHT_LABEL'), $CMSCore::CMS_TITLE . ' ' . $CMSCore::CMS_VERSION);

        $CMSMaxNotifier->setMessage($CMSMaxNotifierMessage);
        $CMSMaxNotifierKey = $CMSCore->configurator->getNotifierKey('max');

        foreach ($notifierMaxChatsIDs as $index => $id) {
          $CMSMaxNotifier->setChatID($id);
          $CMSMaxNotifier->send($CMSMaxNotifierKey);
          usleep(1000);
        }
      }
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FORM_ERROR_NOT_FOUND');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
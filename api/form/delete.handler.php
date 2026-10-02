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
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT)) {
    $formID = $_DELETE['form_id'] ?? 0;
    $formDataID = $_DELETE['form_data_id'] ?? 0;
    
    $formID = is_numeric($formID) ? (int) $formID : 0;
    $formDataID = is_numeric($formDataID) ? (int) $formDataID : 0;

    $isDeleted = false;

    if ($CMSCore->urlp->getPath(2) === 'data') {
      // ============================================================
      // УДАЛЕНИЕ ДАННЫХ ФОРМЫ
      // ============================================================
      if (Form::existsDataByID($CMSCore, $formDataID)) {
        // Получаем данные перед удалением
        $formData = Form::getDataByID($CMSCore, $formDataID);
        $formDataArray = is_array($formData) ? $formData : [];
        $formIDFromData = $formDataArray['formID'] ?? 0;
        
        // Получаем название формы
        $formTitle = '';
        if ($formIDFromData > 0) {
          try {
            $form = new Form($CMSCore, $formIDFromData);
            $form->initData(['texts']);
            $formTitle = $form->getTitle($CMSCore->locale->getName());
          } catch (\Exception $e) {
            $formTitle = 'unknown';
          }
        }
        
        // ============================================================
        // ЛОГИРОВАНИЕ УДАЛЕНИЯ ДАННЫХ ФОРМЫ (152-ФЗ)
        // ============================================================
        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED,
          [
            'formDataID' => $formDataID,
            'formID' => $formIDFromData,
            'formTitle' => $formTitle,
            'deletedByID' => $clientUser->getID(),
            'deletedByLogin' => $clientUser->getLogin(),
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );
        
        $isDeleted = Form::deleteData($CMSCore, $formDataID);
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FORM_DATA_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      // ============================================================
      // УДАЛЕНИЕ ФОРМЫ
      // ============================================================
      if (Form::existsByID($CMSCore, $formID)) {
        $form = new Form($CMSCore, $formID);
        $form->initData(['name', 'texts']);
        
        $formName = $form->getName();
        // Получаем все языковые версии заголовка
        $formTitles = [];
        $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
        foreach ($CMSLocalesNames as $localeName) {
          $formTitles[$localeName] = $form->getTitle($localeName);
        }

        CMSReport::create(
          $CMSCore,
          CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED,
          [
            'formID' => $formID,
            'formName' => $formName,
            'formTitles' => $formTitles,
            'deletedByID' => $clientUser->getID(),
            'deletedByLogin' => $clientUser->getLogin(),
            'ip' => $CMSCore->client->getIPAddress()
          ]
        );
        
        $isDeleted = $form->delete();
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FORM_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }

    if ($isDeleted) {
      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_DELETE_DATA_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      if (empty($handlerMessage)) {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}
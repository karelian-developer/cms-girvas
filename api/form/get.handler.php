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

if (is_numeric($CMSCore->urlp->getPath(2))) {
  $formID = (int) $CMSCore->urlp->getPath(2);

  if (Form::existsByID($CMSCore, $formID)) {
    $form = new Form($CMSCore, $formID);
    $form->initData(['name', 'texts', 'metadata', 'elements', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $formLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['form'] = [];
    $handlerOutputData['form']['id'] = $form->getID();
    $handlerOutputData['form']['name'] = $form->getName();
    $handlerOutputData['form']['methodID'] = $form->getMethodID();
    $handlerOutputData['form']['action'] = $form->getAction();
    $handlerOutputData['form']['elements'] = $form->getElements();
    $handlerOutputData['form']['title'] = $form->getTitle($formLocale);
    $handlerOutputData['form']['description'] = $form->getDescription($formLocale);
    $handlerOutputData['form']['createdUnixTimestamp'] = $form->getCreatedUnixTimestamp();
    $handlerOutputData['form']['updatedUnixTimestamp'] = $form->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FORM_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}
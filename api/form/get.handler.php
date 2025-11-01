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
    $handlerOutputData['form']['elements'] = $form->getElements();
    $handlerOutputData['form']['title'] = $form->getTitle($formLocale);
    $handlerOutputData['form']['description'] = $form->getDescription($formLocale);
    $handlerOutputData['form']['createdUnixTimestamp'] = $form->getCreatedUnixTimestamp();
    $handlerOutputData['form']['updatedUnixTimestamp'] = $form->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FEED_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}
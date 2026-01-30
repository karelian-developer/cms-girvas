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
use \core\PHPLibrary\SystemCore\Notifier as CMSNotifier;

// регистрационная информация (пароль #1)
// registration info (password #1)
$mrh_login = "garbalo_tech";
$mrh_pass1 = "um220TJTXzN1wOqbf3pd";

// чтение параметров
// read parameters
$out_summ = $_POST["OutSum"];
$inv_id = $_POST["InvId"];
$crc = $_POST["SignatureValue"];

$crc = strtoupper($crc);

$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1:shp_interface=link"));

// проверка корректности подписи
// check signature
if ($my_crc != $crc)
{
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
  $handlerStatusCode = $handlerStatusCode ?? 0;
} else {
  $CMSTelegramNotifier = CMSNotifier::create($CMSCore, 'telegram');

  if ($notifierTelegramChatsCount > 0) {
    
    $formDataFormated = [];
    $formElements = $form->getElements();
    $formData = $form->getData();
    $formTitle = $form->getTitle($formLocale);

    foreach($_POST as $POSTDataKey => $POSTData) {

      foreach ($formElements as $elementIndex => $elementData) {
        $elementName = $elementData['name'];
        
        if ($POSTDataKey === $formName . '_' . $elementName) {
          $elementTitle = isset($elementData['texts'][$formLocale]['title'])
            ? $elementData['texts'][$formLocale]['title']
            : $elementName;

          $formDataFormated[] = '*' . $elementTitle . ':* `' . $POSTData . '`';
        }
      }
    }

    $CMSTelegramNotifierMessage = "📊 *Совершена оплата*\n\n";
    $CMSTelegramNotifierMessage .= "*ID заказа:* " . $$inv_id . "\n";
    $CMSTelegramNotifierMessage .= "*Сумма:* " . $out_summ . "\n\n";
    $CMSTelegramNotifierMessage .= sprintf($CMSCore->locale->getSingleValueByKey('API_NOTIFIER_COPYRIGHT_LABEL'), $CMSCore::CMS_TITLE . ' ' . $CMSCore::CMS_VERSION);

    $CMSTelegramNotifier->setMessage($CMSTelegramNotifierMessage);
    $CMSTelegramNotifierKey = $CMSCore->configurator->getNotifierKey('telegram');

    foreach ([867321986] as $index => $id) {
      $CMSTelegramNotifier->setChatID($id);
      $CMSTelegramNotifier->send($CMSTelegramNotifierKey);
      usleep(1000);
    }
  }

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_POST_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
}
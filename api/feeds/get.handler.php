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

use \core\PHPLibrary\Feed as Feed;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;

if ($CMSCore->urlp->getPath(2) === 'types') {
  $handlerOutputData['feedsTypes'] = [
    ['id' => 1, 'name' => FeedBuilder::getTypeName(1), 'title' => FeedBuilder::getTypeTitle(1)],
    ['id' => 2, 'name' => FeedBuilder::getTypeName(2), 'title' => FeedBuilder::getTypeTitle(2)],
    ['id' => 3, 'name' => FeedBuilder::getTypeName(3), 'title' => FeedBuilder::getTypeTitle(3)]
  ];
}
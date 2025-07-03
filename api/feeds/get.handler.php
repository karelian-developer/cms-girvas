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

use \core\PHPLibrary\Feed as Feed;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;

if ($CMSCore->urlp->getPath(2) === 'types') {
  $handlerOutputData['feedsTypes'] = [
    ['id' => 1, 'name' => FeedBuilder::getTypeName(1), 'title' => FeedBuilder::getTypeTitle(1)],
    ['id' => 2, 'name' => FeedBuilder::getTypeName(2), 'title' => FeedBuilder::getTypeTitle(2)],
    ['id' => 3, 'name' => FeedBuilder::getTypeName(3), 'title' => FeedBuilder::getTypeTitle(3)],
    ['id' => 4, 'name' => FeedBuilder::getTypeName(4), 'title' => FeedBuilder::getTypeTitle(4)]
  ];
}
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

use \core\PHPLibrary\WebChannel as Feed;
use \core\PHPLibrary\WebChannel\Builder as FeedBuilder;

if ($system_core->urlp->get_path(2) == 'types') {
  $handler_output_data['webChannelsTypes'] = [
    ['id' => 1, 'name' => FeedBuilder::get_type_name(1), 'title' => FeedBuilder::get_type_title(1)],
    ['id' => 2, 'name' => FeedBuilder::get_type_name(2), 'title' => FeedBuilder::get_type_title(2)],
    ['id' => 3, 'name' => FeedBuilder::get_type_name(3), 'title' => FeedBuilder::get_type_title(3)],
    //['id' => 4, 'name' => FeedBuilder::get_type_name(4), 'title' => FeedBuilder::get_type_title(4)]
  ];
}

?>
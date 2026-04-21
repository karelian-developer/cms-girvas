<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2023, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\ContentBlock as ContentBlock;
use \core\PHPLibrary\Factories\Content as CMSContent;
use \core\PHPLibrary\NadvoParse as NadvoParse;

if (is_numeric($CMSCore->urlp->getPath(2))) {
  $contentBlockID = $CMSCore->urlp->getPath(2) ?? 0;
  $contentBlockID = is_numeric($contentBlockID) ? (int) $contentBlockID : 0;

  if (ContentBlock::existsByID($CMSCore, $contentBlockID)) {
    $contentBlock = CMSContent::create($CMSCore, 'contentBlock', ['id' => $contentBlockID]);
    $contentBlock->initData(['name', 'texts', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
    $dataLocale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

    $handlerOutputData['contentBlock'] = [];
    $handlerOutputData['contentBlock']['id'] = $contentBlock->getID();
    $handlerOutputData['contentBlock']['name'] = $contentBlock->getName();
    $handlerOutputData['contentBlock']['title'] = $contentBlock->getTitle($dataLocale);
    $handlerOutputData['contentBlock']['description'] = $contentBlock->getDescription($dataLocale);
    $handlerOutputData['contentBlock']['content'] = $contentBlock->getContent($dataLocale);
    $handlerOutputData['contentBlock']['typeID'] = $contentBlock->getTypeID();
    $handlerOutputData['contentBlock']['createdUnixTimestamp'] = $contentBlock->getCreatedUnixTimestamp();
    $handlerOutputData['contentBlock']['updatedUnixTimestamp'] = $contentBlock->getUpdatedUnixTimestamp();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_CONTENT_BLOCK_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}
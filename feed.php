<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Feed as Feed;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (defined('IS_NOT_HACKED')) {
  if ($CMSCore->urlp->getPath(1) !== null) {
    if (Feed::existsByName($CMSCore, $CMSCore->urlp->getPath(1))) {
      http_response_code(200);
      
      $feed = Feed::getByName($CMSCore, $CMSCore->urlp->getPath(1));
      $feed->initData(['name', 'texts', 'typeID', 'entriesCategoryID']);
      
      $feedBuilder = new FeedBuilder($CMSCore, FeedBuilder::getTypeEnum($feed->getTypeID()));
      $localeName = $CMSCore->locale->getName();

      $feedBuilder->setLanguage($localeName);

      $feedBuilder->feed->setTitle($feed->getTitle($localeName));
      $feedBuilder->feed->setDescription($feed->getDescription($localeName));

      $entries = new Entries($CMSCore);
      if ($feed->getEntriesCategoryID() === 0) {
        $feedLink = 'https://' . $CMSCore->configurator->get('domain') . '/entries';
        $feedBuilder->feed->setLink($feedLink);
        $entriesArray = $entries->getAll();
      } else {
        $entriesCategory = new EntryCategory($CMSCore, $feed->getEntriesCategoryID());
        $entriesCategory->initData(['name']);

        $feedLink = 'https://' . $CMSCore->configurator->get('domain') . '/entries/' . $entriesCategory->getName();
        $feedBuilder->feed->setLink($feedLink);
        $entriesArray = $entries->getByCategoryID($feed->getEntriesCategoryID());
      }

      /**
       * @var Parsedown Парсер markdown-разметки
       */
      $parsedown = new Parsedown();
      $parsedown->setSafeMode(true);
      $parsedown->setMarkupEscaped(true);

      foreach ($entriesArray as $entry) {
        $entry->initData(['name', 'metadata', 'texts', 'updatedUnixTimestamp']);

        $entryAuthor = $entry->getAuthor();
        $entryLink = 'https://' . $CMSCore->configurator->get('domain') . '/entry/' . $entry->getName();

        $feedBuilder->feed->addItem([
          'title' => $entry->getTitle($localeName),
          'description' => $entry->getDescription($localeName),
          'content' => $parsedown->text($entry->getContent($localeName)),
          'preview_url' => $entry->getPreviewURL(),
          'link' => $entryLink,
          'pubdate' => $entry->getUpdatedUnixTimestamp(),
          'author' => $entryAuthor !== null ? $entryAuthor->getLogin() : 'User Unknown'
        ]);
      }

      header('Content-Type: text/xml');

      $feedBuilder->assembly();
      echo $feedBuilder->assembled;
    } else {
      http_response_code(404);
    }
  } else {
    http_response_code(404);
  }
}
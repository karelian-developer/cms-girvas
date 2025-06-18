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
  if ($CMSCore->urlp->get_path(1) !== null) {
    if (Feed::exists_by_name($CMSCore, $CMSCore->urlp->get_path(1))) {
      http_response_code(200);
      
      $feed = Feed::get_by_name($CMSCore, $CMSCore->urlp->get_path(1));
      $feed->init_data(['name', 'texts', 'typeID', 'entriesCategoryID']);
      
      $feedBuilder = new FeedBuilder($CMSCore, FeedBuilder::get_type_enum($feed->get_type_id()));
      $localeName = $this->CMSCore->locale->get_name();

      $feedBuilder->set_language($localeName);

      $feedBuilder->feed->set_title($feed->get_title($localeName));
      $feedBuilder->feed->set_description($feed->get_description($localeName));

      $entries = new Entries($CMSCore);
      if (in_array($feed->get_entries_category_id(), [0, 1])) {
        $feedLink = 'https://' . $CMSCore->configurator->get('domain') . '/entries';
        $feedBuilder->feed->set_link($feedLink);
        $entriesArray = $entries->get_all();
      } else {
        $entriesCategory = new EntryCategory($CMSCore, $feed->get_entries_category_id());
        $entriesCategory->init_data(['name']);

        $feedLink = 'https://' . $CMSCore->configurator->get('domain') . '/entries/' . $entriesCategory->get_name();
        $feedBuilder->feed->set_link($feedLink);
        $entriesArray = $entries->get_by_category_id($feed->get_entries_category_id());
      }

      /**
       * @var Parsedown Парсер markdown-разметки
       */
      $parsedown = new Parsedown();
      $parsedown->setSafeMode(true);
      $parsedown->setMarkupEscaped(true);

      foreach ($entriesArray as $entry) {
        $entry->init_data(['name', 'metadata', 'texts', 'updatedUnixTimestamp']);

        $entryAuthor = $entry->get_author();
        $entryLink = 'https://' . $CMSCore->configurator->get('domain') . '/entry/' . $entry->get_name();

        $feedBuilder->feed->add_item([
          'title' => $entry->get_title($localeName),
          'description' => $entry->get_description($localeName),
          'content' => $parsedown->text($entry->get_content($localeName)),
          'preview_url' => $entry->get_preview_url(),
          'link' => $entryLink,
          'pubdate' => $entry->get_updated_unix_timestamp(),
          'author' => $entryAuthor !== null ? $entryAuthor->get_login() : 'User Unknown'
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

?>
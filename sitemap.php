<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Pages as StaticPages;
use \core\PHPLibrary\SitemapBuilder as SitemapBuilder;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (defined('IS_NOT_HACKED')) {
  $CMSConfigurator = $CMSCore->configurator;

  $sitemapBuilder = new SitemapBuilder($CMSCore);
  $entries = new Entries($CMSCore);
  $pagesStatic = new StaticPages($CMSCore);

  $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
  if (count($CMSLocalesNames) > 0) {
    // Перебор всех существующих записей
    foreach ($entries->getAll() as $entry) {
      $entry->initData(['name', 'updatedUnixTimestamp', 'metadata', 'texts']);

      if ($entry->isPublished()) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          if (!empty($entry->getTitle($localeName)) && !empty($entry->getDescription($localeName)) && !empty($entry->getContent($localeName))) {
            $CMSConfigDomain = $CMSConfigurator->get('domain');
            $entryURL = sprintf('https://%s/entry/%s?locale=%s', $CMSConfigDomain, $entry->getName(), $localeName);

            $sitemapBuilder->addURL($entryURL, $entry->getUpdatedUnixTimestamp(), 'weekly', 0.8);
          }
        }
      }
    }

    // Перебор всех существующих статических страниц
    foreach ($pagesStatic->getAll() as $pageStatic) {
      $pageStatic->initData(['name', 'updatedUnixTimestamp', 'metadata', 'texts']);

      if ($pageStatic->isPublished()) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          if (!empty($pageStatic->getTitle($localeName)) && !empty($pageStatic->getDescription($localeName)) && !empty($pageStatic->getContent($localeName))) {
            $CMSConfigDomain = $CMSConfigurator->get('domain');
            $pageStaticURL = sprintf('https://%s/page/%s?locale=%s', $CMSConfigDomain, $pageStatic->getName(), $localeName);

            $sitemapBuilder->addURL($pageStaticURL, $pageStatic->getUpdatedUnixTimestamp(), 'weekly', 0.8);
          }
        }
      }
    }
  }

  header('Content-type: text/xml');

  $sitemapBuilder->assembly();

  http_response_code(200);
  echo $sitemapBuilder->assembled;
}
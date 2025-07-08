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
  $sitemapBuilder = new SitemapBuilder($CMSCore);
  $entries = new Entries($CMSCore);
  $pagesStatic = new StaticPages($CMSCore);

  $CMSLocalesNames = $CMSCore->get_array_locales_names();
  if (count($CMSLocalesNames) > 0) {
    // Перебор всех существующих записей
    foreach ($entries->get_all() as $entry) {
      $entry->init_data(['name', 'updatedUnixTimestamp', 'metadata', 'texts']);

      if ($entry->is_published()) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          if (!empty($entry->get_title($localeName)) && !empty($entry->get_description($localeName)) && !empty($entry->get_content($localeName))) {
            $CMSConfigDomain = $CMSCore->configurator->get('domain');
            $entryURL = sprintf('https://%s/entry/%s?locale=%s', $CMSConfigDomain, $entry->get_name(), $localeName);

            $sitemapBuilder->add_url($entryURL, $entry->get_updated_unix_timestamp(), 'weekly', 0.8);
          }
        }
      }
    }

    // Перебор всех существующих статических страниц
    foreach ($pagesStatic->get_all() as $pageStatic) {
      $pageStatic->init_data(['name', 'updatedUnixTimestamp', 'metadata', 'texts']);

      if ($pageStatic->is_published()) {
        foreach ($CMSLocalesNames as $index => $localeName) {
          if (!empty($pageStatic->get_title($localeName)) && !empty($pageStatic->get_description($localeName)) && !empty($pageStatic->get_content($localeName))) {
            $CMSConfigDomain = $CMSCore->configurator->get('domain');
            $pageStaticURL = sprintf('https://%s/page/%s?locale=%s', $CMSConfigDomain, $pageStatic->get_name(), $localeName);

            $sitemapBuilder->add_url($pageStaticURL, $pageStatic->get_updated_unix_timestamp(), 'weekly', 0.8);
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

?>
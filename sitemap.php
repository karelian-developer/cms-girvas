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
use \core\PHPLibrary\SitemapImagesBuilder as SitemapImagesBuilder;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (defined('IS_NOT_HACKED')) {
  $CMSConfigurator = $CMSCore->configurator;

  $sitemapBuilder = new SitemapBuilder($CMSCore);
  $entries = new Entries($CMSCore);
  $pagesStatic = new StaticPages($CMSCore);

  header('Content-type: text/xml');

  if ($CMSURLP->getPath(1) === 'images') {
    $sitemapImagesBuilder = new SitemapImagesBuilder($CMSCore);

    $count = $sitemapImagesBuilder->loadFromMetadata(
      CMS_ROOT_DIRECTORY . '/uploads/media/metadata.json',
      $CMSCore->getSiteURL()
    );

    $sitemapImagesBuilder->assembly();

    http_response_code(200);
    echo $sitemapImagesBuilder->assembled;
  } else {
    $siteMapConfiguration = $CMSCore->configurator->getOtherCollection('sitemap');
    $CMSLocalesNames = $CMSCore->getArrayLocalesNames();
    if (count($CMSLocalesNames) > 0) {
      $CMSConfigDomain = trim($CMSConfigurator->get('domain'));

      // Перебор всех существующих записей
      foreach ($entries->getAll() as $entry) {
        $entry->initData(['name', 'updatedUnixTimestamp', 'metadata', 'texts']);

        if ($entry->isPublished()) {
          foreach ($CMSLocalesNames as $index => $localeName) {
            if (!empty($entry->getTitle($localeName)) && !empty($entry->getDescription($localeName)) && !empty($entry->getContent($localeName))) {
              $pageURL = sprintf('https://%s/entry/%s?locale=%s', $CMSConfigDomain, $entry->getName(), $localeName);
              $sitemapBuilder->addURL($pageURL, $entry->getUpdatedUnixTimestamp(), 'weekly', 0.8);
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
              $pageURL = sprintf('https://%s/page/%s?locale=%s', $CMSConfigDomain, $pageStatic->getName(), $localeName);
              $sitemapBuilder->addURL($pageURL, $pageStatic->getUpdatedUnixTimestamp(), 'weekly', 0.8);
            }
          }
        }
      }

      if (isset($siteMapConfiguration['customPages'])) {
        $siteMapCustomPages = $siteMapConfiguration['customPages'];
        
        foreach ($siteMapCustomPages as $pageData) {
          if (isset($pageData['URL'])) {
            $pageDataChangefreq = $pageData['changefreq'] ?? 'weekly';
            $pageDataPriority = $pageData['priority'] ?? 0.5;

            foreach ($CMSLocalesNames as $index => $localeName) {
              $pageURL = sprintf('https://%s/%s?locale=%s', $CMSConfigDomain, $pageData['URL'], $localeName);
              $sitemapBuilder->addURL($pageURL, time(), $pageDataChangefreq, $pageDataPriority);
            }
          }
        }
      }

      $archiveBaseURL = sprintf('https://%s/archive', $CMSConfigDomain);
      $sitemapBuilder->addURL($archiveBaseURL, time(), 'weekly', 0.7);

      $availableYears = $entries->getAvailableYears(true);
      foreach ($availableYears as $yearData) {
        $year = (int) $yearData['year'];
        $yearURL = $archiveBaseURL . '?year=' . $year;
        $sitemapBuilder->addURL($yearURL, time(), 'monthly', 0.6);
        
        $availableMonths = $entries->getAvailableMonths($year, true);
        if (!empty($availableMonths)) {
          foreach ($availableMonths as $monthData) {
            $month = (int) $monthData['month'];
            $monthURL = $archiveBaseURL . '?year=' . $year . '&month=' . $month;
            error_log($monthURL);
            $sitemapBuilder->addURL($monthURL, time(), 'monthly', 0.5);
          }
        }
      }

      $sitemapBuilder->assembly();

      http_response_code(200);
      echo $sitemapBuilder->assembled;
    }
  }
}

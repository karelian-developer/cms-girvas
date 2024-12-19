<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (defined('IS_NOT_HACKED')) {
  $sitemap_builder = new \core\PHPLibrary\SitemapBuilder($system_core);
  $entries = new \core\PHPLibrary\Entries($system_core);
  $pages_static = new \core\PHPLibrary\Pages($system_core);

  $cms_locales_names = $system_core->get_array_locales_names();
  if (count($cms_locales_names) > 0) {
    // Перебор всех существующих записей
    foreach ($entries->get_all() as $entry) {
      $entry->init_data(['name', 'updated_unix_timestamp', 'metadata', 'texts']);

      if ($entry->is_published()) {
        foreach ($cms_locales_names as $index => $cms_locale_name) {
          if (!empty($entry->get_title($cms_locale_name)) && !empty($entry->get_description($cms_locale_name)) && !empty($entry->get_content($cms_locale_name))) {
            $site_domain = $system_core->configurator->get('domain');
            $entry_url = sprintf('https://%s/entry/%s?locale=%s', $site_domain, $entry->get_name(), $cms_locale_name);

            $sitemap_builder->add_url($entry_url, $entry->get_updated_unix_timestamp(), 'weekly', 0.8);
          }
        }
      }
    }

    // Перебор всех существующих статических страниц
    foreach ($pages_static->get_all() as $page_static) {
      $page_static->init_data(['name', 'updated_unix_timestamp', 'metadata', 'texts']);

      if ($page_static->is_published()) {
        foreach ($cms_locales_names as $index => $cms_locale_name) {
          if (!empty($page_static->get_title($cms_locale_name)) && !empty($page_static->get_description($cms_locale_name)) && !empty($page_static->get_content($cms_locale_name))) {
            $site_domain = $system_core->configurator->get('domain');
            $page_url = sprintf('https://%s/page/%s?locale=%s', $site_domain, $page_static->get_name(), $cms_locale_name);

            $sitemap_builder->add_url($page_url, $page_static->get_updated_unix_timestamp(), 'weekly', 0.8);
          }
        }
      }
    }
  }

  header('Content-type: text/xml');

  $sitemap_builder->assembly();

  http_response_code(200);
  echo $sitemap_builder->assembled;
}

?>
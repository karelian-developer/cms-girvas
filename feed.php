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
  if (!is_null($system_core->urlp->get_path(1))) {
    if (\core\PHPLibrary\WebChannel::exists_by_name($system_core, $system_core->urlp->get_path(1))) {
      http_response_code(200);
      
      $web_channel = \core\PHPLibrary\WebChannel::get_by_name($system_core, $system_core->urlp->get_path(1));
      $web_channel->init_data(['name', 'texts', 'type_id', 'entries_category_id']);
      
      $web_channel_builder = new \core\PHPLibrary\WebChannel\Builder($system_core, \core\PHPLibrary\WebChannel\Builder::get_type_enum($web_channel->get_type_id()));
      $cms_base_locale_setted_name = $system_core->configurator->get_database_entry_value('base_locale');
      $cms_base_locale_name = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : $cms_base_locale_setted_name;
      $cms_base_locale = new \core\PHPLibrary\SystemCore\Locale($system_core, $cms_base_locale_name);
      if (!$cms_base_locale->exists_file_data_json()) {
        $cms_base_locale_name = $cms_base_locale_setted_name;
      }

      $web_channel_builder->set_language($cms_base_locale_name);

      $web_channel_builder->web_channel->set_title($web_channel->get_title($cms_base_locale_name));
      $web_channel_builder->web_channel->set_description($web_channel->get_description($cms_base_locale_name));

      $entries = new \core\PHPLibrary\Entries($system_core);
      if ($web_channel->get_entries_category_id() == 0 || $web_channel->get_entries_category_id() == 1) {
        $web_channel_builder->web_channel->set_link(sprintf('https://%s/entries', $system_core->configurator->get('domain')));
        $entries_array = $entries->get_all();
      } else {
        $entries_category = new \core\PHPLibrary\EntryCategory($system_core, $web_channel->get_entries_category_id());
        $entries_category->init_data(['name']);

        $web_channel_builder->web_channel->set_link(sprintf('https://%s/entries/%s', $system_core->configurator->get('domain'), $entries_category->get_name()));
        $entries_array = $entries->get_by_category_id($web_channel->get_entries_category_id());
      }

      /**
       * @var Parsedown Парсер markdown-разметки
       */
      $parsedown = new \core\PHPLibrary\Parsedown();
      $parsedown->setSafeMode(true);
      $parsedown->setMarkupEscaped(true);

      foreach ($entries_array as $entry) {
        $entry->init_data(['name', 'metadata', 'texts', 'updated_unix_timestamp']);

        $entry_author = $entry->get_author();

        $web_channel_builder->web_channel->add_item([
          'title' => $entry->get_title($cms_base_locale_name),
          'description' => $entry->get_description($cms_base_locale_name),
          'content' => $parsedown->text($entry->get_content($cms_base_locale_name)),
          'preview_url' => $entry->get_preview_url(),
          'link' => sprintf('https://%s/entry/%s', $system_core->configurator->get('domain'), $entry->get_name()),
          'pubdate' => $entry->get_updated_unix_timestamp(),
          'author' => ($entry_author != null) ? $entry_author->get_login() : 'User Unknown'
        ]);
      }

      header('Content-Type: text/xml');

      $web_channel_builder->assembly();
      echo $web_channel_builder->assembled;
    } else {
      http_response_code(404);
    }
  } else {
    http_response_code(404);
  }
}

?>
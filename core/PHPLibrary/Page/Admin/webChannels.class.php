<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\WebChannels as Feeds;
  use \core\PHPLibrary\WebChannel\Builder as FeedBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageWebChannels implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param Page $page
     */
    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/webChannels.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', '{LANG:PAGE_WEB_CHANNELS_NAVIGATION_INDEX_LABEL}'),
        'NAVIGATION_ITEM_URL' => '/admin',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => '{LANG:PAGE_WEB_CHANNELS_NAVIGATION_WEB_CHANNELS_LABEL}',
        'NAVIGATION_ITEM_URL' => '/admin/webChannels',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => 'navigation-item__link_is-active'
      ]));

      if (!empty($navigations_items_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $feeds_items_assembled_array = [];
      $feeds = new Feeds($this->system_core);
      $feeds_locale_default = $this->system_core->get_cms_locale('admin');

      $feeds_array_objects = $feeds->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $feeds->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($feeds);

      foreach ($feeds_array_objects as $feed_index => $feed_object) {
        $feed_object->init_data(['id', 'name', 'type_id', 'texts', 'created_unix_timestamp', 'updated_unix_timestamp']);

        $feed_created_date_timestamp = date('d.m.Y H:i:s', $feed_object->get_created_unix_timestamp());
        $feed_updated_date_timestamp = date('d.m.Y H:i:s', $feed_object->get_updated_unix_timestamp());

        $feed_id = $feed_object->get_id();
        $feed_title = $feed_object->get_title($feeds_locale_default->get_name());
        $feed_title = strip_tags($feed_title);

        $feed_name = $feed_object->get_name();
        $feed_type_title = FeedBuilder::get_type_title($feed_object->get_type_id());
        $feed_index_current = $feed_index + 1;

        array_push($feeds_items_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/webChannels/tableItem.tpl', [
          'WEB_CHANNEL_ID' => $feed_id,
          'WEB_CHANNEL_INDEX' => $feed_index_current,
          'WEB_CHANNEL_NAME' => $feed_name,
          'WEB_CHANNEL_TITLE' => $feed_title,
          'WEB_CHANNEL_TYPE_TITLE' => $feed_type_title,
          'WEB_CHANNEL_CREATED_DATE_TIMESTAMP' => $feed_created_date_timestamp,
          'WEB_CHANNEL_UPDATED_DATE_TIMESTAMP' => $feed_updated_date_timestamp
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/webChannels.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
        'PAGE_WEB_CHANNELS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'web-channels',
        'ADMIN_PANEL_WEB_CHANNELS_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/webChannels/table.tpl', [
          'ADMIN_PANEL_WEB_CHANNELS_TABLE_ITEMS' => implode($feeds_items_assembled_array)
        ])
      ]);
    }

  }

}

?>
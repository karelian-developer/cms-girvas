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
  use \core\PHPLibrary\Feeds as Feeds;
  use \core\PHPLibrary\Feed\Builder as FeedBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageFeeds implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_FEEDS_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'index' => [
        'name' => 'index',
        'iconName' => 'index',
        'link' => '/',
        'permanent' => true,
        'isActive' => false
      ],
      'feeds' => [
        'name' => 'feeds',
        'iconName' => 'feeds',
        'link' => '/feeds',
        'permanent' => false,
        'isActive' => true
      ],
    ];

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
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/feeds.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

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

        array_push($feeds_items_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/feeds/tableItem.tpl', [
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
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/feeds.tpl', [
        'PAGE_WEB_CHANNELS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'web-channels',
        'ADMIN_PANEL_WEB_CHANNELS_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/feeds/table.tpl', [
          'ADMIN_PANEL_WEB_CHANNELS_TABLE_ITEMS' => implode($feeds_items_assembled_array)
        ])
      ]);
    }

  }

}

?>
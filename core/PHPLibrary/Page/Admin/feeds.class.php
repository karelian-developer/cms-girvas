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

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
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
     * @param SystemCore $CMSCore
     * @param Page $page
     */
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/feeds.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $feedsItemsAssembled = [];
      $feeds = new Feeds($this->CMSCore);
      $feedsLocale = $this->CMSCore->get_cms_locale('admin');
      $feedsLocaleName = $feedsLocale->get_name();

      $feedsObjects = $feeds->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $feeds->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($feeds);

      foreach ($feedsObjects as $index => $object) {
        $object->init_data(['id', 'name', 'type_id', 'texts', 'createdUnixTimestamp', 'updatedUnixTimestamp']);

        $createdUnixTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
        $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

        $feedID = $object->get_id();
        $feedTitle = $object->get_title($feedsLocaleName);
        $feedTitle = strip_tags($feedTitle);

        $feedName = $object->get_name();
        $feedTypeTitle = FeedBuilder::get_type_title($object->get_type_id());
        $indexCurrent = $index + 1;

        array_push($feedsItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/feeds/tableItem.tpl', [
          'WEB_CHANNEL_ID' => $feedID,
          'WEB_CHANNEL_INDEX' => $indexCurrent,
          'WEB_CHANNEL_NAME' => $feedName,
          'WEB_CHANNEL_TITLE' => $feedTitle,
          'WEB_CHANNEL_TYPE_TITLE' => $feedTypeTitle,
          'WEB_CHANNEL_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'WEB_CHANNEL_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/feeds.tpl', [
        'PAGE_WEB_CHANNELS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'web-channels',
        'ADMIN_PANEL_WEB_CHANNELS_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/feeds/table.tpl', [
          'ADMIN_PANEL_WEB_CHANNELS_TABLE_ITEMS' => implode($feedsItemsAssembled)
        ])
      ]);
    }

  }

}

?>
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
  use \core\PHPLibrary\Feed as Feed;
  use \core\PHPLibrary\Feed\Builder as FeedBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageFeed implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_FEED_NAVIGATION_%s_LABEL';

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/feeds',
        'permanent' => true,
        'isActive' => false
      ],
    ];

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/feed.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $feed = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        $feedID = (is_numeric($this->CMSCore->urlp->get_path(2))) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        $feed = (Feed::exists_by_id($this->CMSCore, $feedID)) ? new Feed($this->CMSCore, $feedID) : null;
        
        if (!is_null($feedID)) {
          $feed->init_data(['id', 'name', 'entriesCategoryID', 'typeID', 'texts']);
        }
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/feed.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'web-channel',
        'WEB_CHANNEL_ID' => !is_null($feed) ? $feed->get_id() : 0,
        'WEB_CHANNEL_NAME' => !is_null($feed) ? $feed->get_name() : '',
        'WEB_CHANNEL_TITLE' => !is_null($feed) ? $feed->get_title($localeName) : '',
        'WEB_CHANNEL_DESCRIPTION' => !is_null($feed) ? $feed->get_description($localeName) : '',
        'WEB_CHANNEL_FORM_METHOD' => !is_null($feed) ? 'PATCH' : 'PUT',
      ]);
    }

  }

}

?>
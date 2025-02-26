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

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
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
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/feed.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $web_channel = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $web_channel_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $web_channel = (Feed::exists_by_id($this->system_core, $web_channel_id)) ? new Feed($this->system_core, $web_channel_id) : null;
        
        if (!is_null($web_channel_id)) {
          $web_channel->init_data(['id', 'name', 'entries_category_id', 'type_id', 'texts']);
        }
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/feed.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'web-channel',
        'WEB_CHANNEL_ID' => (!is_null($web_channel)) ? $web_channel->get_id() : 0,
        'WEB_CHANNEL_NAME' => (!is_null($web_channel)) ? $web_channel->get_name() : '',
        'WEB_CHANNEL_TITLE' => (!is_null($web_channel)) ? $web_channel->get_title() : '',
        'WEB_CHANNEL_DESCRIPTION' => (!is_null($web_channel)) ? $web_channel->get_description() : '',
        'WEB_CHANNEL_FORM_METHOD' => (!is_null($web_channel)) ? 'PATCH' : 'PUT',
      ]);
    }

  }

}

?>
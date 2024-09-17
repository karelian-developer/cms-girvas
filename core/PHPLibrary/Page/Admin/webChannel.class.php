<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\WebChannel as WebChannel;
  use \core\PHPLibrary\WebChannel\Builder as WebChannelBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageWebChannel implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/webChannel.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_WEB_CHANNEL_NAVIGATION_BACK_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin/webChannels',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
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

      $web_channel = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $web_channel_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $web_channel = (WebChannel::exists_by_id($this->system_core, $web_channel_id)) ? new WebChannel($this->system_core, $web_channel_id) : null;
        
        if (!is_null($web_channel_id)) {
          $web_channel->init_data(['id', 'name', 'entries_category_id', 'type_id', 'texts']);
        }
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/webChannel.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
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
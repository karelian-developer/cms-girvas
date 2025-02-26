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
  use \core\PHPLibrary\Pages as Pages;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PagePages implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_STATIC_PAGES_NAVIGATION_%s_LABEL';

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
      'pages' => [
        'name' => 'pages',
        'iconName' => 'pages',
        'link' => '/pages',
        'permanent' => false,
        'isActive' => true
      ],
    ];

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

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/pages.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $pages_static_table_items_assembled_array = [];
      $pages_static = new Pages($this->system_core);
      $pages_static_locale_default = $this->system_core->get_cms_locale('admin');

      $pages_static_array_objects = $pages_static->get_all([
        'limit' => [$pagination_items_on_page, $pagination_item_current * $pagination_items_on_page]
      ]);

      $pagination = new Pagination($this->system_core, $pages_static->get_count_total(), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      unset($entries);

      $page_static_number = 1;
      foreach ($pages_static_array_objects as $page_static_object) {
        $page_static_object->init_data(['id', 'texts', 'name', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);

        $page_static_created_date_timestamp = date('d.m.Y H:i:s', $page_static_object->get_created_unix_timestamp());
        $page_static_published_date_timestamp = date('d.m.Y H:i:s', $page_static_object->get_published_unix_timestamp());
        $page_static_updated_date_timestamp = date('d.m.Y H:i:s', $page_static_object->get_updated_unix_timestamp());

        $page_static_title = $page_static_object->get_title($pages_static_locale_default->get_name());
        $page_static_description = $page_static_object->get_description($pages_static_locale_default->get_name());

        $page_static_title = strip_tags($page_static_title);
        $page_static_description = strip_tags($page_static_description);

        array_push($pages_static_table_items_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/pages/tableItem.tpl', [
          'PAGE_STATIC_ID' => $page_static_object->get_id(),
          'PAGE_STATIC_NAME' => $page_static_object->get_name(),
          'PAGE_STATIC_INDEX' => $page_static_number,
          'PAGE_STATIC_TITLE' => (!empty($page_static_title)) ? $page_static_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $pages_static_locale_default->get_name()),
          'PAGE_STATIC_DESCRIPTION' => (!empty($page_static_description)) ? $page_static_description : sprintf('[ DESCRIPTION NOT FOUND IN LOCALE %s ]', $pages_static_locale_default->get_name()),
          'PAGE_STATIC_PUBLISHED_STATUS' => ($page_static_object->is_published()) ? 'published' : 'not-published',
          'PAGE_STATIC_URL' => $page_static_object->get_url(),
          'PAGE_STATIC_CREATED_DATE_TIMESTAMP' => $page_static_created_date_timestamp,
          'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP' => ($page_static_object->get_published_unix_timestamp() > 0) ? $page_static_published_date_timestamp : '-',
          'PAGE_STATIC_UPDATED_DATE_TIMESTAMP' => $page_static_updated_date_timestamp
        ]));

        $page_static_number++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/pages.tpl', [
        'PAGE_PAGES_STATIC_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'page_static',
        'ADMIN_PANEL_PAGES_STATIC_TABLE' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/pages/table.tpl', [
          'ADMIN_PANEL_PAGES_STATIC_TABLE_ITEMS' => implode($pages_static_table_items_assembled_array)
        ])
      ]);
    }

  }

}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin\Analytics {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\EntryCategory as EntryCategory;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

/**
 * Страница со списком записей
 */
  class PageEntry implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public Entry $entry;
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     * @param Page $page
     */
    public function __construct(SystemCore $system_core, Page $page, Entry $entry) {
      $this->system_core = $system_core;
      $this->page = $page;
      $this->entry = $entry;
    }

    /**
     * Сборка
     * 
     * @return void
     */
    public function assembly() : void {
      // Добавление таблицы стилей для страницы
      $this->system_core->template->add_style(['href' => 'styles/page/analytics.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      /** @var array Преобразованные элементы навигации */
      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', '{LANG:PAGE_ENTRY_NAVIGATION_BACK_LABEL}'),
        'NAVIGATION_ITEM_URL' => '/admin/entries',
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

      $locale_default = $this->system_core->get_cms_locale('admin');
      $entry_title = $this->entry->get_title($locale_default->get_name());
      $entry_title = (!empty($entry_title)) ? $entry_title : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $locale_default->get_name());

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/analytics/entry.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
        'ADMIN_PANEL_PAGE_NAME' => 'analytics',
        'PAGE_ANALYTICS_ENTRY_TITLE' => sprintf($locale_data['PAGE_ANALYTICS_ENTRY_TITLE'], $entry_title),
        'ENTRY_NAME' => $this->entry->get_name()
      ]);
    }
  }
}

?>
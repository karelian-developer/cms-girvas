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
  use \core\PHPLibrary\EntryCategory as EntryCategory;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageEntriesCategory implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_CATEGORY_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/entriesCategories',
        'permanent' => true,
        'isActive' => false
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
      $this->system_core->template->add_style(['href' => 'styles/page/entriesCategory.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $entries_category = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $entries_category_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $entries_category = (EntryCategory::exists_by_id($this->system_core, $entries_category_id)) ? new EntryCategory($this->system_core, $entries_category_id) : null;
        
        if (!is_null($entries_category)) {
          $entries_category->init_data(['id', 'texts', 'name', 'parent_id', 'metadata']);
        }
      }
      
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesCategory.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
        'ENTRIES_CATEGORY_ID' => (!is_null($entries_category)) ? $entries_category->get_id() : 0,
        'ENTRIES_CATEGORY_TITLE' => (!is_null($entries_category)) ? $entries_category->get_title() : '',
        'ENTRIES_CATEGORY_DESCRIPTION' => (!is_null($entries_category)) ? $entries_category->get_description() : '',
        'ENTRIES_CATEGORY_NAME' => (!is_null($entries_category)) ? $entries_category->get_name() : '',
        'ENTRIES_CATEGORY_FORM_METHOD' => (!is_null($entries_category)) ? 'PATCH' : 'PUT',
        'ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE' => (is_null($entries_category)) ? '' : (($entries_category->is_showed_on_index_page()) ? 'checked' : ''),
      ]);
    }

  }

}

?>
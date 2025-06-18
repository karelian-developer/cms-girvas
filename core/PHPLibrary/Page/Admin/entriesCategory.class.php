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

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/entriesCategories',
        'permanent' => true,
        'isActive' => false
      ],
    ];

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

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entriesCategory.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $entriesCategory = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        $entriesCategoryID = (is_numeric($this->CMSCore->urlp->get_path(2))) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        $entriesCategory = (EntryCategory::exists_by_id($this->CMSCore, $entriesCategoryID)) ? new EntryCategory($this->CMSCore, $entriesCategoryID) : null;
        
        if (!is_null($entriesCategory)) {
          $entriesCategory->init_data(['id', 'texts', 'name', 'parentID', 'metadata']);
        }
      }
      
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesCategory.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
        'ENTRIES_CATEGORY_ID' => !is_null($entriesCategory) ? $entriesCategory->get_id() : 0,
        'ENTRIES_CATEGORY_TITLE' => !is_null($entriesCategory) ? $entriesCategory->get_title() : '',
        'ENTRIES_CATEGORY_DESCRIPTION' => !is_null($entriesCategory) ? $entriesCategory->get_description() : '',
        'ENTRIES_CATEGORY_NAME' => !is_null($entriesCategory) ? $entriesCategory->get_name() : '',
        'ENTRIES_CATEGORY_FORM_METHOD' => !is_null($entriesCategory) ? 'PATCH' : 'PUT',
        'ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE' => (is_null($entriesCategory)) ? '' : (($entriesCategory->is_showed_on_index_page()) ? 'checked' : ''),
      ]);
    }

  }

}

?>
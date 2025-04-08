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
  use \core\PHPLibrary\EntriesSample as EntriesSample;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageEntriesSample implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_SAMPLE_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/entriesSamples',
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
      $this->system_core->template->add_style(['href' => 'styles/page/entriesSample.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $entries_sample = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $entries_sample_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $entries_sample = (EntriesSample::exists_by_id($this->system_core, $entries_sample_id)) ? new EntriesSample($this->system_core, $entries_sample_id) : null;
        
        if (!is_null($entries_sample)) {
          $entries_sample->init_data(['id', 'texts', 'name', 'metadata']);
        }
      }
      
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesSample.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
        'ENTRIES_SAMPLE_ID' => (!is_null($entries_sample)) ? $entries_sample->get_id() : 0,
        'ENTRIES_SAMPLE_TITLE' => (!is_null($entries_sample)) ? $entries_sample->get_title() : '',
        'ENTRIES_SAMPLE_DESCRIPTION' => (!is_null($entries_sample)) ? $entries_sample->get_description() : '',
        'ENTRIES_SAMPLE_NAME' => (!is_null($entries_sample)) ? $entries_sample->get_name() : '',
        'ENTRIES_SAMPLE_LIMIT_COUNT' => (!is_null($entries_sample)) ? $entries_sample->get_limit_count() : '',
        'ENTRIES_SAMPLE_FORM_METHOD' => (!is_null($entries_sample)) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
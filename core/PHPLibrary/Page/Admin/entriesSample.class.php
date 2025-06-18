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

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/entriesSamples',
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entriesSample.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $entriesSample = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        $entriesSampleID = is_numeric($this->CMSCore->urlp->get_path(2)) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        $entriesSample = EntriesSample::exists_by_id($this->CMSCore, $entriesSampleID) ? new EntriesSample($this->CMSCore, $entriesSampleID) : null;
        
        if (!is_null($entriesSample)) {
          $entriesSample->init_data(['id', 'texts', 'name', 'metadata']);
        }
      }
      
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesSample.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
        'ENTRIES_SAMPLE_ID' => !is_null($entriesSample) ? $entriesSample->get_id() : 0,
        'ENTRIES_SAMPLE_TITLE' => !is_null($entriesSample) ? $entriesSample->get_title($localeName) : '',
        'ENTRIES_SAMPLE_DESCRIPTION' => !is_null($entriesSample) ? $entriesSample->get_description($localeName) : '',
        'ENTRIES_SAMPLE_NAME' => !is_null($entriesSample) ? $entriesSample->get_name() : '',
        'ENTRIES_SAMPLE_LIMIT_COUNT' => !is_null($entriesSample) ? $entriesSample->get_limit_count() : '',
        'ENTRIES_SAMPLE_FORM_METHOD' => !is_null($entriesSample) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
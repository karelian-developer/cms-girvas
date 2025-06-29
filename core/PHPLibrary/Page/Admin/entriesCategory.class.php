<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageEntriesCategory implements InterfacePage
{
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
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesCategory.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $entriesCategory = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $entriesCategoryID = (is_numeric($this->CMSCore->urlp->getPath(2))) ? (int)$this->CMSCore->urlp->getPath(2) : 0;
      $entriesCategory = EntryCategory::existsByID($this->CMSCore, $entriesCategoryID) ? new EntryCategory($this->CMSCore, $entriesCategoryID) : null;
      
      if ($entriesCategory !== null) {
        $entriesCategory->initData(['id', 'texts', 'name', 'parentID', 'metadata']);
      }
    }
    
    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesCategory.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
      'ENTRIES_CATEGORY_ID' => $entriesCategory !== null ? $entriesCategory->getID() : 0,
      'ENTRIES_CATEGORY_TITLE' => $entriesCategory !== null ? $entriesCategory->getTitle() : '',
      'ENTRIES_CATEGORY_DESCRIPTION' => $entriesCategory !== null ? $entriesCategory->getDescription() : '',
      'ENTRIES_CATEGORY_NAME' => $entriesCategory !== null ? $entriesCategory->getName() : '',
      'ENTRIES_CATEGORY_FORM_METHOD' => $entriesCategory !== null ? 'PATCH' : 'PUT',
      'ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE' => $entriesCategory === null ? '' : ($entriesCategory->isShowedOnIndexPage() ? 'checked' : ''),
    ]);
  }

}
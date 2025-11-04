<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageEntriesSample implements InterfacePage
{
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
  public function initSubnavigation() : void {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesSample.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $entriesSample = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $entriesSampleID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      $entriesSample = EntriesSample::existsByID($this->CMSCore, $entriesSampleID) ? new EntriesSample($this->CMSCore, $entriesSampleID) : null;
      
      if ($entriesSample !== null) {
        $entriesSample->initData(['id', 'texts', 'name', 'metadata']);
      }
    }
    
    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesSample.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
      'ENTRIES_SAMPLE_ID' => $entriesSample !== null ? $entriesSample->getID() : 0,
      'ENTRIES_SAMPLE_TITLE' => $entriesSample !== null ? $entriesSample->getTitle($localeName) : '',
      'ENTRIES_SAMPLE_DESCRIPTION' => $entriesSample !== null ? $entriesSample->getDescription($localeName) : '',
      'ENTRIES_SAMPLE_NAME' => $entriesSample !== null ? $entriesSample->getName() : '',
      'ENTRIES_SAMPLE_LIMIT_COUNT' => $entriesSample !== null ? $entriesSample->getLimitCount() : '',
      'ENTRIES_SAMPLE_FORM_METHOD' => $entriesSample !== null ? 'PATCH' : 'PUT'
    ]);
  }
}
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin\Analytics;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

/**
 * Страница со списком записей
 */
class PagePage implements InterfacePage
{
  use TraitPage;

  public SystemCore $CMSCore;
  public Page $page;
  public PageStatic $pageStatic;
  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/analytics',
      'permanent' => true,
      'isActive' => false
    ],
  ];

  /**
   * __construct
   * 
   * @param SystemCore $CMSCore
   * @param Page $page
   */
  public function __construct(SystemCore $CMSCore, Page $page, PageStatic $pageStatic)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
    $this->pageStatic = $pageStatic;
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

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $pageStaticTitle = $this->pageStatic->getTitle($localeName);
    $pageStaticTitle = !empty($pageStaticTitle) ? $pageStaticTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $localeName);

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/analytics/page.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'analytics',
      'PAGE_ANALYTICS_PAGE_STATIC_TITLE' => sprintf($localeData['PAGE_ANALYTICS_PAGE_STATIC_TITLE'], $pageStaticTitle),
      'PAGE_STATIC_NAME' => $this->pageStatic->getName()
    ]);
  }
}
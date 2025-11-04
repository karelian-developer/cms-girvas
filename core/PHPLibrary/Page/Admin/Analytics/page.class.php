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

namespace core\PHPLibrary\Page\Admin\Analytics;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
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
   * @param CoreInterface $CMSCore
   * @param InterfacePage $page
   * @param EntityTypeContent $pageStatic
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public InterfacePage $page,
    public PageStatic $pageStatic
  ) {}

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
    $pageStaticTitle = !empty($pageStaticTitle)
      ? $pageStaticTitle
      : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $localeName);

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/analytics/page.tpl',
      [
        'ADMIN_PANEL_PAGE_NAME' => 'analytics',
        'PAGE_ANALYTICS_PAGE_STATIC_TITLE' => sprintf($localeData['PAGE_ANALYTICS_PAGE_STATIC_TITLE'], $pageStaticTitle),
        'PAGE_STATIC_NAME' => $this->pageStatic->getName()
      ]
    );
  }
}
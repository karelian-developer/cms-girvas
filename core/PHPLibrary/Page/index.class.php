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

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \DOMDocument as DOMDocument;
use \DOMXPath as DOMXPath;

class PageIndex implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $metaOpenGraphAllowed = [];

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;

    $this->initMetaOpenGraph();
  }
  
  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/index.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $CMSCore = $this->CMSCore;
    $CMSTheme = $CMSCore->theme;
    $CMSLocale = $CMSCore->locale;

  /**
   * Инициализация данных OpenGraph
   * 
   * @return void
   */
  private function initMetaOpenGraph() : void
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $this->metaOpenGraphAllowed['title'] = $CMSConfigurator->getSiteTitle();
    $this->metaOpenGraphAllowed['description'] = $CMSConfigurator->getSiteDescription();
    $this->metaOpenGraphAllowed['type'] = 'website';
    $this->metaOpenGraphAllowed['url'] = $this->CMSCore->getCMSLink();
    $this->metaOpenGraphAllowed['site_name'] = $CMSConfigurator->getSiteTitle();
  }
  
  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/index.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $CMSCore = $this->CMSCore;
    $CMSTheme = $CMSCore->theme;
    $CMSLocale = $CMSCore->locale;

    $this->addRequiredStyles();

    $localeData = $CMSLocale->getData();
    $localeName = $CMSLocale->getName();

    $this->assembled = ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page.tpl', [
      'PAGE_NAME' => 'index',
      'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page/index.tpl', [])
    ]);
  }
}
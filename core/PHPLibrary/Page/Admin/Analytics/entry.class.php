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
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Pagination as Pagination;

/**
 * Страница со списком записей
 */
class PageEntry implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public Entry $entry;
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param SystemCore $CMSCore
   * @param Page $page
   */
  public function __construct(SystemCore $CMSCore, Page $page, Entry $entry)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
    $this->entry = $entry;
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

    $entryTitle = $this->entry->getTitle($localeName);
    $entryTitle = !empty($entryTitle) ? $entryTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $localeName);

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/analytics/entry.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'analytics',
      'PAGE_ANALYTICS_ENTRY_TITLE' => sprintf($localeData['PAGE_ANALYTICS_ENTRY_TITLE'], $entryTitle),
      'ENTRY_NAME' => $this->entry->getName()
    ]);
  }
}
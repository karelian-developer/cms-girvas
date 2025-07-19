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
use \core\PHPLibrary\Feed as Feed;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageFeed implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_FEED_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/feeds',
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/feed.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $feed = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $feedID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      $feed = Feed::existsByID($this->CMSCore, $feedID) ? new Feed($this->CMSCore, $feedID) : null;
      
      if ($feedID !== null) {
        $feed->initData(['id', 'name', 'entriesCategoryID', 'typeID', 'texts']);
      }
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/feed.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'web-channel',
      'FEED_ID' => $feed !== null ? $feed->getID() : 0,
      'FEED_NAME' => $feed !== null ? $feed->getName() : '',
      'FEED_TITLE' => $feed !== null ? $feed->getTitle($localeName) : '',
      'FEED_DESCRIPTION' => $feed !== null ? $feed->getDescription($localeName) : '',
      'FEED_FORM_METHOD' => $feed !== null ? 'PATCH' : 'PUT',
    ]);
  }

}
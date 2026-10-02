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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
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
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
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
      'isActive' => false,
      'permission' => 'hasPermissionAdminFeedsManagement',
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page) {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Получить группу текущего авторизованного пользователя админки
   * 
   * @return UserGroup|null
   */
  private function getCurrentUserGroup() : ?UserGroup
  {
    /** @var User|null $user */
    $user = $this->CMSCore->client->getUser(2);
    if ($user === null) {
      return null;
    }

    $user->initData(['metadata']);

    /** @var UserGroup|null $userGroup */
    $userGroup = $user->getGroup();
    if ($userGroup === null) {
      return null;
    }

    $userGroup->initData(['permissions']);

    return $userGroup;
  }

  /**
   * Проверить, есть ли у текущего пользователя право управления фидами
   * 
   * @return bool
   */
  private function currentUserCanManageFeeds() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionAdminFeedsManagement')
      && $userGroup->hasPermissionAdminFeedsManagement();
  }

  /**
   * Собрать страницу ошибки и подменить итоговую сборку
   * 
   * @param int $httpCode
   * 
   * @return void
   */
  private function assemblyError(int $httpCode) : void
  {
    http_response_code($httpCode);

    $pageError = new PageError($this->CMSCore, $this->page, $httpCode);
    $pageError->assembly();
    $this->assembled = $pageError->assembled;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void {
    // Если нет прав на управление фидами — подразделы не собираем
    if (!$this->currentUserCanManageFeeds()) {
      return;
    }

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageFeeds()) {
      $this->assemblyError(403);
      return;
    }

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
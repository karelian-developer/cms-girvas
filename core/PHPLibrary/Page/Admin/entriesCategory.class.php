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
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageEntriesCategory implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_CATEGORY_NAVIGATION_%s_LABEL';

  public CoreInterface $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/entriesCategories',
      'permanent' => true,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesCategoriesEdit',
    ],
  ];

  public function __construct(CoreInterface $CMSCore, Page $page) {
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
   * Проверить, есть ли у текущего пользователя право редактирования категорий записей
   * 
   * @return bool
   */
  private function currentUserCanEditCategories() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionEditorEntriesCategoriesEdit')
      && $userGroup->hasPermissionEditorEntriesCategoriesEdit();
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
  public function initSubnavigation() : void
  {
    // Если нет прав на редактирование категорий — подразделы не собираем
    if (!$this->currentUserCanEditCategories()) {
      return;
    }

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanEditCategories()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesCategory.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $entriesCategory = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $entriesCategoryID = is_numeric($this->CMSCore->urlp->getPath(2))
        ? (int)$this->CMSCore->urlp->getPath(2)
        : 0;
      $entriesCategory = EntryCategory::existsByID($this->CMSCore, $entriesCategoryID)
        ? new EntryCategory($this->CMSCore, $entriesCategoryID)
        : null;
      
      if ($entriesCategory !== null) {
        $entriesCategory->initData(['id', 'texts', 'name', 'parentID', 'metadata']);
      }
    }
    
    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/entriesCategory.tpl',
      [
        'ADMIN_PANEL_PAGE_NAME' => 'entries-category',
        'ENTRIES_CATEGORY_ID' => $entriesCategory !== null ? $entriesCategory->getID() : 0,
        'ENTRIES_CATEGORY_TITLE' => $entriesCategory !== null ? $entriesCategory->getTitle($localeName) : '',
        'ENTRIES_CATEGORY_SEO_TITLE' => $entriesCategory !== null ? $entriesCategory->getSEOTitle($localeName) : '',
        'ENTRIES_CATEGORY_DESCRIPTION' => $entriesCategory !== null ? $entriesCategory->getDescription($localeName) : '',
        'ENTRIES_CATEGORY_SEO_DESCRIPTION' => $entriesCategory !== null ? $entriesCategory->getSEODescription($localeName) : '',
        'ENTRIES_CATEGORY_KEYWORDS' => $entriesCategory !== null ? implode(', ', $entriesCategory->getKeywords($localeName)) : '',
        'ENTRIES_CATEGORY_NAME' => $entriesCategory !== null ? $entriesCategory->getName() : '',
        'ENTRIES_CATEGORY_FORM_METHOD' => $entriesCategory !== null ? 'PATCH' : 'PUT',
        'ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE' => $entriesCategory === null ? '' : ($entriesCategory->isShowedOnIndexPage() ? 'checked' : ''),
      ]
    );
  }
}
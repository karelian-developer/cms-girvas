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
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageUserGroup implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USERS_GROUP_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/usersGroups',
      'permanent' => true,
      'isActive' => false
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/userGroup.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $usersGroup = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $usersGroupID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      $usersGroup = UserGroup::existsByID($this->CMSCore, $usersGroupID) ? new UserGroup($this->CMSCore, $usersGroupID) : null;
      
      if ($usersGroup !== null) {
        $usersGroup->initData(['id', 'name', 'permissions', 'texts']);
      }
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/usersGroup.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'user-group',
      'USERS_GROUP_ID' => $usersGroup !== null ? $usersGroup->getID() : 0,
      'USERS_GROUP_NAME' => $usersGroup !== null ? $usersGroup->getName() : '',
      'USERS_GROUP_TITLE' => $usersGroup !== null ? $usersGroup->getTitle($localeName) : '',
      'USERS_GROUP_FORM_METHOD' => $usersGroup !== null ? 'PATCH' : 'PUT',
      'USERS_GROUP_PERMISSION_ADMIN_PANEL_AUTH_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_PANEL_AUTH)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_USERS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_MODULES_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_TEMPLATES_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_FEEDS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_FORMS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_SETTINGS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_ADMIN_VIEWING_LOGS_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_MODER_USERS_BAN_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_BAN)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_MODER_USERS_WARNS_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_WARNS)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_EDITOR_ENTRIES_EDIT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_EDITOR_PAGES_STATIC_EDIT_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CREATE_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CHANGE_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE)) ? 'checked' : ''),
      'USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_RATE_VALUE' => $usersGroup === null ? '' : (($usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE)) ? 'checked' : ''),
    ]);
  }
}
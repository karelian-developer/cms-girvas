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

use \DOMDocument as DOMDocument;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\EntryComments as EntryComments;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageEntriesComments implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_CONTENT_NAVIGATION_%s_LABEL';

  /**
   * Допустимые правила сортировки
   */
  private const ALLOWED_SORT_RULES = [
    'by_createdtimestamp_increase',
    'by_createdtimestamp_decrease',
    'by_updatedtimestamp_increase',
    'by_updatedtimestamp_decrease',
  ];

  public CoreInterface $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false,
      'permission' => null,
    ],
    'entries' => [
      'name' => 'entries',
      'iconName' => 'entries',
      'link' => '/entries',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesEdit',
    ],
    'pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorPagesStaticEdit',
    ],
    'categories' => [
      'name' => 'categories',
      'iconName' => 'entriesCategories',
      'link' => '/entriesCategories',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesCategoriesEdit',
    ],
    'comments' => [
      'name' => 'comments',
      'iconName' => 'entriesComments',
      'link' => '/entriesComments',
      'permanent' => true,
      'isActive' => true,
      'permission' => 'hasPermissionModerEntriesCommentsManagement',
    ],
    'samples' => [
      'name' => 'samples',
      'iconName' => 'entriesSamples',
      'link' => '/entriesSamples',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorEntriesEdit',
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/forms',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminFormsManagement',
    ],
    'blocks' => [
      'name' => 'blocks',
      'iconName' => 'contentBlocks',
      'link' => '/contentBlocks',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionEditorContentBlocksEdit',
    ]
  ];

  public function __construct(CoreInterface $CMSCore, Page $page)
  {
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
   * Проверить, есть ли у текущего пользователя право управления комментариями
   * 
   * @return bool
   */
  private function currentUserCanManageComments() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionModerEntriesCommentsManagement')
      && $userGroup->hasPermissionModerEntriesCommentsManagement();
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
   * Отфильтровать подразделы по правам текущего пользователя
   * 
   * @param UserGroup $userGroup
   * 
   * @return void
   */
  private function filterSubsections(UserGroup $userGroup) : void
  {
    foreach ($this->navigationSubsections as $index => $subsection) {
      $permission = $subsection['permission'] ?? null;

      if ($permission === null) {
        continue;
      }

      $allowed = method_exists($userGroup, $permission) && $userGroup->{$permission}();

      if (!$allowed) {
        unset($this->navigationSubsections[$index]);
      }
    }
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    /** @var UserGroup|null $userGroup */
    $userGroup = $this->getCurrentUserGroup();

    if ($userGroup === null) {
      return;
    }

    // Показываем навигацию только тем, у кого есть хоть какое-то право контент-раздела
    $hasAnyAccess = (method_exists($userGroup, 'hasAnyEditorPermission')
        && $userGroup->hasAnyEditorPermission())
      || $userGroup->hasPermissionModerEntriesCommentsManagement()
      || $userGroup->hasPermissionAdminFormsManagement();

    if (!$hasAnyAccess) {
      return;
    }

    $this->filterSubsections($userGroup);

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  /**
   * Query string для пагинации
   */
  private function buildQueryString(string $searchValue, string $sortRule) : string
  {
    $basePath = '/admin/entriesComments';

    $parts = [];
    if ($searchValue !== '') {
      $parts[] = 'value=' . urlencode($searchValue);
    }
    $parts[] = 'sort=' . urlencode($sortRule);

    return $basePath . '?' . implode('&', $parts);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageComments()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entriesComments.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    // Поиск
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? EntryComments::DEFAULT_SORT_RULE;
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = EntryComments::DEFAULT_SORT_RULE;
    }

    $entryComments = new EntryComments($this->CMSCore);

    $entriesCommentsObjects = $entryComments->getAll(
      ['limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]],
      $searchValue,
      $sortRule
    );

    $entriesCommentsTotal = $entryComments->getCountTotal($searchValue);

    $pagination = new Pagination(
      $this->CMSCore,
      $entriesCommentsTotal,
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    $commentsTableItemsAssembled = [];
    if (!empty($entriesCommentsObjects)) {
      foreach ($entriesCommentsObjects as $index => $object) {
        $object->initData(['content', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata', 'authorID', 'entryID']);

        $createdDateTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
        $updatedDateTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

        $author = $object->getAuthor();
        if ($author !== null) {
          $author->initData(['login']);
        }

        $authorLogin = $author !== null ? $author->getLogin() : 'User deleted';

        $entry = $object->getEntry();
        if ($entry !== null) {
          $entry->initData(['texts']);
        }

        $entryTitle = $entry !== null ? $entry->getTitle($localeName) : 'Entry deleted';

        $commentIsHidden = $object->isHidden();

        $commentsTableItemsAssembled[] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesComments/tableItem.tpl', [
          'COMMENT_ID' => $object->getID(),
          'COMMENT_IS_HIDDEN_STATUS' => var_export($object->isHidden(), true),
          'COMMENT_HIDDEN_REASON' => strip_tags($object->getHiddenReason()),
          'COMMENT_INDEX' => $paginationItemCurrent * $paginationItemsOnPage + $index + 1,
          'COMMENT_CONTENT' => strip_tags($object->getContent()),
          'COMMENT_AUTHOR_LOGIN' => $authorLogin,
          'COMMENT_ENTRY_TITLE' => $entryTitle,
          'COMMENT_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
          'COMMENT_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
          'COMMENT_TOGGLE_EVENT' => $commentIsHidden ? 'show' : 'hide',
          'COMMENT_TOGGLE_LABEL' => $commentIsHidden
            ? ($localeData['PAGE_ENTRIES_COMMENTS_BUTTON_SHOW'] ?? 'Опубликовать')
            : ($localeData['PAGE_ENTRIES_COMMENTS_BUTTON_HIDE'] ?? 'Снять с публикации'),
        ]);
      }
    }

    $templateCommentsTable = !empty($entriesCommentsObjects)
      ? ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesComments/table.tpl', [
          'ADMIN_PANEL_COMMENTS_TABLE_ITEMS' => implode($commentsTableItemsAssembled)
        ])
      : $localeData['PAGE_ENTRIES_COMMENTS_NOT_FOUND_LABEL'];

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entriesComments.tpl', [
      'PAGE_ENTRIES_COMMENTS_PAGINATION' => $pagination->assembled,
      'ADMIN_PANEL_PAGE_NAME' => 'comments',
      'ADMIN_PANEL_COMMENTS_TABLE' => $templateCommentsTable,
      'ENTRIES_COMMENTS_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
      'ENTRIES_COMMENTS_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
    ]);
  }
}
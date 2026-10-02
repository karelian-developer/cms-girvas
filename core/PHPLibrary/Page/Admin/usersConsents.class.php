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
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\User\Consent as UserConsent;
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Report as Report;

class PageUsersConsents implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USERS_CONSENTS_NAVIGATION_%s_LABEL';

  /**
   * Допустимые правила сортировки (должны совпадать с Consent::SORT_RULES)
   */
  private const ALLOWED_SORT_RULES = [
    'by_consentedat_increase',
    'by_consentedat_decrease',
    'by_status_active_first',
    'by_status_revoked_first',
    'by_source_increase',
    'by_source_decrease',
  ];

  public SystemCore $CMSCore;
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
    'users' => [
      'name' => 'users',
      'iconName' => 'users',
      'link' => '/users',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminUsersManagement',
    ],
    'groups' => [
      'name' => 'groups',
      'iconName' => 'usersGroups',
      'link' => '/usersGroups',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminUsersGroupsManagement',
    ],
    'consents' => [
      'name' => 'consents',
      'iconName' => 'usersConsents',
      'link' => '/usersConsents',
      'permanent' => false,
      'isActive' => true,
      'permission' => 'hasPermissionAdminUsersConsentsManagement',
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
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
   * Проверить, есть ли у группы право управления согласиями (152-ФЗ)
   * 
   * @param UserGroup $userGroup
   * 
   * @return bool
   */
  private function userGroupCanManageConsents(UserGroup $userGroup) : bool
  {
    return $userGroup->hasPermissionAdminUsersConsentsManagement()
      || $userGroup->isSuperGroup();
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

    // Если у пользователя вообще нет доступа ни к одному подразделу — не собираем
    $hasAnyAccess = $this->userGroupCanManageConsents($userGroup)
      || $userGroup->hasPermissionAdminUsersManagement()
      || $userGroup->hasPermissionAdminUsersGroupsManagement();

    if (!$hasAnyAccess) {
      return;
    }

    $this->filterSubsections($userGroup);

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/usersConsents.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    // Получаем текущего пользователя админки
    /** @var User|null $clientUser */
    $clientUser = $this->CMSCore->client->getUser(2);
    if ($clientUser === null) {
      $this->assemblyError(401);
      return;
    }

    /** @var UserGroup|null $clientUserGroup */
    $clientUserGroup = $this->getCurrentUserGroup();
    if ($clientUserGroup === null || !$this->userGroupCanManageConsents($clientUserGroup)) {
      $this->assemblyError(403);
      return;
    }

    // Отзывать согласия может только супергруппа
    $clientIsSuper = $clientUserGroup->isSuperGroup();

    // Пагинация
    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null
      ? (int) $this->CMSCore->urlp->getParam('pageNumber')
      : 0;
    $paginationItemsOnPage = 20;

    // Поиск по userID
    $searchValue = $this->CMSCore->urlp->getParam('value');
    $searchValue = $searchValue !== null ? trim(urldecode($searchValue)) : '';

    // Сортировка
    $sortRule = $this->CMSCore->urlp->getParam('sort') ?? UserConsent::DEFAULT_SORT_RULE;
    if (!in_array($sortRule, self::ALLOWED_SORT_RULES, true)) {
      $sortRule = UserConsent::DEFAULT_SORT_RULE;
    }

    // Логируем просмотр
    Report::create(
      $this->CMSCore,
      Report::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
      [
        'action' => 'consents_list_view',
        'viewedByID' => $clientUser->getID(),
        'search' => $searchValue,
        'sort' => $sortRule,
        'page' => $paginationItemCurrent,
        'perPage' => $paginationItemsOnPage,
        'ip' => $this->CMSCore->client->getIPAddress()
      ]
    );

    // Получаем согласия
    $consents = UserConsent::getAll(
      $this->CMSCore,
      $paginationItemsOnPage,
      $paginationItemCurrent * $paginationItemsOnPage,
      $searchValue,
      $sortRule
    );
    $consentsTotal = UserConsent::countAll($this->CMSCore, $searchValue);

    $pagination = new Pagination(
      $this->CMSCore,
      $consentsTotal,
      $paginationItemsOnPage,
      $paginationItemCurrent,
      $this->buildQueryString($searchValue, $sortRule),
      false
    );
    $pagination->assembly();

    $tableItemsAssembled = [];
    $consentNumber = $paginationItemCurrent * $paginationItemsOnPage + 1;

    foreach ($consents as $consent) {
      $consent->initData();

      // Пользователь
      $userLogin = '—';
      $userID = $consent->getUserID();
      if ($userID > 0 && User::existsByID($this->CMSCore, $userID)) {
        $userObject = new User($this->CMSCore, $userID);
        $userObject->initData(['login']);
        $userLogin = $userObject->getLogin();
      }

      // Документ
      $documentTitle = '—';
      $documentVersion = $consent->getDocumentVersion();
      if ($consent->getPageStaticID() > 0) {
        $pageStatic = new PageStatic($this->CMSCore, $consent->getPageStaticID());
        if ($pageStatic !== null) {
          $pageStatic->initData(['texts', 'name']);
          $documentTitle = $pageStatic->getTitle($localeName);
          if (empty($documentTitle)) {
            $documentTitle = $pageStatic->getName();
          }
        }
      }

      // Локаль — заголовок, а не имя
      $localeTitle = $consent->getLocale();
      try {
        $CMSLocale = new SystemCoreLocale($this->CMSCore, $consent->getLocale());
        $CMSLocale->initPathes();
        $localeTitle = $CMSLocale->getTitle();
      } catch (\Exception $e) {
        // fallback на имя локали
      }

      // Источник
      $sourceLabel = match ($consent->getSource()) {
        'form' => $localeData['PAGE_USERS_CONSENTS_SOURCE_FORM'] ?? 'Форма',
        'registration' => $localeData['PAGE_USERS_CONSENTS_SOURCE_REGISTRATION'] ?? 'Регистрация',
        default => $consent->getSource()
      };

      // Статус
      if ($consent->isRevoked()) {
        $statusLabel = $localeData['PAGE_USERS_CONSENTS_STATUS_REVOKED'] ?? 'Отозвано';
        $statusClass = 'status-revoked';
      } else {
        $statusLabel = $localeData['PAGE_USERS_CONSENTS_STATUS_ACTIVE'] ?? 'Активно';
        $statusClass = 'status-active';
      }

      // Дата
      $consentedDate = date('d.m.Y H:i:s', $consent->getConsentedAt());

      // User-Agent — сокращённый
      $userAgentFull = $consent->getUserAgent();
      $userAgentShort = mb_strlen($userAgentFull) > 40
        ? mb_substr($userAgentFull, 0, 40) . '…'
        : $userAgentFull;

      $canRevoke = $clientIsSuper && !$consent->isRevoked();

      $tableItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/users/consents/item.tpl',
        [
          'CONSENT_ID' => $consent->getID(),
          'USER_ID' => $userID,
          'USER_LOGIN' => htmlspecialchars($userLogin),
          'PAGE_STATIC_ID' => $consent->getPageStaticID(),
          'DOCUMENT_TITLE' => htmlspecialchars($documentTitle),
          'DOCUMENT_VERSION' => htmlspecialchars($documentVersion),
          'LOCALE_TITLE' => htmlspecialchars($localeTitle),
          'SOURCE_LABEL' => htmlspecialchars($sourceLabel),
          'STATUS_LABEL' => '<span class="consent-status ' . $statusClass . '">' . htmlspecialchars($statusLabel) . '</span>',
          'CONSENTED_DATE' => $consentedDate,
          'IP' => htmlspecialchars($consent->getIP()),
          'USER_AGENT_SHORT' => htmlspecialchars($userAgentShort),
          'IS_REVOKED' => $consent->isRevoked() ? '1' : '0',
          'CAN_REVOKE' => $canRevoke ? '1' : '0'
        ]
      );

      $consentNumber++;
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/users/consents.tpl',
      [
        'PAGE_CONSENTS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'users-consents',
        'ADMIN_PANEL_CONSENTS_TABLE' => implode("\n", $tableItemsAssembled),
        'USERS_CONSENTS_SEARCH_VALUE' => htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'),
        'USERS_CONSENTS_SORT_VALUE'   => htmlspecialchars($sortRule, ENT_QUOTES, 'UTF-8'),
      ]
    );
  }

  /**
   * Query string для пагинации с сохранением поиска и сортировки
   */
  private function buildQueryString(string $searchValue, string $sortRule) : string
  {
    $basePath = '/admin/usersConsents';

    $parts = [];
    if ($searchValue !== '') {
      $parts[] = 'value=' . urlencode($searchValue);
    }
    $parts[] = 'sort=' . urlencode($sortRule);

    return $basePath . '?' . implode('&', $parts);
  }
}
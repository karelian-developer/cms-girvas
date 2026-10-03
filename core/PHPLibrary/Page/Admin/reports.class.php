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
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Reports as CMSReports;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

final class PageReports implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_REPORTS_NAVIGATION_%s_LABEL';

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
    'all' => [
      'name' => 'base',
      'iconName' => 'base',
      'link' => '/reports',
      'permanent' => true,
      'isActive' => true,
      'permission' => 'hasPermissionAdminViewingLogs',
    ],
    'content' => [
      'name' => 'content',
      'iconName' => 'content',
      'link' => '/reports/content',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminViewingLogs',
    ],
    'security' => [
      'name' => 'security',
      'iconName' => 'security',
      'link' => '/reports/security',
      'permanent' => false,
      'isActive' => false,
      'permission' => 'hasPermissionAdminViewingLogs',
    ],
  ];

  public function __construct(
    public CoreInterface $CMSCore,
    public Page $page
  ) {}

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
    $themeSource =& $this->CMSCore->theme->core->source;

    /** @var User Объект авторизованного пользователя */
    $user = $this->CMSCore->client->getUser(2);
    $user->initData(['metadata']);

    /** @var UserGroup Объект группы пользователя */
    $userGroup = $user->getGroup();
    $userGroup->initData(['permissions']);

    // Отфильтровываем подразделы по правам
    $filteredSubsections = [];
    foreach ($this->navigationSubsections as $subsectionIndex => $subsectionData) {
      $permission = $subsectionData['permission'] ?? null;

      $allowed = $permission === null
        || (method_exists($userGroup, $permission) && $userGroup->{$permission}());

      if ($allowed) {
        $filteredSubsections[$subsectionIndex] = $subsectionData;
      }
    }

    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource, $filteredSubsections);
  }

  public function getAvailableReportsCategoriesArray() : array
  {
    $reports = [];

    $reportsClassesFilesPath = $this->CMSCore->getCMSPath() . '/core/PHPLibrary/Page/Admin/Reports';
    $reportsClassesFiles = array_diff(scandir($reportsClassesFilesPath), ['.', '..']);

    foreach ($reportsClassesFiles as $file) {
      if (preg_match('/^([a-zA-Z_]+)\.class\.php$/', $file, $matches)) {
        array_push($reports, $matches[1]);
      }
    }

    return $reports;
  }

  /**
   * Конвертировать имя настройки в константу
   * 
   * @param string $reportsName
   * 
   * @return string
   */
  private function convertReportsNameToConstant(string $reportsName) : string
  {
    return match ($reportsName) {
      default => strtoupper($reportsName)
    };
  }

  /**
   * Получить пространство имен страницы с настройками
   * 
   * @param string $reportsName
   * 
   * @return string
   */
  private function getReportsPageClassNamespace(string $reportsName) : string
  {
    return '\\core\\PHPLibrary\\Page\\Admin\\Reports\\Reports' . ucfirst($reportsName);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    /** @var User|null $user */
    $user = $this->CMSCore->client->getUser(2);
    if ($user === null) {
      $this->assemblyError(401);
      return;
    }
    $user->initData(['metadata']);

    /** @var UserGroup|null $userGroup */
    $userGroup = $user->getGroup();
    if ($userGroup === null) {
      $this->assemblyError(403);
      return;
    }
    $userGroup->initData(['permissions']);

    $canViewReports = $userGroup->hasReportsAccess()
      || $userGroup->hasPermissionAdminViewingLogs();

    if (!$canViewReports) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/reports.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addScript(['src' => 'admin/page/reports.js'], true);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $reportsName = $this->CMSCore->urlp->getPath(2) ?? 'base';
    $reportsCorePath = $this->CMSCore->getCMSPath() . '/core/PHPLibrary/Page/Admin/Reports/' . $reportsName . '.class.php';

    if (file_exists($reportsCorePath)) {
      http_response_code(200);

      $classNamespace = $this->getReportsPageClassNamespace($reportsName);
      $reportsPage = new $classNamespace($this->CMSCore, $reportsName);
      $reportsNameConstant = $this->convertReportsNameToConstant($reportsName);

      $reportsPage->setTitle('{LANG:PAGE_REPORTS_REPORTS_GROUP_' . $reportsNameConstant . '_TITLE}');
      $reportsPage->setDescription('{LANG:PAGE_REPORTS_REPORTS_GROUP_' . $reportsNameConstant . '_DESCRIPTION}');

      $reportsTitle = $reportsPage->getTitle();
      $reportsDescription = $reportsPage->getDescription();
      $reportsPage->assembly();

      $reportsPageAssembled = $reportsPage->assembled;
    } else {
      $this->assemblyError(404);
      return;
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports.tpl', [
      'PAGE_NAME' => 'reports',
      'PAGE_REPORT_TITLE' => $reportsTitle ?? $localeData['PAGE_REPORTS_GROUP_NOT_FOUND_TITLE'],
      'PAGE_REPORT_DESCRIPTION' => $reportsDescription ?? $localeData['PAGE_REPORTS_GROUP_NOT_FOUND_DESCRIPTION'],
      'PAGE_REPORT' => ThemeCollector::assembly($reportsPageAssembled, [])
    ]);
  }
}
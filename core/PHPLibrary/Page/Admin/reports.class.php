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

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
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
      'isActive' => false
    ],
    'all' => [
      'name' => 'all',
      'iconName' => 'all',
      'link' => '/reports',
      'permanent' => false,
      'isActive' => true
    ],
  ];

  public function __construct(
    public CoreInterface $CMSCore,
    public Page $page
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
      $reportsPageAssembled = $reportsPage->assembly();
    } else {
      http_response_code(404);
    }

    $reportsPageAssembled = $reportsPageAssembled ?? '';

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports.tpl', [
      'PAGE_NAME' => 'reports',
      'PAGE_REPORT_TITLE' => $reportsTitle ?? $localeData['PAGE_REPORTS_GROUP_NOT_FOUND_TITLE'],
      'PAGE_REPORT_DESCRIPTION' => $reportsDescription ?? $localeData['PAGE_REPORTS_GROUP_NOT_FOUND_DESCRIPTION'],
      'PAGE_REPORT' => ThemeCollector::assembly($reportsPageAssembled, [])
    ]);

    // $reportsSecurityAssembled = [];
    // $reportsSecurity = (new CMSReports($this->CMSCore))->getByTypeIDs([
    //   CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
    //   CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
    // ], ['limit' => 50]);

    // $reportsCommonAssembled = [];
    // $reportsCommon = (new CMSReports($this->CMSCore))->getByTypeIDs([
    //   CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
    //   CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
    //   CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED
    // ], ['limit' => 50]);

    // foreach ($reportsSecurity as $index => $report) {
    //   $report->initData(['metadata', 'variables', 'createdUnixTimestamp']);
      
    //   $reportCategoryName = 'security';
    //   $reportVariables = $report->getVariables();

    //   $reportsSecurityAssembled[] = ThemeCollector::assemblyFileContent(
    //     $this->CMSCore->theme,
    //     'templates/page/reports/listItem.tpl',
    //     [
    //       'REPORT_CATEGORY_NAME' => $reportCategoryName,
    //       'REPORT_CONTENT' => ThemeCollector::assembly(
    //         ThemeCollector::assemblyLocale(
    //           $report->getContent(),
    //           $this->CMSCore->locale
    //         ),
    //         [
    //           'CLIENT_IP' => $reportVariables['clientIP'] ?? '0.0.0.0',
    //           'DATE' => $reportVariables['date'] ?? date('d.m.Y H:i:s', time()),
    //           'ENTRY_TITLE' => $reportVariables['entryTitle'] ?? '[ ??? ]',
    //         ]
    //       ),
    //       'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp()),
    //     ]
    //   );
    // }

    // foreach ($reportsCommon as $index => $report) {
    //   $report->initData(['metadata', 'variables', 'createdUnixTimestamp']);

    //   $reportCategoryName = 'common';
    //   $reportVariables = $report->getVariables();

    //   $reportsCommonAssembled[] = ThemeCollector::assemblyFileContent(
    //     $this->CMSCore->theme,
    //     'templates/page/reports/listItem.tpl',
    //     [
    //       'REPORT_CATEGORY_NAME' => $reportCategoryName,
    //       'REPORT_CONTENT' => ThemeCollector::assembly(
    //         ThemeCollector::assemblyLocale(
    //           $report->getContent(),
    //           $this->CMSCore->locale
    //         ),
    //         [
    //           'CLIENT_IP' => $reportVariables['clientIP'] ?? '0.0.0.0',
    //           'DATE' => $reportVariables['date'] ?? date('d.m.Y H:i:s', time()),
    //           'ENTRY_TITLE' => $reportVariables['entryTitle'] ?? '[ ??? ]',
    //         ]
    //       ),
    //       'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp()),
    //     ]
    //   );
    // }

    /** @var string $site_page Содержимое шаблона страницы */
    // $this->assembled = ThemeCollector::assemblyFileContent(
    //   $this->CMSCore->theme,
    //   'templates/page/reports.tpl',
    //   [
    //     'ADMIN_PANEL_PAGE_NAME' => 'reports',
    //     'REPORTS_SECURITY_LIST' => ThemeCollector::assemblyFileContent(
    //       $this->CMSCore->theme,
    //       'templates/page/reports/list.tpl',
    //       [
    //         'REPORTS_LIST_ITEMS' => implode($reportsSecurityAssembled)
    //       ]
    //     ),
    //     'REPORTS_COMMON_LIST' => ThemeCollector::assemblyFileContent(
    //       $this->CMSCore->theme,
    //       'templates/page/reports/list.tpl',
    //       [
    //         'REPORTS_LIST_ITEMS' => implode($reportsCommonAssembled)
    //       ]
    //     )
    //   ]
    // );


  }
}
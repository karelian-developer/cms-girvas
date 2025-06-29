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
use \core\PHPLibrary\SystemCore\Report as SystemCoreReport;
use \core\PHPLibrary\SystemCore\Reports as SystemCoreReports;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

final class PageReports implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_REPORTS_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/reports.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addScript(['src' => 'admin/page/reports.js'], true);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $reportsSecurityAssembled = [];
    $reportsSecurity = (new SystemCoreReports($this->CMSCore))->getByTypeIDs([
      SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
      SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
    ], ['limit' => 50]);

    $reportsCommonAssembled = [];
    $reportsCommon = (new SystemCoreReports($this->CMSCore))->getByTypeIDs([
      SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
      SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
      SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_DELETED
    ], ['limit' => 50]);

    foreach ($reportsSecurity as $report) {
      $report->initData(['metadata', 'variables', 'createdUnixTimestamp']);
      
      $reportCategoryName = 'security';
      $reportVariables = $report->getVariables();

      array_push($reportsSecurityAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports/listItem.tpl', [
        'REPORT_CATEGORY_NAME' => $reportCategoryName,
        'REPORT_CONTENT' =>ThemeCollector::assembly(ThemeCollector::assemblyLocale($report->getContent(), $this->CMSCore->locale), [
          'CLIENT_IP' => $reportVariables['clientIP'] ?? '0.0.0.0',
          'DATE' => $reportVariables['date'] ?? date('d.m.Y H:i:s', time()),
          'ENTRY_TITLE' => $reportVariables['entryTitle'] ?? '[ ??? ]',
        ]),
        'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp()),
      ]));
    }

    foreach ($reportsCommon as $report) {
      $report->initData(['metadata', 'variables', 'createdUnixTimestamp']);

      $reportCategoryName = 'common';
      $reportVariables = $report->getVariables();

      array_push($reportsCommonAssembled, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports/listItem.tpl', [
        'REPORT_CATEGORY_NAME' => $reportCategoryName,
        'REPORT_CONTENT' =>ThemeCollector::assembly(ThemeCollector::assemblyLocale($report->getContent(), $this->CMSCore->locale), [
          'CLIENT_IP' => $reportVariables['clientIP'] ?? '0.0.0.0',
          'DATE' => $reportVariables['date'] ?? date('d.m.Y H:i:s', time()),
          'ENTRY_TITLE' => $reportVariables['entryTitle'] ?? '[ ??? ]',
        ]),
        'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp()),
      ]));
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'reports',
      'REPORTS_SECURITY_LIST' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports/list.tpl', [
        'REPORTS_LIST_ITEMS' => implode($reportsSecurityAssembled)
      ]),
      'REPORTS_COMMON_LIST' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/reports/list.tpl', [
        'REPORTS_LIST_ITEMS' => implode($reportsCommonAssembled)
      ])
    ]);
  }
}
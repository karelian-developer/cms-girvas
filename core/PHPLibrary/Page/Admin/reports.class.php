<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Report as SystemCoreReport;
  use \core\PHPLibrary\SystemCore\Reports as SystemCoreReports;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  final class PageReports implements InterfacePage {
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

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/reports.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_script(['src' => 'admin/page/reports.js'], true);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $reportsSecurityAssembled = [];
      $reportsSecurity = (new SystemCoreReports($this->CMSCore))->get_by_type_ids([
        SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
        SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
      ], ['limit' => 50]);

      $reportsCommonAssembled = [];
      $reportsCommon = (new SystemCoreReports($this->CMSCore))->get_by_type_ids([
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_DELETED
      ], ['limit' => 50]);

      foreach ($reportsSecurity as $report) {
        $report->init_data(['metadata', 'variables', 'createdUnixTimestamp']);
        
        $reportCategoryName = 'security';
        $reportVariables = $report->get_variables();

        array_push($reportsSecurityAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/reports/listItem.tpl', [
          'REPORT_CATEGORY_NAME' => $reportCategoryName,
          'REPORT_CONTENT' =>TemplateCollector::assembly(TemplateCollector::assembly_locale($report->get_content(), $this->CMSCore->locale), [
            'CLIENT_IP' => isset($reportVariables['clientIP']) ? $reportVariables['clientIP'] : '[ ??? ]',
            'DATE' => isset($reportVariables['date']) ? $reportVariables['date'] : '[ ??? ]',
            'ENTRY_TITLE' => isset($reportVariables['entryTitle']) ? $reportVariables['entryTitle'] : '[ ??? ]',
          ]),
          'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->get_created_unix_timestamp()),
        ]));
      }

      foreach ($reportsCommon as $report) {
        $report->init_data(['metadata', 'variables', 'createdUnixTimestamp']);

        $reportCategoryName = 'common';
        $reportVariables = $report->get_variables();

        array_push($reportsCommonAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/reports/listItem.tpl', [
          'REPORT_CATEGORY_NAME' => $reportCategoryName,
          'REPORT_CONTENT' =>TemplateCollector::assembly(TemplateCollector::assembly_locale($report->get_content(), $this->CMSCore->locale), [
            'CLIENT_IP' => isset($reportVariables['clientIP']) ? $reportVariables['clientIP'] : '[ ??? ]',
            'DATE' => isset($reportVariables['date']) ? $reportVariables['date'] : '[ ??? ]',
            'ENTRY_TITLE' => isset($reportVariables['entryTitle']) ? $reportVariables['entryTitle'] : '[ ??? ]',
          ]),
          'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->get_created_unix_timestamp()),
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/reports.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'reports',
        'REPORTS_SECURITY_LIST' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/reports/list.tpl', [
          'REPORTS_LIST_ITEMS' => implode($reportsSecurityAssembled)
        ]),
        'REPORTS_COMMON_LIST' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/reports/list.tpl', [
          'REPORTS_LIST_ITEMS' => implode($reportsCommonAssembled)
        ])
      ]);
    }
  }
}

?>
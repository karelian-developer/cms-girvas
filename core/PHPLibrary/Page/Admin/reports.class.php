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

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
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

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/reports.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_script(['src' => 'admin/page/reports.js'], true);

      $locale_data = $this->system_core->locale->get_data();

      $reports_security_assembled_array = [];
      $reports_security_array = (new SystemCoreReports($this->system_core))->get_by_type_ids([
        SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
        SystemCoreReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
      ], ['limit' => 50]);

      $reports_common_assembled_array = [];
      $reports_common_array = (new SystemCoreReports($this->system_core))->get_by_type_ids([
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
        SystemCoreReport::REPORT_TYPE_ID_AP_ENTRY_DELETED
      ], ['limit' => 50]);

      foreach ($reports_security_array as $report) {
        $report->init_data(['metadata', 'variables', 'created_unix_timestamp']);
        
        $report_category_name = 'security';
        $report_variables = $report->get_variables();

        array_push($reports_security_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/reports/listItem.tpl', [
          'REPORT_CATEGORY_NAME' => $report_category_name,
          'REPORT_CONTENT' =>TemplateCollector::assembly(TemplateCollector::assembly_locale($report->get_content(), $this->system_core->locale), [
            'CLIENT_IP' => isset($report_variables['clientIP']) ? $report_variables['clientIP'] : '[ ??? ]',
            'DATE' => isset($report_variables['date']) ? $report_variables['date'] : '[ ??? ]',
            'ENTRY_TITLE' => isset($report_variables['entryTitle']) ? $report_variables['entryTitle'] : '[ ??? ]',
          ]),
          'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->get_created_unix_timestamp()),
        ]));
      }

      foreach ($reports_common_array as $report) {
        $report->init_data(['metadata', 'variables', 'created_unix_timestamp']);

        $report_category_name = 'common';
        $report_variables = $report->get_variables();

        array_push($reports_common_assembled_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/reports/listItem.tpl', [
          'REPORT_CATEGORY_NAME' => $report_category_name,
          'REPORT_CONTENT' =>TemplateCollector::assembly(TemplateCollector::assembly_locale($report->get_content(), $this->system_core->locale), [
            'CLIENT_IP' => isset($report_variables['clientIP']) ? $report_variables['clientIP'] : '[ ??? ]',
            'DATE' => isset($report_variables['date']) ? $report_variables['date'] : '[ ??? ]',
            'ENTRY_TITLE' => isset($report_variables['entryTitle']) ? $report_variables['entryTitle'] : '[ ??? ]',
          ]),
          'REPORT_CREATED_TIMESTAMP' => date('d.m.Y H:i:s', $report->get_created_unix_timestamp()),
        ]));
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/reports.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'reports',
        'REPORTS_SECURITY_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/reports/list.tpl', [
          'REPORTS_LIST_ITEMS' => implode($reports_security_assembled_array)
        ]),
        'REPORTS_COMMON_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/reports/list.tpl', [
          'REPORTS_LIST_ITEMS' => implode($reports_common_assembled_array)
        ])
      ]);
    }
  }
}
?>
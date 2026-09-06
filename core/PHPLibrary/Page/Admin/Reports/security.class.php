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

namespace core\PHPLibrary\Page\Admin\Reports;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\Report as CMSReport;
use \core\PHPLibrary\SystemCore\Reports as CMSReports;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\User as User;

/**
 * Class ReportsSecurity
 */
class ReportsSecurity implements ReportsPageInterface
{
  const FORM_PATH = 'templates/page/reports';

  public string $title;
  public string $description;
  public string $assembled = '';
  protected ?User $viewer = null;
  protected array $localeData = [];

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   * @param string $name
   * 
   * @return void
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public string $name
  ) {}

  /**
   * Установить заголовок
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  /**
   * Установить описание
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }

  /**
   * Получить заголовок
   * 
   * @return string
   */
  public function getTitle() : string
  {
    return $this->title;
  }

  /**
   * Получить описание
   * 
   * @return string
   */
  public function getDescription() : string
  {
    return $this->description;
  }

  /**
   * Установить пользователя для расшифровки ПДн
   * 
   * @param User $viewer
   * @return void
   */
  public function setViewer(User $viewer) : void
  {
    $this->viewer = $viewer;
  }

  /**
   * Получить объекты отчетов за период
   * 
   * @param array $typeIDs
   * @return array
   */
  private function getReportsByTypes(array $typeIDs) : array
  {
    $startPeriodUnix = time() - 604800;
    $endPeriodUnix = time();

    $reports = CMSReports::getAllByPeriod(
      $this->CMSCore,
      $startPeriodUnix,
      $endPeriodUnix,
      ['id', 'metadata', 'variables']
    );

    $filtered = [];
    foreach ($reports as $report) {
      $typeID = $report->getTypeID();
      if (in_array($typeID, $typeIDs, true)) {
        $filtered[] = $report;
      }
    }

    return $filtered;
  }

  /**
   * Получить имя типа отчета
   */
  private function getReportTypeName(int $typeID): string
  {
    $reflectionClass = new \ReflectionClass('\core\PHPLibrary\SystemCore\Report');
    $constants = $reflectionClass->getConstants();

    foreach ($constants as $name => $value) {
      if ($value === $typeID) {
        return $name;
      }
    }

    return 'UNKNOWN';
  }

  /**
   * Получить логин пользователя по ID
   */
  private function getUserLogin(int $userID): string
  {
    if ($userID <= 0) {
      return 'system';
    }

    try {
      $user = new \core\PHPLibrary\User($this->CMSCore, $userID);
      $user->initData(['login']);
      return $user->getLogin();
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Форматировать описание отчета с использованием локализации
   */
  private function formatReportDescription(CMSReport $report): string
  {
    $typeID = $report->getTypeID();
    $variables = $this->viewer !== null
      ? $report->getVariables($this->viewer)
      : $report->getVariables();

    $localeData = $this->CMSCore->locale->getData();
    $typeName = $this->getReportTypeName($typeID);
    $template = $localeData[$typeName] ?? '';

    if (empty($template)) {
      return $typeName . ' (ID: ' . ($variables['id'] ?? '?') . ')';
    }

    $replacements = [
      '{CLIENT_IP}' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
      '{USER_LOGIN}' => $this->getUserLogin($variables['userID'] ?? 0),
      '{USER_ID}' => $variables['userID'] ?? 0,
    ];

    return str_replace(
      array_keys($replacements),
      array_values($replacements),
      $template
    );
  }

  /**
   * Собрать шаблон
   * 
   * @param array $templateValues
   * 
   * @return void
   */
  public function assembly(array $templateValues = []) : void
  {
    $templatePath = 'templates/page/reports/' . $this->name . '.tpl';
    $localeData = $this->CMSCore->locale->getData();

    // Типы отчетов по безопасности
    $securityTypeIDs = [
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS,
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS,
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL,
      CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
      CMSReport::REPORT_TYPE_ID_AP_VIEWING_LOGS,
    ];

    $reports = $this->getReportsByTypes($securityTypeIDs);

    // Статистика безопасности
    $stats = [
      'success_auth_admin' => 0,
      'fail_auth_admin' => 0,
      'success_auth_site' => 0,
      'fail_auth_site' => 0,
      'banned' => 0,
      'unbanned' => 0,
      'personal_data_views' => 0,
      'logs_views' => 0,
    ];

    $uniqueIpsSuccess = [];
    $uniqueIpsFail = [];

    $items = [];
    foreach ($reports as $report) {
      $typeID = $report->getTypeID();
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();

      $ip = $variables['ip'] ?? $variables['clientIP'] ?? null;

      // Сбор статистики
      switch ($typeID) {
        case CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS:
          $stats['success_auth_admin']++;
          if ($ip) $uniqueIpsSuccess[] = $ip;
          break;
        case CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL:
          $stats['fail_auth_admin']++;
          if ($ip) $uniqueIpsFail[] = $ip;
          break;
        case CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS:
          $stats['success_auth_site']++;
          if ($ip) $uniqueIpsSuccess[] = $ip;
          break;
        case CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL:
          $stats['fail_auth_site']++;
          if ($ip) $uniqueIpsFail[] = $ip;
          break;
        case CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED:
          $stats['banned']++;
          break;
        case CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED:
          $stats['unbanned']++;
          break;
        case CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED:
          $stats['personal_data_views']++;
          break;
        case CMSReport::REPORT_TYPE_ID_AP_VIEWING_LOGS:
          $stats['logs_views']++;
          break;
      }

      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      $typeName = $this->getReportTypeName($typeID);
      $typeLabel = $localeData[$typeName] ?? $typeName;

      // Определяем статус события
      $statusClass = 'info';
      if (in_array($typeID, [
        CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS,
        CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS,
        CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED
      ])) {
        $statusClass = 'success';
      } elseif (in_array($typeID, [
        CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
        CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL,
        CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED
      ])) {
        $statusClass = 'danger';
      }

      $items[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/reports/item.tpl',
        [
          'REPORT_TYPE' => $typeLabel,
          'REPORT_DESCRIPTION' => $description,
          'REPORT_DATE' => $createdDate,
          'REPORT_IP' => $ip ?? '0.0.0.0',
          'REPORT_STATUS_CLASS' => $statusClass
        ]
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      $templatePath,
      [
        'TOTAL_SUCCESS_AUTH_ADMIN' => $stats['success_auth_admin'],
        'TOTAL_FAIL_AUTH_ADMIN' => $stats['fail_auth_admin'],
        'TOTAL_SUCCESS_AUTH_SITE' => $stats['success_auth_site'],
        'TOTAL_FAIL_AUTH_SITE' => $stats['fail_auth_site'],
        'TOTAL_BANNED' => $stats['banned'],
        'TOTAL_UNBANNED' => $stats['unbanned'],
        'TOTAL_PERSONAL_DATA_VIEWS' => $stats['personal_data_views'],
        'TOTAL_LOGS_VIEWS' => $stats['logs_views'],
        'UNIQUE_IPS_SUCCESS' => !empty($uniqueIpsSuccess) ? implode(', ', array_unique($uniqueIpsSuccess)) : '-',
        'UNIQUE_IPS_FAIL' => !empty($uniqueIpsFail) ? implode(', ', array_unique($uniqueIpsFail)) : '-',
        'REPORTS_ITEMS' => implode("\n", $items),
        'TOTAL_COUNT' => count($items)
      ]
    );
  }
}
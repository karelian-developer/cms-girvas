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
 * Class ReportsBase
 */
class ReportsBase implements ReportsPageInterface
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
   * Получить объекты отчетов
   * 
   * @return array
   */
  private function getAllReportsObjectsByPeriod() : array
  {
    $startPeriodUnix = time() - 604800;
    $endPeriodUnix = time();

    return CMSReports::getAllByPeriod(
      $this->CMSCore,
      $startPeriodUnix,
      $endPeriodUnix,
      ['id', 'metadata', 'variables']
    );
  }

  private function filterReports(array $reportsObjects, array $typeIDs) : array
  {
    foreach ($reportsObjects as $index => $report) {
      $typeID = $report->getTypeID();
      $typeID = is_numeric($report->getTypeID())
        ? (int)$report->getTypeID()
        : 0;

      if (!in_array($typeID, $typeIDs, true)) {
        unset($reportsObjects[$index]);
      }
    }

    return $reportsObjects;
  }

  private function extractClientIPs(array $reports) : array {
    $IPs = [];

    foreach ($reports as $report) {
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();

      if (isset($variables['ip'])) {
        $IPs[] = $variables['ip'];
      } elseif (isset($variables['clientIP'])) {
        $IPs[] = $variables['clientIP'];
      }
    }

    return array_unique($IPs);
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
   * Получить название записи по ID
   */
  private function getEntryTitle(int $entryID): string
  {
    if ($entryID <= 0) {
      return '';
    }

    try {
      $entry = new \core\PHPLibrary\Entry($this->CMSCore, $entryID);
      $entry->initData(['texts']);
      $localeName = $this->CMSCore->locale->getName();
      return $entry->getTitle($localeName);
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

    // Заменяем плейсхолдеры на значения
    $replacements = [
      '{ENTRY_TITLE}' => $this->getEntryTitle($variables['entryID'] ?? 0),
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
   * Собрать шаблон
   * 
   * @param array $templateValues
   * 
   * @return void
   */
  public function assembly(array $templateValues = []) : void
  {
    $templatePath = 'templates/page/reports/' . $this->name . '.tpl';
    $reports = $this->getAllReportsObjectsByPeriod();
    $localeData = $this->CMSCore->locale->getData();

    // Типы отчетов для общей сводки
    $reportsAll = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_USER_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_USER_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_USER_DELETED
    ]);

    $reportsEntriesCreated = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED
    ]);

    $reportsPagesCreated = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED
    ]);

    $reportsMediaUploaded = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED
    ]);

    $reportsUsersRegistered = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_BASE_USER_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_USER_CREATED
    ]);

    $reportsUsersEdited = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_USER_EDITED
    ]);

    $reportsUsersDeleted = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_USER_DELETED
    ]);

    $reportsSecurityAdminAuthFail = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL
    ]);

    $reportsSecurityAdminAuthSuccess = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS
    ]);

    $reportsSecurityBaseAuthFail = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL
    ]);

    $reportsSecurityBaseAuthSuccess = $this->filterReports($reports, [
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS
    ]);

    $totalActions = count($reportsAll);
    $totalEntriesCreatedActions = count($reportsEntriesCreated);
    $totalPagesCreatedActions = count($reportsPagesCreated);
    $totalMediaUploadedActions = count($reportsMediaUploaded);
    $totalUsersRegisteredActions = count($reportsUsersRegistered);
    $totalUsersEditedActions = count($reportsUsersEdited);
    $totalUsersDeletedActions = count($reportsUsersDeleted);

    $totalSecurityAdminAuthFailActions = count($reportsSecurityAdminAuthFail);
    $totalSecurityAdminAuthSuccessActions = count($reportsSecurityAdminAuthSuccess);
    $totalSecurityBaseAuthFailActions = count($reportsSecurityBaseAuthFail);
    $totalSecurityBaseAuthSuccessActions = count($reportsSecurityBaseAuthSuccess);

    $IPsWithSuccessfulAuth = $this->extractClientIPs($reportsSecurityAdminAuthSuccess);
    $IPsWithFailAuth = $this->extractClientIPs($reportsSecurityAdminAuthFail);

    $IPsWithSuccessfulAuthImploded = !empty($IPsWithSuccessfulAuth)
      ? implode(', ', $IPsWithSuccessfulAuth)
      : '-';
    $IPsWithFailAuthImploded = !empty($IPsWithFailAuth)
      ? implode(', ', $IPsWithFailAuth)
      : '-';

    // Сборка списка последних событий (для отображения в сводке)
    $recentItems = [];
    $recentReports = array_slice($reportsAll, 0, 10);
    foreach ($recentReports as $report) {
      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      $typeName = $this->getReportTypeName($report->getTypeID());
      $typeLabel = $localeData[$typeName] ?? $typeName;

      $recentItems[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/reports/item.tpl',
        [
          'REPORT_TYPE' => $typeLabel,
          'REPORT_DESCRIPTION' => $description,
          'REPORT_DATE' => $createdDate,
          'REPORT_IP' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0'
        ]
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      $templatePath,
      [
        'REPORT_NAME' => $this->name,
        'TOTAL_ACTIONS' => $totalActions,
        'TOTAL_ENTRIES_CREATED' => $totalEntriesCreatedActions,
        'TOTAL_PAGES_CREATED' => $totalPagesCreatedActions,
        'TOTAL_MEDIA_UPLOADS' => $totalMediaUploadedActions,
        'TOTAL_USERS_CREATED' => $totalUsersRegisteredActions,
        'TOTAL_USERS_EDITED' => $totalUsersEditedActions,
        'TOTAL_USERS_DELETED' => $totalUsersDeletedActions,
        'TOTAL_SUCCESSFUL_AUTH_ON_THE_SITE' => $totalSecurityBaseAuthSuccessActions,
        'TOTAL_UNSUCCESSFUL_AUTH_ON_THE_SITE' => $totalSecurityBaseAuthFailActions,
        'TOTAL_SUCCESSFUL_AUTH_ON_THE_ADMIN_PANEL' => $totalSecurityAdminAuthSuccessActions,
        'TOTAL_UNSUCCESSFUL_AUTH_ON_THE_ADMIN_PANEL' => $totalSecurityAdminAuthFailActions,
        'TOTAL_IP_ADDRESS_WITH_SUCCESSFUL_AUTH_ON_THE_ADMIN_PANEL' => $IPsWithSuccessfulAuthImploded,
        'TOTAL_IP_ADDRESS_WITH_UNSUCCESSFUL_AUTH_ON_THE_ADMIN_PANEL' => $IPsWithFailAuthImploded,
        'RECENT_EVENTS' => implode("\n", $recentItems)
      ]
    );
  }
}
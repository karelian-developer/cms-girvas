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
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Page as Page;

/**
 * Class ReportsBase
 * 
 * Общая сводка по всем событиям за последние 7 дней
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
   * Получить объекты отчетов за период
   * 
   * @return array
   */
  private function getReportsByPeriod() : array
  {
    $startPeriodUnix = time() - 604800; // 7 дней
    $endPeriodUnix = time();

    return CMSReports::getAllByPeriod(
      $this->CMSCore,
      $startPeriodUnix,
      $endPeriodUnix,
      ['id', 'metadata', 'variables', 'createdUnixTimestamp']
    );
  }

  /**
   * Фильтровать отчеты по типам
   */
  private function filterReports(array $reportsObjects, array $typeIDs) : array
  {
    $filtered = [];
    foreach ($reportsObjects as $report) {
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
      $user = new User($this->CMSCore, $userID);
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
      $entry = new Entry($this->CMSCore, $entryID);
      $entry->initData(['texts']);
      $localeName = $this->CMSCore->locale->getName();
      return $entry->getTitle($localeName);
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название страницы по ID
   */
  private function getPageTitle(int $pageID): string
  {
    if ($pageID <= 0) {
      return '';
    }

    try {
      $page = new Page($this->CMSCore, $pageID);
      $page->initData(['texts']);
      $localeName = $this->CMSCore->locale->getName();
      return $page->getTitle($localeName);
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

    $typeName = $this->getReportTypeName($typeID);
    $template = $this->localeData[$typeName] ?? '';

    if (empty($template)) {
      return $typeName . ' (ID: ' . ($variables['id'] ?? '?') . ')';
    }

    $replacements = [
      '{ENTRY_TITLE}' => $this->getEntryTitle($variables['entryID'] ?? $variables['id'] ?? 0),
      '{PAGE_TITLE}' => $this->getPageTitle($variables['pageID'] ?? $variables['id'] ?? 0),
      '{CATEGORY_TITLE}' => $variables['categoryTitle'] ?? $variables['name'] ?? '',
      '{FORM_TITLE}' => $variables['formTitle'] ?? $variables['name'] ?? '',
      '{BLOCK_TITLE}' => $variables['blockTitle'] ?? $variables['name'] ?? '',
      '{SAMPLE_TITLE}' => $variables['sampleTitle'] ?? $variables['name'] ?? '',
      '{FILE_NAME}' => $variables['fileName'] ?? $variables['name'] ?? '',
      '{CLIENT_IP}' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
      '{USER_LOGIN}' => $this->getUserLogin($variables['userID'] ?? 0),
      '{CREATOR_LOGIN}' => $this->getUserLogin($variables['createdByID'] ?? 0),
      '{UPDATER_LOGIN}' => $this->getUserLogin($variables['updatedByID'] ?? 0),
      '{DELETER_LOGIN}' => $this->getUserLogin($variables['deletedByID'] ?? 0),
      '{TARGET_USER_LOGIN}' => $this->getUserLogin($variables['targetUserID'] ?? 0),
      '{VIEWER_LOGIN}' => $this->getUserLogin($variables['viewedByID'] ?? 0),
      '{COUNT}' => $variables['count'] ?? 0,
    ];

    return str_replace(
      array_keys($replacements),
      array_values($replacements),
      $template
    );
  }

  /**
   * Группировка отчетов по часам для графика
   */
  private function groupReportsByHour(array $reports): array
  {
    $grouped = array_fill(0, 24, 0);
    
    foreach ($reports as $report) {
      $hour = (int) date('H', $report->getCreatedUnixTimestamp());
      $grouped[$hour]++;
    }
    
    return $grouped;
  }

  /**
   * Группировка отчетов по дням для графика
   */
  private function groupReportsByDay(array $reports): array
  {
    $grouped = [];
    $now = time();
    
    for ($i = 6; $i >= 0; $i--) {
      $day = strtotime('-' . $i . ' days', $now);
      $key = date('Y-m-d', $day);
      $grouped[$key] = 0;
    }
    
    foreach ($reports as $report) {
      $key = date('Y-m-d', $report->getCreatedUnixTimestamp());
      if (isset($grouped[$key])) {
        $grouped[$key]++;
      }
    }
    
    return $grouped;
  }

  /**
   * Получить топ активных пользователей
   */
  private function getTopUsers(array $reports, int $limit = 5): array
  {
    $userActions = [];
    
    foreach ($reports as $report) {
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();
      
      $userID = $variables['userID'] ?? $variables['createdByID'] ?? $variables['updatedByID'] ?? $variables['deletedByID'] ?? 0;
      
      if ($userID > 0) {
        if (!isset($userActions[$userID])) {
          $userActions[$userID] = 0;
        }
        $userActions[$userID]++;
      }
    }
    
    arsort($userActions);
    return array_slice($userActions, 0, $limit, true);
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
    $this->localeData = $this->CMSCore->locale->getData();
    
    // Получаем все отчеты за период
    $reports = $this->getReportsByPeriod();

    // ============================================================
    // КАТЕГОРИИ СОБЫТИЙ
    // ============================================================
    
    // Контент
    $contentTypeIDs = [
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED,
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_FORM_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_FORM_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED,
    ];
    
    // Пользователи
    $userTypeIDs = [
      CMSReport::REPORT_TYPE_ID_BASE_USER_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_USER_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_USER_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_USER_DELETED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED,
      CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
    ];
    
    // Безопасность
    $securityTypeIDs = [
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS,
      CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS,
      CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL,
      CMSReport::REPORT_TYPE_ID_AP_VIEWING_LOGS,
    ];

    // Все события
    $allReports = $reports;
    $contentReports = $this->filterReports($reports, $contentTypeIDs);
    $userReports = $this->filterReports($reports, $userTypeIDs);
    $securityReports = $this->filterReports($reports, $securityTypeIDs);

    // ============================================================
    // СТАТИСТИКА ПО ТИПАМ
    // ============================================================
    
    // Контент
    $statsContent = [
      'entries_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED])),
      'entries_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED])),
      'entries_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED])),
      'pages_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED])),
      'pages_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_PAGE_EDITED])),
      'pages_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED])),
      'media_uploaded' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED])),
      'media_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED])),
      'categories_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_CREATED])),
      'categories_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_EDITED])),
      'categories_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED])),
      'samples_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_CREATED])),
      'samples_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_EDITED])),
      'samples_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED])),
      'forms_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FORM_CREATED])),
      'forms_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FORM_EDITED])),
      'forms_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED])),
      'comments_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_CREATED])),
      'comments_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_EDITED])),
      'comments_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_DELETED])),
      'blocks_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED])),
      'blocks_edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED])),
      'blocks_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED])),
    ];

    // Пользователи
    $statsUsers = [
      'registered' => count($this->filterReports($reports, [
        CMSReport::REPORT_TYPE_ID_BASE_USER_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_USER_CREATED
      ])),
      'edited' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USER_EDITED])),
      'deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USER_DELETED])),
      'banned' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED])),
      'unbanned' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED])),
      'personal_data_views' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED])),
    ];

    // Безопасность
    $statsSecurity = [
      'auth_success_admin' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS])),
      'auth_fail_admin' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL])),
      'auth_success_site' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS])),
      'auth_fail_site' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL])),
      'logs_views' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_VIEWING_LOGS])),
    ];

    // ============================================================
    // ГРАФИКИ (группировка по дням и часам)
    // ============================================================
    
    $activityByDay = $this->groupReportsByDay($reports);
    $activityByHour = $this->groupReportsByHour($reports);

    // ============================================================
    // ТОП АКТИВНЫХ ПОЛЬЗОВАТЕЛЕЙ
    // ============================================================
    
    $topUsers = $this->getTopUsers($reports, 5);

    // ============================================================
    // ПОСЛЕДНИЕ СОБЫТИЯ
    // ============================================================
    
    $recentItems = [];
    $recentReports = array_slice($reports, 0, 15);
    foreach ($recentReports as $report) {
      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      $typeName = $this->getReportTypeName($report->getTypeID());
      $typeLabel = $this->localeData[$typeName] ?? $typeName;
      
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();

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

    // ============================================================
    // СБОРКА ШАБЛОНА
    // ============================================================

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      $templatePath,
      [
        // Общая статистика
        'TOTAL_ACTIONS' => count($reports),
        'TOTAL_CONTENT_ACTIONS' => count($contentReports),
        'TOTAL_USER_ACTIONS' => count($userReports),
        'TOTAL_SECURITY_ACTIONS' => count($securityReports),

        // Статистика по контенту
        'CONTENT_ENTRIES_CREATED' => $statsContent['entries_created'],
        'CONTENT_ENTRIES_EDITED' => $statsContent['entries_edited'],
        'CONTENT_ENTRIES_DELETED' => $statsContent['entries_deleted'],
        'CONTENT_PAGES_CREATED' => $statsContent['pages_created'],
        'CONTENT_PAGES_EDITED' => $statsContent['pages_edited'],
        'CONTENT_PAGES_DELETED' => $statsContent['pages_deleted'],
        'CONTENT_MEDIA_UPLOADED' => $statsContent['media_uploaded'],
        'CONTENT_MEDIA_DELETED' => $statsContent['media_deleted'],
        'CONTENT_CATEGORIES_CREATED' => $statsContent['categories_created'],
        'CONTENT_CATEGORIES_EDITED' => $statsContent['categories_edited'],
        'CONTENT_CATEGORIES_DELETED' => $statsContent['categories_deleted'],
        'CONTENT_SAMPLES_CREATED' => $statsContent['samples_created'],
        'CONTENT_SAMPLES_EDITED' => $statsContent['samples_edited'],
        'CONTENT_SAMPLES_DELETED' => $statsContent['samples_deleted'],
        'CONTENT_FORMS_CREATED' => $statsContent['forms_created'],
        'CONTENT_FORMS_EDITED' => $statsContent['forms_edited'],
        'CONTENT_FORMS_DELETED' => $statsContent['forms_deleted'],
        'CONTENT_COMMENTS_CREATED' => $statsContent['comments_created'],
        'CONTENT_COMMENTS_EDITED' => $statsContent['comments_edited'],
        'CONTENT_COMMENTS_DELETED' => $statsContent['comments_deleted'],
        'CONTENT_BLOCKS_CREATED' => $statsContent['blocks_created'],
        'CONTENT_BLOCKS_EDITED' => $statsContent['blocks_edited'],
        'CONTENT_BLOCKS_DELETED' => $statsContent['blocks_deleted'],

        // Статистика по пользователям
        'USERS_REGISTERED' => $statsUsers['registered'],
        'USERS_EDITED' => $statsUsers['edited'],
        'USERS_DELETED' => $statsUsers['deleted'],
        'USERS_BANNED' => $statsUsers['banned'],
        'USERS_UNBANNED' => $statsUsers['unbanned'],
        'USERS_PERSONAL_DATA_VIEWS' => $statsUsers['personal_data_views'],

        // Статистика по безопасности
        'SECURITY_AUTH_SUCCESS_ADMIN' => $statsSecurity['auth_success_admin'],
        'SECURITY_AUTH_FAIL_ADMIN' => $statsSecurity['auth_fail_admin'],
        'SECURITY_AUTH_SUCCESS_SITE' => $statsSecurity['auth_success_site'],
        'SECURITY_AUTH_FAIL_SITE' => $statsSecurity['auth_fail_site'],
        'SECURITY_LOGS_VIEWS' => $statsSecurity['logs_views'],

        // Графики
        'ACTIVITY_BY_DAY' => json_encode($activityByDay),
        'ACTIVITY_BY_HOUR' => json_encode($activityByHour),

        // Топ пользователей
        'TOP_USERS' => $this->formatTopUsers($topUsers),

        // Последние события
        'RECENT_EVENTS' => implode("\n", $recentItems)
      ]
    );
  }

  /**
   * Форматирование топа пользователей для шаблона
   */
  private function formatTopUsers(array $topUsers): string
  {
    if (empty($topUsers)) {
      return '<li class="report-section__list-item">' . $this->localeData['PAGE_REPORTS_TOP_USERS_EMPTY'] ?? 'Нет данных' . '</li>';
    }

    $items = [];
    $rank = 1;
    foreach ($topUsers as $userID => $count) {
      $login = $this->getUserLogin($userID);
      $items[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/reports/top_user_item.tpl',
        [
          'USER_RANK' => $rank,
          'USER_LOGIN' => $login,
          'USER_ACTIONS' => $count
        ]
      );
      $rank++;
    }

    return implode("\n", $items);
  }
}
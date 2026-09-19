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
   * Получить короткое название типа отчета для отображения
   */
  private function getReportTypeLabel(int $typeID): string
  {
    $typeName = $this->getReportTypeName($typeID);
    $shortTypeName = str_replace('REPORT_TYPE_ID_', '', $typeName);
    $labelKey = 'REPORT_TYPE_NAME_' . $shortTypeName;
    
    return $this->localeData[$labelKey] ?? $typeName;
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

    // Текущая локаль админки
    $currentLocale = $this->CMSCore->locale->getName();

    // Хелпер: получить заголовок из массива по локалям
    $getLocalizedTitle = function($titlesKey, $singleKey, $idKey, $dbGetter) use ($variables, $currentLocale) {
      // 1. Пробуем взять из массива по текущей локали
      if (isset($variables[$titlesKey]) && is_array($variables[$titlesKey])) {
        if (!empty($variables[$titlesKey][$currentLocale])) {
          return $variables[$titlesKey][$currentLocale];
        }
        // 2. Fallback на первый непустой язык
        foreach ($variables[$titlesKey] as $title) {
          if (!empty($title)) return $title;
        }
      }
      // 3. Пробуем одиночный ключ (старый формат)
      if (!empty($variables[$singleKey])) {
        return $variables[$singleKey];
      }
      // 4. Fallback на БД
      return $dbGetter($variables[$idKey] ?? $variables['id'] ?? 0);
    };

    $replacements = [
      '{ENTRY_TITLE}' => $getLocalizedTitle('entryTitles', 'entryTitle', 'entryID', fn($id) => $this->getEntryTitle($id)),
      '{PAGE_TITLE}' => $getLocalizedTitle('pageTitles', 'pageTitle', 'pageID', fn($id) => $this->getPageTitle($id)),
      '{CATEGORY_TITLE}' => $getLocalizedTitle('categoryTitles', 'categoryTitle', 'categoryID', fn($id) => $this->getCategoryTitle($id)),
      '{FORM_TITLE}' => $getLocalizedTitle('formTitles', 'formTitle', 'formID', fn($id) => $this->getFormTitle($id)),
      '{BLOCK_TITLE}' => $getLocalizedTitle('blockTitles', 'blockTitle', 'blockID', fn($id) => $this->getBlockTitle($id)),
      '{SAMPLE_TITLE}' => $getLocalizedTitle('sampleTitles', 'sampleTitle', 'sampleID', fn($id) => $this->getSampleTitle($id)),
      '{FEED_TITLE}' => $getLocalizedTitle('feedTitles', 'feedTitle', 'feedID', fn($id) => $this->getFeedTitle($id)),
      '{GROUP_TITLE}' => $getLocalizedTitle('groupTitles', 'groupTitle', 'groupID', fn($id) => $this->getGroupTitle($id)),
      '{FILE_NAME}' => $variables['fileName'] ?? $variables['name'] ?? '',
      '{CLIENT_IP}' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
      '{USER_LOGIN}' => $variables['userLogin'] ?? $this->getUserLogin($variables['userID'] ?? 0),
      '{CREATOR_LOGIN}' => $variables['createdByLogin'] ?? $this->getUserLogin($variables['createdByID'] ?? 0),
      '{UPDATER_LOGIN}' => $variables['updatedByLogin'] ?? $this->getUserLogin($variables['updatedByID'] ?? 0),
      '{DELETER_LOGIN}' => $variables['deletedByLogin'] ?? $this->getUserLogin($variables['deletedByID'] ?? 0),
      '{TARGET_USER_LOGIN}' => $variables['targetUserLogin'] ?? $this->getUserLogin($variables['targetUserID'] ?? 0),
      '{SUBJECT_LOGIN}' => $variables['subjectUserLogin'] ?? $this->getUserLogin($variables['subjectUserID'] ?? 0),
      '{EXPORTED_BY_LOGIN}' => $variables['exportedByLogin'] ?? $this->getUserLogin($variables['exportedByID'] ?? 0),
      '{VIEWER_LOGIN}' => $variables['viewedByLogin'] ?? $this->getUserLogin($variables['viewedByID'] ?? 0),
      // ============================================================
      // СОГЛАСИЯ (152-ФЗ)
      // ============================================================
      '{DOCUMENT_TITLE}' => (function() use ($variables, $currentLocale) {
        if (isset($variables['documentTitles']) && is_array($variables['documentTitles'])) {
          if (!empty($variables['documentTitles'][$currentLocale])) {
            return $variables['documentTitles'][$currentLocale];
          }
          foreach ($variables['documentTitles'] as $title) {
            if (!empty($title)) return $title;
          }
        }
        if (!empty($variables['documentTitle'])) {
          return $variables['documentTitle'];
        }
        $dbTitle = $this->getPageStaticTitle($variables['pageStaticID'] ?? 0);
        if (!empty($dbTitle)) return $dbTitle;
        return $variables['documentKey'] ?? '';
      })(),
      '{DOCUMENT_KEY}' => $variables['documentKey'] ?? '',
      '{DOCUMENT_VERSION}' => $variables['documentVersion'] ?? '',
      '{LOCALE}' => $variables['locale'] ?? '',
      '{FORM_ID}' => $variables['formID'] ?? 0,
      '{FORM_REPORT_ID}' => $variables['formReportID'] ?? 0,
      '{PAGE_STATIC_ID}' => $variables['pageStaticID'] ?? 0,
      '{COUNT}' => $variables['count'] ?? 0,
    ];

    return str_replace(
      array_keys($replacements),
      array_values($replacements),
      $template
    );
  }

  /**
   * Получить название категории по ID
   */
  private function getCategoryTitle(int $categoryID): string
  {
    if ($categoryID <= 0) return '';
    try {
      $category = new \core\PHPLibrary\EntryCategory($this->CMSCore, $categoryID);
      $category->initData(['texts']);
      return $category->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название формы по ID
   */
  private function getFormTitle(int $formID): string
  {
    if ($formID <= 0) return '';
    try {
      $form = new \core\PHPLibrary\Form($this->CMSCore, $formID);
      $form->initData(['texts']);
      return $form->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название контент-блока по ID
   */
  private function getBlockTitle(int $blockID): string
  {
    if ($blockID <= 0) return '';
    try {
      $block = new \core\PHPLibrary\ContentBlock($this->CMSCore, $blockID);
      $block->initData(['texts']);
      return $block->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название выборки по ID
   */
  private function getSampleTitle(int $sampleID): string
  {
    if ($sampleID <= 0) return '';
    try {
      $sample = new \core\PHPLibrary\EntriesSample($this->CMSCore, $sampleID);
      $sample->initData(['texts']);
      return $sample->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название фида по ID
   */
  private function getFeedTitle(int $feedID): string
  {
    if ($feedID <= 0) return '';
    try {
      $feed = new \core\PHPLibrary\Feed($this->CMSCore, $feedID);
      $feed->initData(['texts']);
      return $feed->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
  }

  /**
   * Получить название группы по ID
   */
  private function getGroupTitle(int $groupID): string
  {
    if ($groupID <= 0) return '';
    try {
      $group = new \core\PHPLibrary\UserGroup($this->CMSCore, $groupID);
      $group->initData(['texts']);
      return $group->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return 'unknown';
    }
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
    
    $contentTypeIDs  = CMSReport::getTypeIDsByCategory(CMSReport::CATEGORY_CONTENT);
    $securityTypeIDs = CMSReport::getTypeIDsByCategory(CMSReport::CATEGORY_SECURITY);

    // Все события
    $allReports = $reports;
    $contentReports = $this->filterReports($reports, $contentTypeIDs);
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

    usort($reports, function($a, $b) {
      return $b->getCreatedUnixTimestamp() <=> $a->getCreatedUnixTimestamp();
    });

    $recentItems = [];
    $recentReports = array_slice($reports, 0, 15);

    foreach ($recentReports as $report) {
      $typeName = $this->getReportTypeName($report->getTypeID());
      $typeLabel = $this->getReportTypeLabel($report->getTypeID());
      
      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      
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
   * Получить заголовок статической страницы (юридического документа) по ID
   *
   * @param int $pageStaticID
   * @return string
   */
  private function getPageStaticTitle(int $pageStaticID): string
  {
    if ($pageStaticID <= 0) {
      return '';
    }

    try {
      $pageStatic = new \core\PHPLibrary\PageStatic($this->CMSCore, $pageStaticID);
      $pageStatic->initData(['texts']);
      return $pageStatic->getTitle($this->CMSCore->locale->getName());
    } catch (\Exception $e) {
      return '';
    }
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
        'templates/page/reports/base/topUserItem.tpl',
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
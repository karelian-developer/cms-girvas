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
 * Class ReportsSecurity
 * 
 * Отчеты по безопасности за последние 7 дней
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
      ['id', 'metadata', 'variables', 'createdUnixTimestamp']
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
      if (isset($variables[$titlesKey]) && is_array($variables[$titlesKey])) {
        if (!empty($variables[$titlesKey][$currentLocale])) {
          return $variables[$titlesKey][$currentLocale];
        }
        foreach ($variables[$titlesKey] as $title) {
          if (!empty($title)) return $title;
        }
      }
      if (!empty($variables[$singleKey])) {
        return $variables[$singleKey];
      }
      return $dbGetter($variables[$idKey] ?? $variables['id'] ?? 0);
    };

    $replacements = [
      '{ENTRY_TITLE}' => $getLocalizedTitle('entryTitles', 'entryTitle', 'entryID', fn($id) => $this->getEntryTitle($id)),
      '{PAGE_TITLE}' => $getLocalizedTitle('pageTitles', 'pageTitle', 'pageID', fn($id) => $this->getPageTitle($id)),
      '{CATEGORY_TITLE}' => $getLocalizedTitle('categoryTitles', 'categoryTitle', 'categoryID', fn($id) => $this->getCategoryTitle($id)),
      '{FORM_TITLE}' => $getLocalizedTitle('formTitles', 'formTitle', 'formID', fn($id) => $this->getFormTitle($id)),
      '{BLOCK_TITLE}' => $getLocalizedTitle('blockTitles', 'blockTitle', 'blockID', fn($id) => $this->getBlockTitle($id)),
      '{SAMPLE_TITLE}' => $getLocalizedTitle('sampleTitles', 'sampleTitle', 'sampleID', fn($id) => $this->getSampleTitle($id)),
      '{GROUP_TITLE}' => $getLocalizedTitle('groupTitles', 'groupTitle', 'groupID', fn($id) => $this->getGroupTitle($id)),
      '{FILE_NAME}' => $variables['fileName'] ?? $variables['name'] ?? '',
      '{CLIENT_IP}' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
      '{USER_LOGIN}' => $variables['userLogin'] ?? $this->getUserLogin($variables['userID'] ?? 0),
      '{CREATOR_LOGIN}' => $variables['createdByLogin'] ?? $this->getUserLogin($variables['createdByID'] ?? 0),
      '{UPDATER_LOGIN}' => $variables['updatedByLogin'] ?? $this->getUserLogin($variables['updatedByID'] ?? 0),
      '{DELETER_LOGIN}' => $variables['deletedByLogin'] ?? $this->getUserLogin($variables['deletedByID'] ?? 0),
      '{TARGET_USER_LOGIN}' => $variables['targetUserLogin'] ?? $this->getUserLogin($variables['targetUserID'] ?? 0),
      '{VIEWER_LOGIN}' => $variables['viewedByLogin'] ?? $this->getUserLogin($variables['viewedByID'] ?? 0),
      '{CHANGED_VALUES}' => $this->formatSettingsChanges(
        is_array($variables['changedValues'] ?? null) ? $variables['changedValues'] : [],
        is_array($variables['sensitiveChanged'] ?? null) ? $variables['sensitiveChanged'] : []
      ),
      // ============================================================
      // СОГЛАСИЯ (152-ФЗ)
      // ============================================================
      '{DOCUMENT_TITLE}' => $variables['documentTitle']
        ?? $this->getPageStaticTitle($variables['pageStaticID'] ?? 0)
        ?: ($variables['documentKey'] ?? ''),
      '{DOCUMENT_KEY}' => $variables['documentKey'] ?? '',
      '{DOCUMENT_VERSION}' => $variables['documentVersion'] ?? '',
      '{LOCALE}' => $variables['locale'] ?? '',
      '{FORM_ID}' => $variables['formID'] ?? 0,
      '{FORM_REPORT_ID}' => $variables['formReportID'] ?? 0,
      '{PAGE_STATIC_ID}' => $variables['pageStaticID'] ?? 0,
      '{COUNT}' => $variables['count'] ?? 0,
    ];

    // Специальная обработка для просмотра ПДн
    if ($typeID === CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED) {
      if (isset($variables['action']) && $variables['action'] === 'list_view') {
        $template = $this->localeData['REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED_LIST'] ?? $template;
      }
    }

    return str_replace(
      array_keys($replacements),
      array_values($replacements),
      $template
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
   * Получить название группы пользователей по ID
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
   * Форматировать изменения настроек CMS для отображения в ленте
   *
   * @param array $changedValues Изменения безопасных полей: ['setting_x' => ['old' => ..., 'new' => ...]]
   * @param array $sensitiveChanged Имена чувствительных полей: ['setting_y', ...]
   * @return string
   */
  private function formatSettingsChanges(array $changedValues, array $sensitiveChanged): string
  {
    $parts = [];

    // Безопасные поля: показываем «было → стало»
    if (!empty($changedValues)) {
      foreach ($changedValues as $settingKey => $values) {
        $label = $this->localeData['SETTING_NAME_' . strtoupper($settingKey)] ?? $settingKey;
        $old = $this->truncateValue($values['old'] ?? '');
        $new = $this->truncateValue($values['new'] ?? '');
        $parts[] = sprintf('%s («%s» → «%s»)', $label, $old, $new);
      }
    }

    // Чувствительные поля: только имена
    if (!empty($sensitiveChanged)) {
      $sensitiveParts = [];
      foreach ($sensitiveChanged as $settingKey) {
        $label = $this->localeData['SETTING_NAME_' . strtoupper($settingKey)] ?? $settingKey;
        $sensitiveParts[] = $label;
      }
      $sensitiveLabel = $this->localeData['PAGE_REPORTS_SETTINGS_SENSITIVE_CHANGED'] ?? 'Чувствительные';
      $parts[] = sprintf('%s: %s', $sensitiveLabel, implode(', ', $sensitiveParts));
    }

    if (empty($parts)) return '';

    return '<br>' . implode('<br>', $parts);
  }

  /**
   * Обрезать значение для отображения в логе
   *
   * @param string $value
   * @param int $length
   * @return string
   */
  private function truncateValue(string $value, int $length = 80): string
  {
    $value = trim($value);
    if (mb_strlen($value) <= $length) {
      return $value;
    }
    return mb_substr($value, 0, $length) . '…';
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

    // ============================================================
    // ТИПЫ ОТЧЕТОВ ПО БЕЗОПАСНОСТИ
    // ============================================================
    
    $securityTypeIDs = CMSReport::getTypeIDsByCategory(CMSReport::CATEGORY_SECURITY);
    $reports = $this->getReportsByTypes($securityTypeIDs);

    // ============================================================
    // СТАТИСТИКА БЕЗОПАСНОСТИ
    // ============================================================
    
    $stats = [
      // Авторизация
      'auth_success_admin'  => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS])),
      'auth_fail_admin'     => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL])),
      'auth_success_site'   => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS])),
      'auth_fail_site'      => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL])),
      // Блокировки
      'banned'              => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED])),
      'unbanned'            => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED])),
      // ПДн и логи
      'personal_data_views' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED])),
      'logs_views'          => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_VIEWING_LOGS])),
      // НОВОЕ: пользователи
      'users_created'       => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USER_CREATED])),
      'users_edited'        => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USER_EDITED])),
      'users_deleted'       => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USER_DELETED])),
      // НОВОЕ: группы
      'groups_created'      => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USERS_GROUP_CREATED])),
      'groups_edited'       => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USERS_GROUP_EDITED])),
      'groups_deleted'      => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_USERS_GROUP_DELETED])),
    ];

    // Уникальные IP-адреса
    $uniqueIpsSuccess = [];
    $uniqueIpsFail = [];

    foreach ($reports as $report) {
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();
      
      $ip = $variables['ip'] ?? $variables['clientIP'] ?? null;
      $typeID = $report->getTypeID();

      if ($ip) {
        if (in_array($typeID, [
          CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS,
          CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS,
        ])) {
          $uniqueIpsSuccess[] = $ip;
        } elseif (in_array($typeID, [
          CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
          CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL,
        ])) {
          $uniqueIpsFail[] = $ip;
        }
      }
    }

    $uniqueIpsSuccess = array_unique($uniqueIpsSuccess);
    $uniqueIpsFail = array_unique($uniqueIpsFail);

    // ============================================================
    // ПОСЛЕДНИЕ СОБЫТИЯ БЕЗОПАСНОСТИ
    // ============================================================

    usort($reports, function($a, $b) {
      return $b->getCreatedUnixTimestamp() <=> $a->getCreatedUnixTimestamp();
    });
    
    $recentItems = [];
    $recentReports = array_slice($reports, 0, 20);

    foreach ($recentReports as $report) {
      $typeLabel = $this->getReportTypeLabel($report->getTypeID());
      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();

      // Определяем статус события
      $statusClass = 'info';
      if (in_array($report->getTypeID(), [
        CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS,
        CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_SUCCESS,
        CMSReport::REPORT_TYPE_ID_BASE_USER_UNBANNED,
        CMSReport::REPORT_TYPE_ID_AP_USER_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_USERS_GROUP_CREATED,
      ])) {
        $statusClass = 'success';
      } elseif (in_array($report->getTypeID(), [
        CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL,
        CMSReport::REPORT_TYPE_ID_BASE_AUTHORIZATION_FAIL,
        CMSReport::REPORT_TYPE_ID_BASE_USER_BANNED,
        CMSReport::REPORT_TYPE_ID_AP_USER_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_USERS_GROUP_DELETED,
      ])) {
        $statusClass = 'danger';
      }

      $recentItems[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/reports/item.tpl',
        [
          'REPORT_TYPE' => $typeLabel,
          'REPORT_DESCRIPTION' => $description,
          'REPORT_DATE' => $createdDate,
          'REPORT_IP' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
          'REPORT_STATUS_CLASS' => $statusClass
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
        // Статистика безопасности
        'TOTAL_SUCCESS_AUTH_ADMIN' => $stats['auth_success_admin'],
        'TOTAL_FAIL_AUTH_ADMIN' => $stats['auth_fail_admin'],
        'TOTAL_SUCCESS_AUTH_SITE' => $stats['auth_success_site'],
        'TOTAL_FAIL_AUTH_SITE' => $stats['auth_fail_site'],
        'TOTAL_BANNED' => $stats['banned'],
        'TOTAL_UNBANNED' => $stats['unbanned'],
        'TOTAL_PERSONAL_DATA_VIEWS' => $stats['personal_data_views'],
        'TOTAL_LOGS_VIEWS' => $stats['logs_views'],
        'TOTAL_USERS_CREATED'  => $stats['users_created'],
        'TOTAL_USERS_EDITED'   => $stats['users_edited'],
        'TOTAL_USERS_DELETED'  => $stats['users_deleted'],
        'TOTAL_GROUPS_CREATED' => $stats['groups_created'],
        'TOTAL_GROUPS_EDITED'  => $stats['groups_edited'],
        'TOTAL_GROUPS_DELETED' => $stats['groups_deleted'],
        'UNIQUE_IPS_SUCCESS' => !empty($uniqueIpsSuccess) ? implode(', ', $uniqueIpsSuccess) : '-',
        'UNIQUE_IPS_FAIL' => !empty($uniqueIpsFail) ? implode(', ', $uniqueIpsFail) : '-',
        'RECENT_EVENTS' => implode("\n", $recentItems)
      ]
    );
  }
}
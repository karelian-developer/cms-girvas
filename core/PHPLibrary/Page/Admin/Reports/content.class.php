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
use \core\PHPLibrary\PageStatic as Page;

/**
 * Class ReportsContent
 * 
 * Отчеты по контенту за последние 7 дней
 */
class ReportsContent implements ReportsPageInterface
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
      '{FEED_TITLE}' => $getLocalizedTitle('feedTitles', 'feedTitle', 'feedID', fn($id) => $this->getFeedTitle($id)),
      '{SAMPLE_TITLE}' => $getLocalizedTitle('sampleTitles', 'sampleTitle', 'sampleID', fn($id) => $this->getSampleTitle($id)),
      '{FILE_NAME}' => $variables['fileName'] ?? $variables['name'] ?? '',
      '{CLIENT_IP}' => $variables['ip'] ?? $variables['clientIP'] ?? '0.0.0.0',
      '{USER_LOGIN}' => $variables['userLogin'] ?? $this->getUserLogin($variables['userID'] ?? 0),
      '{CREATOR_LOGIN}' => $variables['createdByLogin'] ?? $this->getUserLogin($variables['createdByID'] ?? 0),
      '{UPDATER_LOGIN}' => $variables['updatedByLogin'] ?? $this->getUserLogin($variables['updatedByID'] ?? 0),
      '{DELETER_LOGIN}' => $variables['deletedByLogin'] ?? $this->getUserLogin($variables['deletedByID'] ?? 0),
      '{TARGET_USER_LOGIN}' => $variables['targetUserLogin'] ?? $this->getUserLogin($variables['targetUserID'] ?? 0),
      '{VIEWER_LOGIN}' => $variables['viewedByLogin'] ?? $this->getUserLogin($variables['viewedByID'] ?? 0),
      '{SUBJECT_LOGIN}' => $variables['subjectUserLogin'] ?? $this->getUserLogin($variables['subjectUserID'] ?? 0),
      '{EXPORTED_BY_LOGIN}' => $variables['exportedByLogin'] ?? $this->getUserLogin($variables['exportedByID'] ?? 0),
      '{TARGET_USER_LOGIN}' => $variables['targetUserLoginBefore'] ?? $this->getUserLogin($variables['targetUserID'] ?? 0),
      '{ANONYMIZED_BY_LOGIN}' => $variables['anonymizedByLogin'] ?? $this->getUserLogin($variables['anonymizedByID'] ?? 0),
      '{REASON}' => $variables['reason'] ?? '',
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
    // ТИПЫ ОТЧЕТОВ ПО КОНТЕНТУ
    // ============================================================
    
    $contentTypeIDs = CMSReport::getTypeIDsByCategory(CMSReport::CATEGORY_CONTENT);
    $reports = $this->getReportsByTypes($contentTypeIDs);

    // ============================================================
    // СТАТИСТИКА ПО ТИПАМ КОНТЕНТА
    // ============================================================
    
    $stats = [
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
      'feeds_created' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FEED_CREATED])),
      'feeds_edited'  => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FEED_EDITED])),
      'feeds_deleted' => count($this->filterReports($reports, [CMSReport::REPORT_TYPE_ID_AP_FEED_DELETED])),
    ];

    // ============================================================
    // ПОСЛЕДНИЕ СОБЫТИЯ ПО КОНТЕНТУ
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
        CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_FORM_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED,
        CMSReport::REPORT_TYPE_ID_AP_FEED_CREATED,
      ])) {
        $statusClass = 'success';
      } elseif (in_array($report->getTypeID(), [
        CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED,
        CMSReport::REPORT_TYPE_ID_AP_FEED_DELETED,
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
        // Статистика по контенту
        'CONTENT_ENTRIES_CREATED' => $stats['entries_created'],
        'CONTENT_ENTRIES_EDITED' => $stats['entries_edited'],
        'CONTENT_ENTRIES_DELETED' => $stats['entries_deleted'],
        'CONTENT_PAGES_CREATED' => $stats['pages_created'],
        'CONTENT_PAGES_EDITED' => $stats['pages_edited'],
        'CONTENT_PAGES_DELETED' => $stats['pages_deleted'],
        'CONTENT_MEDIA_UPLOADED' => $stats['media_uploaded'],
        'CONTENT_MEDIA_DELETED' => $stats['media_deleted'],
        'CONTENT_CATEGORIES_CREATED' => $stats['categories_created'],
        'CONTENT_CATEGORIES_EDITED' => $stats['categories_edited'],
        'CONTENT_CATEGORIES_DELETED' => $stats['categories_deleted'],
        'CONTENT_SAMPLES_CREATED' => $stats['samples_created'],
        'CONTENT_SAMPLES_EDITED' => $stats['samples_edited'],
        'CONTENT_SAMPLES_DELETED' => $stats['samples_deleted'],
        'CONTENT_FORMS_CREATED' => $stats['forms_created'],
        'CONTENT_FORMS_EDITED' => $stats['forms_edited'],
        'CONTENT_FORMS_DELETED' => $stats['forms_deleted'],
        'CONTENT_COMMENTS_CREATED' => $stats['comments_created'],
        'CONTENT_COMMENTS_EDITED' => $stats['comments_edited'],
        'CONTENT_COMMENTS_DELETED' => $stats['comments_deleted'],
        'CONTENT_BLOCKS_CREATED' => $stats['blocks_created'],
        'CONTENT_BLOCKS_EDITED' => $stats['blocks_edited'],
        'CONTENT_BLOCKS_DELETED' => $stats['blocks_deleted'],
        'CONTENT_FEEDS_CREATED' => $stats['feeds_created'],
        'CONTENT_FEEDS_EDITED'  => $stats['feeds_edited'],
        'CONTENT_FEEDS_DELETED' => $stats['feeds_deleted'],
        'TOTAL_CONTENT_ACTIONS' => count($reports),
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
}
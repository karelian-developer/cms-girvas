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
 * Class ReportsContent
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

    $localeData = $this->CMSCore->locale->getData();
    $typeName = $this->getReportTypeName($typeID);
    $template = $localeData[$typeName] ?? '';

    if (empty($template)) {
      return $typeName . ' (ID: ' . ($variables['id'] ?? '?') . ')';
    }

    $replacements = [
      '{ENTRY_TITLE}' => $this->getEntryTitle($variables['entryID'] ?? $variables['id'] ?? 0),
      '{PAGE_TITLE}' => $this->getPageTitle($variables['pageID'] ?? $variables['id'] ?? 0),
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

    // Типы отчетов по контенту
    $contentTypeIDs = [
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED,
      CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED,
      CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED,
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
    ];

    $reports = $this->getReportsByTypes($contentTypeIDs);

    // Статистика по типам контента
    $stats = [
      'entries_created' => 0,
      'entries_edited' => 0,
      'entries_deleted' => 0,
      'pages_created' => 0,
      'pages_edited' => 0,
      'pages_deleted' => 0,
      'media_uploaded' => 0,
      'media_deleted' => 0,
      'categories_created' => 0,
      'categories_edited' => 0,
      'categories_deleted' => 0,
      'samples_created' => 0,
      'samples_edited' => 0,
      'samples_deleted' => 0,
      'forms_created' => 0,
      'forms_edited' => 0,
      'forms_deleted' => 0,
      'comments_created' => 0,
      'comments_edited' => 0,
      'comments_deleted' => 0,
      'blocks_created' => 0,
      'blocks_edited' => 0,
      'blocks_deleted' => 0,
    ];

    $items = [];
    foreach ($reports as $report) {
      $variables = $this->viewer !== null
        ? $report->getVariables($this->viewer)
        : $report->getVariables();

      $typeID = $report->getTypeID();

      // Сбор статистики
      switch ($typeID) {
        case CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED: $stats['entries_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRY_EDITED: $stats['entries_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRY_DELETED: $stats['entries_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED: $stats['pages_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_PAGE_EDITED: $stats['pages_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_PAGE_DELETED: $stats['pages_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED: $stats['media_uploaded']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_MEDIA_DELETED: $stats['media_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_CREATED: $stats['categories_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_EDITED: $stats['categories_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_CATEGORY_DELETED: $stats['categories_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_CREATED: $stats['samples_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_EDITED: $stats['samples_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_SAMPLE_DELETED: $stats['samples_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_FORM_CREATED: $stats['forms_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_FORM_EDITED: $stats['forms_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_FORM_DELETED: $stats['forms_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_CREATED: $stats['comments_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_EDITED: $stats['comments_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_ENTRIES_COMMENT_DELETED: $stats['comments_deleted']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_CREATED: $stats['blocks_created']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_EDITED: $stats['blocks_edited']++; break;
        case CMSReport::REPORT_TYPE_ID_AP_CONTENT_BLOCK_DELETED: $stats['blocks_deleted']++; break;
      }

      $description = $this->formatReportDescription($report);
      $createdDate = date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp());
      $typeName = $this->getReportTypeName($typeID);
      $typeLabel = $localeData[$typeName] ?? $typeName;

      $items[] = ThemeCollector::assemblyFileContent(
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
      array_merge($stats, [
        'REPORTS_ITEMS' => implode("\n", $items),
        'TOTAL_COUNT' => count($items)
      ])
    );
  }
}
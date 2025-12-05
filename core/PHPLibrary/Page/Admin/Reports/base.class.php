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

/**
 * Class ReportsBase
 */
class ReportsBase implements ReportsPageInterface
{
  const FORM_PATH = 'templates/page/reports';

  public string $title;
  public string $description;
  public string $assembled = '';

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
      ['id', 'metadata']
    );
  }

  private function filterReports(array &$reportsObjects, array $typeIDs) : void
  {
    foreach ($reportsObjects as $index => $report) {
      $typeID = $report->getTypeID();
      $typeID = is_numeric($report->getTypeID()) ? (int)$report->getTypeID() : 0;

      if (!in_array($typeID, $typeIDs)) {
        unset($reportsObjects[$index]);
      }
    }
  }

  /**
   * Собрать шаблон
   * 
   * @param array $templateValues
   * 
   * @return string
   */
  public function assembly(array $templateValues = []) : void
  {
    $templatePath = 'templates/page/reports/' . $this->name . '.tpl';
    $reports = $this->getAllReportsObjectsByPeriod();
    // $this->filterReports($reports, [
    //   CMSReport::REPORT_TYPE_ID_AP_ENTRY_CREATED,
    //   CMSReport::REPORT_TYPE_ID_AP_PAGE_CREATED,
    //   CMSReport::REPORT_TYPE_ID_AP_MEDIA_UPLOADED,
    //   CMSReport::REPORT_TYPE_ID_USER_CREATED
    // ]);

    $totalActions = count($reports);
    $totalEntriesCreated = count($reports);
    
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      $templatePath,
      [
        'REPORT_NAME' => $this->name,
        'TOTAL_ACTIONS' => $totalActions
      ]
    );
  }
}
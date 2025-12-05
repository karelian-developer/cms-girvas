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
   * Собрать шаблон
   * 
   * @param array $templateValues
   * 
   * @return string
   */
  public function assembly(array $templateValues = []) : void
  {
    $templatePath = 'templates/page/reports/' . $this->name . '.tpl';
    
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      $templatePath,
      [
        'SETTINGS_NAME' => $this->name
      ]
    );
  }
}
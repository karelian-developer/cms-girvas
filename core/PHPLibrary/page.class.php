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

namespace core\PHPLibrary;

use \core\PHPLibrary\Template\Collector as ThemeCollector;

class Page implements InterfacePage
{
  public PageBreadcrumbs $breadcrumbs;
  private string $name;
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {
    $this->setBreadcrumps($this->CMSCore);
  }

  /**
   * Установить "хлебные" крошки
   * 
   * @return void
   */
  private function setBreadcrumps(CoreInterface $CMSCore) : void
  {
    $this->breadcrumbs = new PageBreadcrumbs($CMSCore);
  }

  /**
   * Назначить техническое имя страницы
   *
   * @param  string $value
   * 
   * @return void
   */
  private function setName(string $value) : void
  {
    $this->name = $value;
  }

  /**
   * Получить техническое имя страницы
   *
   * @param  string $value
   * 
   * @return void
   */
  public function getName() : string
  {
    return $this->name;
  }

  /**
   * Назначить объект шаблона
   *
   * @param  Template $theme
   * 
   * @return void
   */
  private function setTemplate(Template $theme) : void
  {
    $this->theme = $theme;
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    
  }
}
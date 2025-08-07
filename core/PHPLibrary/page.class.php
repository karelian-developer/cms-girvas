<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Template\Collector as ThemeCollector;

class Page
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
  private function assembly() : string
  {
    /** @var string $themePath Путь до шаблона */
    $themePath = $this->theme->getPath();
    $fileTemplatePath = $themePath . '/page.tpl';

    if (file_exists($fileTemplatePath)) {
      $pageThemePath = $themePath . '/page/' . $this->getName() . '.tpl';

      if (file_exists($pageThemePath)) {
        $pageTheme = file_get_contents($pageThemePath);
        return ThemeCollector::assembly($pageTheme, [
          'PAGE_NAME' => $this->getName(),
        ]);
      }
    }
  }
}
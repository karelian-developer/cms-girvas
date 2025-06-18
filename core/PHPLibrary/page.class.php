<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class Page {
    private SystemCore $CMSCore;
    public PageBreadcrumbs $breadcrumbs;
    private string $name;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @param  string $name
     * @return void
     */
    public function __construct(SystemCore $CMSCore, array $directoryExploded) {
      $this->set_system_core($CMSCore);
      $this->breadcrumbs = new PageBreadcrumbs($CMSCore);
    }

    /**
     * Назначить техническое имя страницы
     *
     * @param  string $value
     * @return void
     */
    private function set_name(string $value) : void {
      $this->name = $value;
    }

    /**
     * Получить техническое имя страницы
     *
     * @param  string $value
     * @return void
     */
    public function get_name() : string {
      return $this->name;
    }

    /**
     * Назначить объект шаблона
     *
     * @param  Template $theme
     * @return void
     */
    private function set_template(Template $theme) : void {
      $this->theme = $theme;
    }

    /**
     * Назначить объект системного ядра
     *
     * @param  Template $theme
     * @return void
     */
    private function set_system_core(SystemCore $CMSCore) : void {
      $this->CMSCore = $CMSCore;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    private function assembly() : string {
      /** @var string $themePath Путь до шаблона */
      $themePath = $this->theme->get_path();

      if (file_exists(sprintf('%s/page.tpl', $themePath))) {
        $pageThemePath = sprintf('%s/page/%s.tpl', $themePath, $this->get_name());
        if (file_exists($pageThemePath)) {
          $pageTheme = file_get_contents($pageThemePath);
          return TemplateCollector::assembly($pageTheme, [
            'PAGE_NAME' => $this->get_name(),
          ]);
        }
      }
    }
  }
}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class Page {
    private SystemCore $system_core;
    public PageBreadcrumbs $breadcrumbs;
    private string $name;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return void
     */
    public function __construct(SystemCore $system_core, array $dir_exploded) {
      $this->set_system_core($system_core);
      $this->breadcrumbs = new PageBreadcrumbs($system_core);
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
     * @param  Template $template
     * @return void
     */
    private function set_template(Template $template) : void {
      $this->template = $template;
    }

    /**
     * Назначить объект системного ядра
     *
     * @param  Template $template
     * @return void
     */
    private function set_system_core(SystemCore $system_core) : void {
      $this->system_core = $system_core;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    private function assembly() : string {
      /** @var string $template_path Путь до шаблона */
      $template_path = $this->template->get_path();

      if (file_exists(sprintf('%s/page.tpl', $template_path))) {
        $page_template_path = sprintf('%s/page/%s.tpl', $template_path, $this->get_name());
        if (file_exists($page_template_path)) {
          $page_template = file_get_contents($page_template_path);
          return TemplateCollector::assembly($page_template, [
            'PAGE_NAME' => $this->get_name(),
          ]);
        }
      }
    }
  }
}

?>
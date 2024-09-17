<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageError implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public int $error_code;
    private string $error_title;
    private string $error_desription;
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  Page $page
     * @param  int $error_code
     * @return void
     */
    public function __construct(SystemCore $system_core, Page $page, int $error_code) {
      $this->system_core = $system_core;
      $this->page = $page;
      $this->error_code = $error_code;

      $locale_data = $this->system_core->locale->get_data();

      switch ($error_code) {
        case 404:
          $this->error_title = $locale_data['PAGE_ERROR_404_TITLE'];
          $this->error_desription = sprintf($locale_data['PAGE_ERROR_404_DESCRIPTION'], urldecode($_SERVER['REQUEST_URI']));
          break;
        case 500:
          $this->error_title = $locale_data['PAGE_ERROR_500_TITLE'];
          $this->error_desription = $locale_data['PAGE_ERROR_500_DESCRIPTION'];
          break;
        case 503:
          $this->error_title = $locale_data['PAGE_ERROR_503_TITLE'];
          $this->error_desription = $locale_data['PAGE_ERROR_503_DESCRIPTION'];
          break;
        default:
          $this->error_title = $locale_data['PAGE_ERROR_UNKNOWN_TITLE'];
          $this->error_desription = $locale_data['PAGE_ERROR_UNKNOWN_DESCRIPTION'];
      }

    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      http_response_code($this->error_code);

      $this->system_core->template->add_style(['href' => 'styles/page/error.css', 'rel' => 'stylesheet']);

      $this->system_core->configurator->set_meta_title($this->error_title);

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/error.tpl', [
        'ERROR_TITLE' => $this->error_title,
        'ERROR_DESCRIPTION' => $this->error_desription
      ]);
    }

  }

}

?>
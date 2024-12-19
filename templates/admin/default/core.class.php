<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\admin\default {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Entries\Database as EntriesDatabase;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Users as Users;
  use \core\PHPLibrary\Client\Session as ClientSession;

  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $template;
    public string $assembled;
    
    /**
     * __construct
     *
     * @param  mixed $template
     * @return void
     */
    public function __construct(\core\PHPLibrary\Template $template) {
      $this->template = $template;
    }
    
    /**
     * Сборка шапки сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_header(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/header.tpl', $template_replaces);
    }
    
    /**
     * Сборка главной секции сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_main(array $template_replaces = []) : string {
      $this->template->system_core->init_page(ltrim($_SERVER['REQUEST_URI'], '/'));
      $site_page = $this->template->system_core->get_inited_page();
      $site_page->assembly();
      
      $template_replaces['ADMIN_PANEL_PAGE_WRAPPER'] = TemplateCollector::assembly_file_content($this->template, 'templates/page.tpl', [
        'ADMIN_PANEL_PAGE' => $site_page->assembled,
      ]);

      return TemplateCollector::assembly_file_content($this->template, 'templates/main.tpl', $template_replaces);
    }
    
    /**
     * Сборка подвала сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_footer(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/footer.tpl', $template_replaces);
    }
    
    /**
     * Сборка основной части документа
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_document(array $template_replaces = []) : string {
      /** @var string $assembled Содержимое шаблона */
      $assembled;

      if ($this->template->system_core->client->is_logged(2)) {
        $template_content = TemplateCollector::assembly_file_content($this->template, 'templates/documentBase.tpl', $template_replaces);
      } else {
        $template_content = TemplateCollector::assembly_file_content($this->template, 'templates/documentAuth.tpl', $template_replaces);
      }

      return $template_content;
    }

    public function assembly_auth_admin_page(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/page/auth.tpl', $template_replaces);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->template->add_style(['href' => 'styles/normalize.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/fonts.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/table.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/form.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/modal.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/interactive.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/notification.css', 'rel' => 'stylesheet']);
      
      $this->template->add_script(['src' => 'interactive.class.js', 'type' => 'module'], true);
      $this->template->add_script(['src' => 'common.js'], true);
      $this->template->add_script(['src' => 'core.class.js', 'type' => 'module'], true);
      $this->template->add_script(['src' => 'core.class.js', 'type' => 'module']);


      /** @var string $user_ip IP-адрес пользователя */
      $user_ip = $_SERVER['REMOTE_ADDR'];

      if ($this->template->system_core->client->is_logged(2)) {
        $this->template->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);

        /** @var string $this->assembled Итоговый шаблон в виде строки */
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => $this->assembly_header(),
          'ADMIN_PANEL_MAIN' => $this->assembly_main(),
          'ADMIN_PANEL_FOOTER' => $this->assembly_footer()
        ]);
      } else {
        $this->template->add_style(['href' => 'styles/page/auth.css', 'rel' => 'stylesheet']);

        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => '',
          'ADMIN_PANEL_MAIN' => $this->assembly_auth_admin_page(),
          'ADMIN_PANEL_FOOTER' => ''
        ]);
      }
    }

  }

}

?>
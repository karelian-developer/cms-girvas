<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\default {
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $template;
    private SystemCoreLocale $locale;
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

    public function assembly_page_index(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/page/index.tpl', [
        'PAGE_NAME' => 'index',
        'ENTRIES_LIST' => ''
      ]);
    }
    
    /**
     * Сборка заглушки сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_plug(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/plug.tpl', $template_replaces);
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
      $this->template->system_core->init_page($this->template->system_core->urlp->get_path_string());
      $site_page = $this->template->system_core->get_inited_page();
      $site_page->assembly();
      
      $template_replaces['SITE_PAGE'] = TemplateCollector::assembly($site_page->assembled, []);

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

      return TemplateCollector::assembly_file_content($this->template, 'templates/html.tpl', $template_replaces);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->template->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);

      $locale_data = $this->template->locale->get_data();

      $client_is_logged = $this->template->system_core->client->is_logged(1);
      $client_user = ($client_is_logged) ? $this->template->system_core->client->get_user(1) : null;
      
      if ($client_user != null) {
        $client_user->init_data(['metadata']);
      }

      $client_user_group_id = ($client_user != null) ? $client_user->get_group_id() : 0;

      if ($this->template->system_core->configurator->get_database_entry_value('base_engineering_works_status') == 'off' || $client_user_group_id == 1) {
        $this->template->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
        
        $this->template->add_script(['src' => 'common.js'], true);
        $this->template->add_script(['src' => 'core.class.js', 'type' => 'module'], true);
        $this->template->add_script(['src' => 'core.class.js', 'type' => 'module']);

        $profile_link = ($this->template->system_core->client->is_logged(1)) ? sprintf('<a class="header__nav-link display-block" href="/profile"><span class="header__nav-span">%s</span></a>', $locale_data['DEFAULT_TEXT_PROFILE']) : sprintf('<a id="SYSTEM_GE_IMC_00000001" class="header__nav-link display-block" href="#"><span class="header__nav-span">%s</span></a>', $locale_data['DEFAULT_TEXT_LOGIN']);
        $registration_link = (!$this->template->system_core->client->is_logged(1)) ? sprintf('<a class="header__nav-link display-block" href="/registration"><span class="header__nav-span">%s</span></a>', $locale_data['DEFAULT_TEXT_REGISTRATION']) : '';
        $exit_link = ($this->template->system_core->client->is_logged(1)) ? sprintf('<a class="header__nav-link display-block" href="#" role="profileNavigationExit"><span class="header__nav-span">%s</span></a>', $locale_data['DEFAULT_TEXT_EXIT']) : '';

        /** @var string $this->assembled Итоговый шаблон в виде строки */
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'SITE_HEADER' => $this->assembly_header([
            'NAVIGATION_PROFILE_LINK' => $profile_link,
            'NAVIGATION_REGISTRATION_LINK' => $registration_link,
            'NAVIGATION_EXIT_LINK' => $exit_link
          ]),
          'SITE_MAIN' => $this->assembly_main(),
          'SITE_FOOTER' => $this->assembly_footer()
        ]);
      } else {
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'SITE_HEADER' => '',
          'SITE_MAIN' => TemplateCollector::assembly($this->assembly_plug(), [
            'SITE_CLOSED_REASON' => $this->template->system_core->configurator->get_database_entry_value('base_engineering_works_text')
          ]),
          'SITE_FOOTER' => ''
        ]);
      }
    }

  }

}

?>
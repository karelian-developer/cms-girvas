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
  use \DOMDocument as DOMDocument;

  #[\AllowDynamicProperties]
  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $theme;
    private SystemCoreLocale $locale;
    public string $assembled = '';
    public DOMDocument|null $source = null;
    
    /**
     * __construct
     *
     * @param  mixed $theme
     * @return void
     */
    public function __construct(\core\PHPLibrary\Template $theme) {
      $this->theme = $theme;
    }

    public function assembly_page_index(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/page/index.tpl', [
        'PAGE_NAME' => 'index',
        'ENTRIES_LIST' => ''
      ]);
    }
    
    /**
     * Сборка заглушки сайта
     *
     * @param  mixed $themeVars Массив с переменами темы и их значениями
     * @return string
     */
    public function assembly_plug(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/plug.tpl', $themeVars);
    }
    
    /**
     * Сборка шапки сайта
     *
     * @param  mixed $themeVars Массив с переменами темы и их значениями
     * @return string
     */
    public function assembly_header(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/header.tpl', $themeVars);
    }
    
    /**
     * Сборка главной секции сайта
     *
     * @param  mixed $themeVars Массив с переменами темы и их значениями
     * @return string
     */
    public function assembly_main(array $themeVars = []) : string {
      $this->theme->CMSCore->init_page($this->theme->CMSCore->urlp->get_path_string());
      $sitePage = $this->theme->CMSCore->get_inited_page();
      $sitePage->assembly();
      
      $themeVars['SITE_PAGE'] = TemplateCollector::assembly($sitePage->assembled, []);

      return TemplateCollector::assembly_file_content($this->theme, 'templates/main.tpl', $themeVars);
    }
    
    /**
     * Сборка подвала сайта
     *
     * @param  mixed $themeVars Массив с переменами темы и их значениями
     * @return string
     */
    public function assembly_footer(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/footer.tpl', $themeVars);
    }
    
    /**
     * Сборка основной части документа
     *
     * @param  mixed $themeVars Массив с переменами темы и их значениями
     * @return string
     */
    public function assembly_document(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/html.tpl', $themeVars);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->theme->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);

      $localeData = $this->theme->locale->get_data();

      $clientIsLogged = $this->theme->CMSCore->client->is_logged(1);
      $user = $clientIsLogged ? $this->theme->CMSCore->client->get_user(1) : null;
      
      if ($user !== null) {
        $user->init_data(['metadata']);
      }

      $userGroupID = $user !== null ? $user->get_group_id() : 0;

      if ($this->theme->CMSCore->configurator->get_database_entry_value('base_engineering_works_status') == 'off' || $userGroupID == 1) {
        $this->theme->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
        
        $this->theme->add_script(['src' => 'common.js'], true);
        $this->theme->add_script(['src' => 'core.class.js', 'type' => 'module'], true);
        $this->theme->add_script(['src' => 'core.class.js', 'type' => 'module']);

        $profileLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="/profile"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_PROFILE']) : sprintf('<a id="SYSTEM_GE_IMC_00000001" class="header__nav-link display-block" href="#"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_LOGIN']);
        $registrationLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="/registration"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_REGISTRATION']) : '';
        $exitLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="#" role="profileNavigationExit"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_EXIT']) : '';

        /** @var string $this->assembled Итоговый шаблон в виде строки */
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'SITE_HEADER' => $this->assembly_header([
            'NAVIGATION_PROFILE_LINK' => $profileLink,
            'NAVIGATION_REGISTRATION_LINK' => $registrationLink,
            'NAVIGATION_EXIT_LINK' => $exitLink
          ]),
          'SITE_MAIN' => $this->assembly_main(),
          'SITE_FOOTER' => $this->assembly_footer()
        ]);
      } else {
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'SITE_HEADER' => '',
          'SITE_MAIN' => TemplateCollector::assembly($this->assembly_plug(), [
            'SITE_CLOSED_REASON' => $this->theme->CMSCore->configurator->get_database_entry_value('base_engineering_works_text')
          ]),
          'SITE_FOOTER' => ''
        ]);
      }
    }
  }
}

?>
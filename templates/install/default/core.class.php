<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\install\default {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \DOMDocument as DOMDocument;

  #[\AllowDynamicProperties]
  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $theme;
    public string $assembled;
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
    
    /**
     * Сборка шапки сайта
     *
     * @param  mixed $themeVars Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_header(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/header.tpl', $themeVars);
    }
    
    /**
     * Сборка главной секции сайта
     *
     * @param  mixed $themeVars Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_main(array $themeVars = []) : string {
      $domainConfiguration = $this->theme->CMSCore->configurator->get('domain');
      
      $domainAliasesConfiguration = $this->theme->CMSCore->configurator->get('domain_aliases');
      $domainAliasesConfiguration = (is_array($domainAliasesConfiguration)) ? implode(', ', $domainAliasesConfiguration) : '';

      $databaseConfigurations = $this->theme->CMSCore->configurator->get('database');
      $databaseConfigurations = (is_null($databaseConfigurations)) ? [] : $databaseConfigurations;

      $themeVars['CONFIGURATION_DOMAIN'] = ($domainConfiguration != null) ? $domainConfiguration : '';
      $themeVars['CONFIGURATION_DOMAIN_ALIASES'] = ($domainAliasesConfiguration != null) ? $domainAliasesConfiguration : '';
      $themeVars['CONFIGURATION_DATABASE_SCHEME'] = (array_key_exists('scheme', $databaseConfigurations)) ? $databaseConfigurations['scheme'] : '';
      $themeVars['CONFIGURATION_DATABASE_PREFIX'] = (array_key_exists('prefix', $databaseConfigurations)) ? $databaseConfigurations['prefix'] : '';
      $themeVars['CONFIGURATION_DATABASE_HOST'] = (array_key_exists('host', $databaseConfigurations)) ? $databaseConfigurations['host'] : '';
      $themeVars['CONFIGURATION_DATABASE_PASSWORD'] = (array_key_exists('password', $databaseConfigurations)) ? $databaseConfigurations['password'] : '';
      $themeVars['CONFIGURATION_DATABASE_NAME'] = (array_key_exists('name', $databaseConfigurations)) ? $databaseConfigurations['name'] : '';
      $themeVars['CONFIGURATION_DATABASE_USER'] = (array_key_exists('user', $databaseConfigurations)) ? $databaseConfigurations['user'] : '';

      $themeVars['SITE_TITLE_VALUE'] = ($this->theme->CMSCore->configurator->exists_database_entry_value('base_title')) ? $this->theme->CMSCore->configurator->get_database_entry_value('base_title') : '';
      $themeVars['SITE_DESCRIPTION_VALUE'] = ($this->theme->CMSCore->configurator->exists_database_entry_value('seo_site_description')) ? $this->theme->CMSCore->configurator->get_database_entry_value('seo_site_description') : '';
      $themeVars['SITE_KEYWORDS_VALUE'] = ($this->theme->CMSCore->configurator->exists_database_entry_value('seo_site_keywords')) ? implode(', ', json_decode($this->theme->CMSCore->configurator->get_database_entry_value('seo_site_keywords'), true)) : '';

      return TemplateCollector::assembly_file_content($this->theme, 'templates/main.tpl', $themeVars);
    }
    
    /**
     * Сборка подвала сайта
     *
     * @param  mixed $themeVars Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_footer(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/footer.tpl', $themeVars);
    }
    
    /**
     * Сборка основной части документа
     *
     * @param  mixed $themeVars Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_document(array $themeVars = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/document.tpl', $themeVars);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->theme->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);
      
      $this->theme->add_script(['src' => 'interactive.class.js', 'type' => 'module'], true);

      $this->theme->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);

      $this->theme->add_script(['src' => 'core.class.js', 'type' => 'module']);

      /** @var string $this->assembled Итоговый шаблон в виде строки */
      $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
        'PAGE_HEADER' => $this->assembly_header(),
        'PAGE_MAIN' => $this->assembly_main(),
        'PAGE_FOOTER' => $this->assembly_footer()
      ]);
    }

  }

}

?>
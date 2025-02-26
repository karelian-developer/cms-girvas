<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  /**
   * Class SettingsBase
   */
  class SettingsBase {
    const FORM_PATH = 'templates/page/settings';

    public SystemCore $system_core;
    public string $title;
    public string $name;
    public string $description;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param mixed $system_core
     * @param string $name
     * 
     * @return void
     */
    public function __construct(SystemCore $system_core, string $name) {
      $this->system_core = $system_core;
      $this->name = $name;
    }

    /**
     * Установить заголовок
     * 
     * @param string $value
     * 
     * @return void
     */
    public function set_title(string $value) : void {
      $this->title = $value;
    }

    /**
     * Установить описание
     * 
     * @param string $value
     * 
     * @return void
     */
    public function set_description(string $value) : void {
      $this->description = $value;
    }
    
    /**
     * Получить заголовок
     * 
     * @return string
     */
    public function get_title() : string {
      return $this->title;
    }

    /**
     * Получить описание
     * 
     * @return string
     */
    public function get_description() : string {
      return $this->description;
    }

    /**
     * Собрать шаблон
     * 
     * @param array $template_values
     * 
     * @return string
     */
    public function assembly(array $template_values = []) {
      $form_template_path = sprintf('%s/%s.tpl', self::FORM_PATH, $this->name);
      
      /** @var string */
      $setting_engineering_works_status_value = $this->system_core->configurator->get_engineering_works_status();
      /** @var string */
      $setting_engineering_works_text_value = $this->system_core->configurator->get_engineering_works_text();
      /** @var string */
      $setting_engineering_works_text_value = ($setting_engineering_works_text_value == '') ? $setting_engineering_works_text_value : 'The site is undergoing technical work :C';

      /** @var string */
      $setting_section_entries_status_value = $this->system_core->configurator->get_section_entries_status();
      /** @var string */
      $setting_section_static_pages_value = $this->system_core->configurator->get_section_static_pages_status();
      /** @var string */
      $setting_section_modules_status_value = $this->system_core->configurator->get_section_modules_status();
      /** @var string */
      $setting_section_templates_status_value = $this->system_core->configurator->get_section_templates_status();
      /** @var string */
      $setting_section_users_status_value = $this->system_core->configurator->get_section_users_status();
      /** @var string */
      $setting_section_media_status_value = $this->system_core->configurator->get_section_media_status();
      /** @var string */
      $setting_section_feeds_status_value = $this->system_core->configurator->get_section_feeds_status();
      /** @var string */
      $setting_section_analytics_status_value = $this->system_core->configurator->get_section_analytics_status();

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, $form_template_path, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_SITE_TITLE_VALUE' => ($this->system_core->configurator->exists_database_entry_value('base_site_title')) ? $this->system_core->configurator->get_database_entry_value('base_site_title') : '',
        'SETTING_ENGINEERING_WORKS_TEXT_VALUE' => $setting_engineering_works_text_value,
        'SETTING_ENGINEERING_WORKS_STATUS_VALUE' => $setting_engineering_works_status_value,
        'SETTING_SECTION_ENTRIES_STATUS_VALUE' => $setting_section_entries_status_value,
        'SETTING_SECTION_STATIC_PAGES_STATUS_VALUE' => $setting_section_static_pages_value,
        'SETTING_SECTION_MODULES_STATUS_VALUE' => $setting_section_modules_status_value,
        'SETTING_SECTION_TEMPLATES_STATUS_VALUE' => $setting_section_templates_status_value,
        'SETTING_SECTION_USERS_STATUS_VALUE' => $setting_section_users_status_value,
        'SETTING_SECTION_MEDIA_STATUS_VALUE' => $setting_section_media_status_value,
        'SETTING_SECTION_FEEDS_STATUS_VALUE' => $setting_section_feeds_status_value,
        'SETTING_SECTION_ANALYTICS_STATUS_VALUE' => $setting_section_analytics_status_value,
        'SETTING_ENGINEERING_WORKS_CHECKED_VALUE' => ($setting_engineering_works_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_ENTRIES_CHECKED_VALUE' => ($setting_section_entries_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_STATIC_PAGES_CHECKED_VALUE' => ($setting_section_static_pages_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_MODULES_CHECKED_VALUE' => ($setting_section_modules_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_TEMPLATES_CHECKED_VALUE' => ($setting_section_templates_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_USERS_CHECKED_VALUE' => ($setting_section_users_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_MEDIA_CHECKED_VALUE' => ($setting_section_media_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_FEEDS_CHECKED_VALUE' => ($setting_section_feeds_status_value == 'on') ? 'checked' : '',
        'SETTING_SECTION_ANALYTICS_CHECKED_VALUE' => ($setting_section_analytics_status_value == 'on') ? 'checked' : '',
      ]);
    }
  }
}

?>
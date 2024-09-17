<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2023, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class SettingsBase {
    const FORM_PATH = 'templates/page/settings';

    public SystemCore $system_core;
    public string $title;
    public string $name;
    public string $description;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, string $name) {
      $this->system_core = $system_core;
      $this->name = $name;
    }

    public function set_title(string $value) : void {
      $this->title = $value;
    }

    public function set_description(string $value) : void {
      $this->description = $value;
    }

    public function get_title() : string {
      return $this->title;
    }

    public function get_description() : string {
      return $this->description;
    }

    public function assembly(array $template_values = []) {
      $form_template_path = sprintf('%s/%s.tpl', self::FORM_PATH, $this->name);
      
      $setting_engineering_works_status_value = ($this->system_core->configurator->exists_database_entry_value('base_engineering_works_status')) ? $this->system_core->configurator->get_database_entry_value('base_engineering_works_status') : '';

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, $form_template_path, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_SITE_TITLE_VALUE' => ($this->system_core->configurator->exists_database_entry_value('base_site_title')) ? $this->system_core->configurator->get_database_entry_value('base_site_title') : '',
        'SETTING_ENGINEERING_WORKS_TEXT_VALUE' => ($this->system_core->configurator->exists_database_entry_value('base_engineering_works_text')) ? $this->system_core->configurator->get_database_entry_value('base_engineering_works_text') : '',
        'SETTING_ENGINEERING_WORKS_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('base_engineering_works_status')) ? $this->system_core->configurator->get_database_entry_value('base_engineering_works_status') : 'off',
        'SETTING_ENGINEERING_WORKS_CHECKED_VALUE' => ($setting_engineering_works_status_value == 'on') ? 'checked' : '',
      ]);
    }

  }

}

?>
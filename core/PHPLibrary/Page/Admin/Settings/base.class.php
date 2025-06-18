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

    public SystemCore $CMSCore;
    public string $title;
    public string $name;
    public string $description;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param mixed $CMSCore
     * @param string $name
     * 
     * @return void
     */
    public function __construct(SystemCore $CMSCore, string $name) {
      $this->CMSCore = $CMSCore;
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
     * @param array $templateValues
     * 
     * @return string
     */
    public function assembly(array $templateValues = []) {
      $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
      
      /** @var string */
      $settingEngineeringWorksStatusValue = $this->CMSCore->configurator->get_engineering_works_status();
      /** @var string */
      $settingEngineeringWorksTextValue = $this->CMSCore->configurator->get_engineering_works_text();
      /** @var string */
      $settingEngineeringWorksTextValue = empty($settingEngineeringWorksTextValue) ? $settingEngineeringWorksTextValue : 'The site is undergoing technical work :C';

      /** @var string */
      $settingSectionEntriesStatusValue = $this->CMSCore->configurator->get_section_entries_status();
      /** @var string */
      $settingSectionStaticPagesValue = $this->CMSCore->configurator->get_section_static_pages_status();
      /** @var string */
      $settingSectionModulesStatusValue = $this->CMSCore->configurator->get_section_modules_status();
      /** @var string */
      $settingSectionThemesStatusValue = $this->CMSCore->configurator->get_section_templates_status();
      /** @var string */
      $settingSectionUsersStatusValue = $this->CMSCore->configurator->get_section_users_status();
      /** @var string */
      $settingSectionMediaStatusValue = $this->CMSCore->configurator->get_section_media_status();
      /** @var string */
      $settingSectionFeedsStatusValue = $this->CMSCore->configurator->get_section_feeds_status();
      /** @var string */
      $settingSectionAnalyticsStatusValue = $this->CMSCore->configurator->get_section_analytics_status();

      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, $formTemplatePath, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_SITE_TITLE_VALUE' => $this->CMSCore->configurator->exists_database_entry_value('base_site_title') ? $this->CMSCore->configurator->get_database_entry_value('base_site_title') : '',
        'SETTING_ENGINEERING_WORKS_TEXT_VALUE' => $settingEngineeringWorksTextValue,
        'SETTING_ENGINEERING_WORKS_STATUS_VALUE' => $settingEngineeringWorksStatusValue,
        'SETTING_SECTION_ENTRIES_STATUS_VALUE' => $settingSectionEntriesStatusValue,
        'SETTING_SECTION_STATIC_PAGES_STATUS_VALUE' => $settingSectionStaticPagesValue,
        'SETTING_SECTION_MODULES_STATUS_VALUE' => $settingSectionModulesStatusValue,
        'SETTING_SECTION_TEMPLATES_STATUS_VALUE' => $settingSectionThemesStatusValue,
        'SETTING_SECTION_USERS_STATUS_VALUE' => $settingSectionUsersStatusValue,
        'SETTING_SECTION_MEDIA_STATUS_VALUE' => $settingSectionMediaStatusValue,
        'SETTING_SECTION_FEEDS_STATUS_VALUE' => $settingSectionFeedsStatusValue,
        'SETTING_SECTION_ANALYTICS_STATUS_VALUE' => $settingSectionAnalyticsStatusValue,
        'SETTING_ENGINEERING_WORKS_CHECKED_VALUE' => $settingEngineeringWorksStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_ENTRIES_CHECKED_VALUE' => $settingSectionEntriesStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_STATIC_PAGES_CHECKED_VALUE' => $settingSectionStaticPagesValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_MODULES_CHECKED_VALUE' => $settingSectionModulesStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_TEMPLATES_CHECKED_VALUE' => $settingSectionThemesStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_USERS_CHECKED_VALUE' => $settingSectionUsersStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_MEDIA_CHECKED_VALUE' => $settingSectionMediaStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_FEEDS_CHECKED_VALUE' => $settingSectionFeedsStatusValue === 'on' ? 'checked' : '',
        'SETTING_SECTION_ANALYTICS_CHECKED_VALUE' => $settingSectionAnalyticsStatusValue === 'on' ? 'checked' : '',
      ]);
    }
  }
}

?>
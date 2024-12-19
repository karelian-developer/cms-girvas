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

  class SettingsSecurity {
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
      
      $setting_allowed_users_registration_status_value = ($this->system_core->configurator->exists_database_entry_value('security_allowed_users_registration_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_users_registration_status') : '';
      $setting_allowed_emails_status_value = ($this->system_core->configurator->exists_database_entry_value('security_allowed_emails_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_emails_status') : '';
      $setting_allowed_ip_admin_status_value = ($this->system_core->configurator->exists_database_entry_value('security_allowed_admin_ip_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_admin_ip_status') : '';
      $setting_premoderation_create_status_value = ($this->system_core->configurator->exists_database_entry_value('security_premoderation_create_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_create_status') : '';
      $setting_premoderation_links_filter_status_value = ($this->system_core->configurator->exists_database_entry_value('security_premoderation_links_filter_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_links_filter_status') : '';
      $setting_premoderation_words_filter_status_value = ($this->system_core->configurator->exists_database_entry_value('security_premoderation_words_filter_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_words_filter_status') : '';

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, $form_template_path, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_ALLOWED_USERS_REGISTRATION_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_allowed_users_registration_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_users_registration_status') : 'off',
        'SETTING_ALLOWED_USERS_REGISTRATION_CHECKED_VALUE' => ($setting_allowed_users_registration_status_value == 'on') ? 'checked' : '',
        'SETTING_ALLOWED_EMAILS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_allowed_emails')) ? implode(', ', json_decode($this->system_core->configurator->get_database_entry_value('security_allowed_emails'), true)) : '',
        'SETTING_ALLOWED_EMAILS_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_allowed_emails_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_emails_status') : 'off',
        'SETTING_ALLOWED_EMAILS_CHECKED_VALUE' => ($setting_allowed_emails_status_value == 'on') ? 'checked' : '',
        'SETTING_ALLOWED_IP_ADMIN_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_allowed_admin_ip')) ? implode(', ', json_decode($this->system_core->configurator->get_database_entry_value('security_allowed_admin_ip'), true)) : '',
        'SETTING_ALLOWED_IP_ADMIN_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_allowed_admin_ip_status')) ? $this->system_core->configurator->get_database_entry_value('security_allowed_admin_ip_status') : 'off',
        'SETTING_ALLOWED_IP_ADMIN_CHECKED_VALUE' => ($setting_allowed_ip_admin_status_value == 'on') ? 'checked' : '',
        'SETTING_PREMODERATION_CREATE_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_premoderation_create_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_create_status') : 'off',
        'SETTING_PREMODERATION_CREATE_CHECKED_VALUE' => ($setting_premoderation_create_status_value == 'on') ? 'checked' : '',
        'SETTING_NEGATIVE_EVALUATION_THRESHOLD_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_negative_evaluation_threshold')) ? $this->system_core->configurator->get_database_entry_value('security_negative_evaluation_threshold') : 0,
        'SETTING_PREMODERATION_LINKS_FILTER_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_premoderation_links_filter_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_links_filter_status') : 'off',
        'SETTING_PREMODERATION_LINKS_FILTER_CHECKED_VALUE' => ($setting_premoderation_links_filter_status_value == 'on') ? 'checked' : '',
        'SETTING_PREMODERATION_WORDS_FILTER_LIST_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_premoderation_words_filter_list')) ? implode(', ', json_decode($this->system_core->configurator->get_database_entry_value('security_premoderation_words_filter_list'), true)) : '',
        'SETTING_PREMODERATION_WORDS_FILTER_STATUS_VALUE' => ($this->system_core->configurator->exists_database_entry_value('security_premoderation_words_filter_status')) ? $this->system_core->configurator->get_database_entry_value('security_premoderation_words_filter_status') : 'off',
        'SETTING_PREMODERATION_WORDS_FILTER_CHECKED_VALUE' => ($setting_premoderation_words_filter_status_value == 'on') ? 'checked' : '',
      ]);
    }

  }

}

?>
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

  class SettingsUsers {
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
      
      $setting_upload_avatar_status_value = $this->system_core->configurator->get_users_upload_avatar_status();
      $setting_login_edit_status_value = $this->system_core->configurator->get_users_login_edit_status();
      $setting_login_special_symbols_status_value = $this->system_core->configurator->get_users_login_special_symbols_status();
      $setting_login_register_accounting_status_value = $this->system_core->configurator->get_users_login_register_accounting_status();
      $setting_password_special_symbols_status_value = $this->system_core->configurator->get_users_password_special_symbols_status();
      $setting_logins_blacklist_status_value = $this->system_core->configurator->get_users_logins_blacklist_status();

      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, $form_template_path, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_USERS_UPLOAD_AVATAR_STATUS_VALUE' => $setting_upload_avatar_status_value,
        'SETTING_USERS_UPLOAD_AVATAR_CHECKED_VALUE' => ($setting_upload_avatar_status_value == 'on') ? 'checked' : '',
        'SETTING_LOGIN_LENGTH_MIN_VALUE' => $this->system_core->configurator->get_users_login_length_min(),
        'SETTING_LOGIN_LENGTH_MAX_VALUE' => $this->system_core->configurator->get_users_login_length_max(),
        'SETTING_USERS_LOGIN_EDIT_STATUS_VALUE' => $setting_login_edit_status_value,
        'SETTING_USERS_LOGIN_EDIT_CHECKED_VALUE' => ($setting_login_edit_status_value == 'on') ? 'checked' : '',
        'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_STATUS_VALUE' => $setting_login_special_symbols_status_value,
        'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_CHECKED_VALUE' => ($setting_login_special_symbols_status_value == 'on') ? 'checked' : '',
        'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_STATUS_VALUE' => $setting_login_register_accounting_status_value,
        'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_CHECKED_VALUE' => ($setting_login_register_accounting_status_value == 'on') ? 'checked' : '',
        'SETTING_PASSWORD_LENGTH_MIN_VALUE' => $this->system_core->configurator->get_users_password_length_min(),
        'SETTING_PASSWORD_LENGTH_MAX_VALUE' => $this->system_core->configurator->get_users_password_length_max(),
        'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_STATUS_VALUE' => $setting_password_special_symbols_status_value,
        'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_CHECKED_VALUE' => ($setting_password_special_symbols_status_value == 'on') ? 'checked' : '',
        'SETTING_USERS_LOGINS_BLACKLIST_STATUS_VALUE' => $setting_logins_blacklist_status_value,
        'SETTING_USERS_LOGINS_BLACKLIST_CHECKED_VALUE' => ($setting_logins_blacklist_status_value == 'on') ? 'checked' : '',
        'SETTING_LOGINS_BLACKLIST_VALUE' => $this->system_core->configurator->get_users_logins_blacklist()
      ]);
    }

  }

}

?>
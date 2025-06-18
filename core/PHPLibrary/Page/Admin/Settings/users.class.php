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

    public SystemCore $CMSCore;
    public string $title;
    public string $name;
    public string $description;
    public string $assembled = '';

    public function __construct(SystemCore $CMSCore, string $name) {
      $this->CMSCore = $CMSCore;
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

    public function assembly(array $templateValues = []) {
      $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
      
      $settingUploadAvatarStatusValue = $this->CMSCore->configurator->get_users_upload_avatar_status();
      $settingLoginEditStatusValue = $this->CMSCore->configurator->get_users_login_edit_status();
      $settingLoginSpecialSymbolsStatusValue = $this->CMSCore->configurator->get_users_login_special_symbols_status();
      $settingLoginRegisterAccountingStatusValue = $this->CMSCore->configurator->get_users_login_register_accounting_status();
      $settingPasswordSpecialSymbolsStatusValue = $this->CMSCore->configurator->get_users_password_special_symbols_status();
      $settingLoginsBlacklistStatusValue = $this->CMSCore->configurator->get_users_logins_blacklist_status();

      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, $formTemplatePath, [
        'SETTINGS_NAME' => $this->name,
        'SETTING_USERS_UPLOAD_AVATAR_STATUS_VALUE' => $settingUploadAvatarStatusValue,
        'SETTING_USERS_UPLOAD_AVATAR_CHECKED_VALUE' => $settingUploadAvatarStatusValue === 'on' ? 'checked' : '',
        'SETTING_LOGIN_LENGTH_MIN_VALUE' => $this->CMSCore->configurator->get_users_login_length_min(),
        'SETTING_LOGIN_LENGTH_MAX_VALUE' => $this->CMSCore->configurator->get_users_login_length_max(),
        'SETTING_USERS_LOGIN_EDIT_STATUS_VALUE' => $settingLoginEditStatusValue,
        'SETTING_USERS_LOGIN_EDIT_CHECKED_VALUE' => $settingLoginEditStatusValue === 'on' ? 'checked' : '',
        'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_STATUS_VALUE' => $settingLoginSpecialSymbolsStatusValue,
        'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_CHECKED_VALUE' => $settingLoginSpecialSymbolsStatusValue === 'on' ? 'checked' : '',
        'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_STATUS_VALUE' => $settingLoginRegisterAccountingStatusValue,
        'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_CHECKED_VALUE' => $settingLoginRegisterAccountingStatusValue === 'on' ? 'checked' : '',
        'SETTING_PASSWORD_LENGTH_MIN_VALUE' => $this->CMSCore->configurator->get_users_password_length_min(),
        'SETTING_PASSWORD_LENGTH_MAX_VALUE' => $this->CMSCore->configurator->get_users_password_length_max(),
        'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_STATUS_VALUE' => $settingPasswordSpecialSymbolsStatusValue,
        'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_CHECKED_VALUE' => $settingPasswordSpecialSymbolsStatusValue === 'on' ? 'checked' : '',
        'SETTING_USERS_LOGINS_BLACKLIST_STATUS_VALUE' => $settingLoginsBlacklistStatusValue,
        'SETTING_USERS_LOGINS_BLACKLIST_CHECKED_VALUE' => $settingLoginsBlacklistStatusValue === 'on' ? 'checked' : '',
        'SETTING_LOGINS_BLACKLIST_VALUE' => $this->CMSCore->configurator->get_users_logins_blacklist()
      ]);
    }

  }

}

?>
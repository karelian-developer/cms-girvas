<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsUsers implements SettingsPageInterface
{
  const FORM_PATH = 'templates/page/settings';

  public string $title;
  public string $description;
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param CMSCore $CMSCore
   * @param string $name
   * 
   * @return void
   */
  public function __construct(
    public CMSCore $CMSCore,
    public string $name
  ) {}

  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }

  public function getTitle() : string
  {
    return $this->title;
  }

  public function getDescription() : string
  {
    return $this->description;
  }

  public function assembly(array $templateValues = []) : void
  {
    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
    
    $settingUploadAvatarStatusValue = $this->CMSCore->configurator->getUsersUploadAvatarStatus();
    $settingLoginEditStatusValue = $this->CMSCore->configurator->getUsersLoginEditStatus();
    $settingLoginSpecialSymbolsStatusValue = $this->CMSCore->configurator->getUsersLoginSpecialSymbolsStatus();
    $settingLoginRegisterAccountingStatusValue = $this->CMSCore->configurator->getUsersLoginRegisterAccountingStatus();
    $settingPasswordSpecialSymbolsStatusValue = $this->CMSCore->configurator->getUsersPasswordSpecialSymbolsStatus();
    $settingLoginsBlacklistStatusValue = $this->CMSCore->configurator->getUsersLoginsBlacklistStatus();

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_USERS_UPLOAD_AVATAR_STATUS_VALUE' => $settingUploadAvatarStatusValue,
      'SETTING_USERS_UPLOAD_AVATAR_CHECKED_VALUE' => $settingUploadAvatarStatusValue === 'on' ? 'checked' : '',
      'SETTING_LOGIN_LENGTH_MIN_VALUE' => $this->CMSCore->configurator->getUsersLoginLengthMin(),
      'SETTING_LOGIN_LENGTH_MAX_VALUE' => $this->CMSCore->configurator->getUsersLoginLengthMax(),
      'SETTING_USERS_LOGIN_EDIT_STATUS_VALUE' => $settingLoginEditStatusValue,
      'SETTING_USERS_LOGIN_EDIT_CHECKED_VALUE' => $settingLoginEditStatusValue === 'on' ? 'checked' : '',
      'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_STATUS_VALUE' => $settingLoginSpecialSymbolsStatusValue,
      'SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_CHECKED_VALUE' => $settingLoginSpecialSymbolsStatusValue === 'on' ? 'checked' : '',
      'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_STATUS_VALUE' => $settingLoginRegisterAccountingStatusValue,
      'SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_CHECKED_VALUE' => $settingLoginRegisterAccountingStatusValue === 'on' ? 'checked' : '',
      'SETTING_PASSWORD_LENGTH_MIN_VALUE' => $this->CMSCore->configurator->getUsersPasswordLengthMin(),
      'SETTING_PASSWORD_LENGTH_MAX_VALUE' => $this->CMSCore->configurator->getUsersPasswordLengthMax(),
      'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_STATUS_VALUE' => $settingPasswordSpecialSymbolsStatusValue,
      'SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_CHECKED_VALUE' => $settingPasswordSpecialSymbolsStatusValue === 'on' ? 'checked' : '',
      'SETTING_USERS_LOGINS_BLACKLIST_STATUS_VALUE' => $settingLoginsBlacklistStatusValue,
      'SETTING_USERS_LOGINS_BLACKLIST_CHECKED_VALUE' => $settingLoginsBlacklistStatusValue === 'on' ? 'checked' : '',
      'SETTING_LOGINS_BLACKLIST_VALUE' => $this->CMSCore->configurator->getUsersLoginsBlacklist()
    ]);
  }
}
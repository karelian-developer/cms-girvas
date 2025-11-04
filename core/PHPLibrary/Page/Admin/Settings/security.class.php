<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsSecurity implements SettingsPageInterface
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
    
    $settingAllowedUsersRegistrationStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_users_registration_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_users_registration_status') : '';
    $settingAllowedEmailsStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails_status') : '';
    $settingAllowedIPAdminStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip_status') : '';
    $settingPremoderationCreateStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_create_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_create_status') : '';
    $settingPremoderationLinksFilterStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_links_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status') : '';
    $settingPremoderationWordsFilterStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_status') : '';

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_ALLOWED_USERS_REGISTRATION_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_users_registration_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_users_registration_status') : 'off',
      'SETTING_ALLOWED_USERS_REGISTRATION_CHECKED_VALUE' => $settingAllowedUsersRegistrationStatusValue === 'on' ? 'checked' : '',
      'SETTING_ALLOWED_EMAILS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails'), true)) : '',
      'SETTING_ALLOWED_EMAILS_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails_status') : 'off',
      'SETTING_ALLOWED_EMAILS_CHECKED_VALUE' => $settingAllowedEmailsStatusValue === 'on' ? 'checked' : '',
      'SETTING_ALLOWED_IP_ADMIN_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip'), true)) : '',
      'SETTING_ALLOWED_IP_ADMIN_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip_status') : 'off',
      'SETTING_ALLOWED_IP_ADMIN_CHECKED_VALUE' => $settingAllowedIPAdminStatusValue === 'on' ? 'checked' : '',
      'SETTING_PREMODERATION_CREATE_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_create_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_create_status') : 'off',
      'SETTING_PREMODERATION_CREATE_CHECKED_VALUE' => $settingPremoderationCreateStatusValue === 'on' ? 'checked' : '',
      'SETTING_NEGATIVE_EVALUATION_THRESHOLD_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_negative_evaluation_threshold') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_negative_evaluation_threshold') : 0,
      'SETTING_PREMODERATION_LINKS_FILTER_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_links_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status') : 'off',
      'SETTING_PREMODERATION_LINKS_FILTER_CHECKED_VALUE' => $settingPremoderationLinksFilterStatusValue === 'on' ? 'checked' : '',
      'SETTING_PREMODERATION_WORDS_FILTER_LIST_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_list') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_list'), true)) : '',
      'SETTING_PREMODERATION_WORDS_FILTER_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_status') : 'off',
      'SETTING_PREMODERATION_WORDS_FILTER_CHECKED_VALUE' => $settingPremoderationWordsFilterStatusValue === 'on' ? 'checked' : '',
    ]);
  }
}
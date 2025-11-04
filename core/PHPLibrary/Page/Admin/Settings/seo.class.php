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

class SettingsSeo implements SettingsPageInterface
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

    $fileRobotsTXTPath = CMS_ROOT_DIRECTORY . '/robots.txt';
    $fileRobotsTXTContent = file_exists($fileRobotsTXTPath) ? file_get_contents($fileRobotsTXTPath) : '';
    
    $settingPermanentRedirectWWWStatusValue = $this->CMSCore->configurator->getPermanentRedirectToWWWStatus();

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, $formTemplatePath,
      [
        'SETTINGS_NAME' => $this->name,
        'SETTING_CODE_YANDEX_WEBMASTER_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_code_yandex_webmaster')
          ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_code_yandex_webmaster')
          : '',
        'SETTING_SITE_DESCRIPTION_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_description')
          ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_site_description')
          : '',
        'SETTING_SITE_KEYWORDS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_keywords')
          ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('seo_site_keywords'), true))
          : '',
        'SETTING_SITE_ROBOTS_TXT_VALUE' => $fileRobotsTXTContent,
        'SETTING_PERMANENT_REDIRECT_WWW_STATUS_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'on' : 'off',
        'SETTING_PERMANENT_REDIRECT_WWW_CHECKED_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'checked' : '',
      ]
    );
  }
}
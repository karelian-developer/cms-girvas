<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as TemplateCollector;

class SettingsSeo
{
  const FORM_PATH = 'templates/page/settings';

  public SystemCore $CMSCore;
  public string $title;
  public string $name;
  public string $description;
  public string $assembled = '';

  public function __construct(SystemCore $CMSCore, string $name)
  {
    $this->CMSCore = $CMSCore;
    $this->name = $name;
  }

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

    $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_SITE_DESCRIPTION_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_description') ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_site_description') : '',
      'SETTING_SITE_KEYWORDS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_keywords') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('seo_site_keywords'), true)) : '',
      'SETTING_SITE_ROBOTS_TXT_VALUE' => $fileRobotsTXTContent,
      'SETTING_PERMANENT_REDIRECT_WWW_STATUS_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'on' : 'off',
      'SETTING_PERMANENT_REDIRECT_WWW_CHECKED_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'checked' : '',
    ]);
  }
}
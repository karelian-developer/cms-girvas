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

  /**
   * Имя админской локали.
   */
  private function getAdminLocaleName(): string
  {
    return $this->CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale')
      ? (string) $this->CMSCore->configurator->getDatabaseEntryValue('base_admin_locale')
      : 'ru_RU';
  }

  /**
   * Получить мультиязычное значение настройки для указанной локали
   *
   * @param string $settingName
   * @param string $localeName
   *
   * @return mixed
   */
  private function getLocalizedSettingValue(string $settingName, string $localeName): mixed
  {
    if (!$this->CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
      return null;
    }

    $raw = $this->CMSCore->configurator->getDatabaseEntryValue($settingName);

    if (!is_string($raw) || $raw === '') {
      return $raw;
    }

    $decoded = json_decode($raw, true);

    if (is_array($decoded) && !array_is_list($decoded)) {
      return $decoded[$localeName] ?? null;
    }

    if (is_array($decoded) && array_is_list($decoded)) {
      return $decoded;
    }

    return $raw;
  }

  public function assembly(array $templateValues = []) : void
  {
    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';

    $fileRobotsTXTPath = CMS_ROOT_DIRECTORY . '/robots.txt';
    $fileLLMSTXTPath = CMS_ROOT_DIRECTORY . '/llms.txt';

    $fileRobotsTXTContent = file_exists($fileRobotsTXTPath) ? file_get_contents($fileRobotsTXTPath) : '';
    $fileLLMSTXTContent = file_exists($fileLLMSTXTPath) ? file_get_contents($fileLLMSTXTPath) : '';
    
    $settingPermanentRedirectWWWStatusValue = $this->CMSCore->configurator->getPermanentRedirectToWWWStatus();

    $adminLocaleName = $this->getAdminLocaleName();

    $siteDescription = $this->getLocalizedSettingValue('seo_site_description', $adminLocaleName);
    $siteDescription = is_string($siteDescription) ? $siteDescription : '';

    $siteKeywordsRaw = $this->getLocalizedSettingValue('seo_site_keywords', $adminLocaleName);
    if (is_array($siteKeywordsRaw)) {
      $siteKeywords = implode(', ', $siteKeywordsRaw);
    } elseif (is_string($siteKeywordsRaw)) {
      $siteKeywords = $siteKeywordsRaw;
    } else {
      $siteKeywords = '';
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, $formTemplatePath,
      [
        'SETTINGS_NAME' => $this->name,
        'SETTINGS_ADMIN_LOCALE'  => $adminLocaleName,
        'SETTING_CODE_YANDEX_WEBMASTER_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('seo_code_yandex_webmaster')
          ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_code_yandex_webmaster')
          : '',
        'SETTING_SITE_DESCRIPTION_VALUE' => $siteDescription,
        'SETTING_SITE_KEYWORDS_VALUE'    => $siteKeywords,
        'SETTING_SITE_ROBOTS_TXT_VALUE'  => $fileRobotsTXTContent,
        'SETTING_SITE_LLMS_TXT_VALUE'    => $fileLLMSTXTContent,
        'SETTING_PERMANENT_REDIRECT_WWW_STATUS_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'on' : 'off',
        'SETTING_PERMANENT_REDIRECT_WWW_CHECKED_VALUE' => $settingPermanentRedirectWWWStatusValue ? 'checked' : '',
      ]
    );
  }
}
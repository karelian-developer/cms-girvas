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

/**
 * Class SettingsBase
 */
class SettingsBase implements SettingsPageInterface
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

  /**
   * Установить заголовок
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  /**
   * Установить описание
   * 
   * @param string $value
   * 
   * @return void
   */
  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }
  
  /**
   * Получить заголовок
   * 
   * @return string
   */
  public function getTitle() : string
  {
    return $this->title;
  }

  /**
   * Получить описание
   * 
   * @return string
   */
  public function getDescription() : string
  {
    return $this->description;
  }

  /**
   * Собрать шаблон
   * 
   * @param array $templateValues
   * 
   * @return string
   */
  public function assembly(array $templateValues = []) : void
  {
    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
    
    /** @var string */
    $settingEngineeringWorksStatusValue = $this->CMSCore->configurator->getEngineeringWorksStatus();
    /** @var string */
    $settingEngineeringWorksTextValue = $this->CMSCore->configurator->getEngineeringWorksText();
    /** @var string */
    $settingEngineeringWorksTextValue = empty($settingEngineeringWorksTextValue) ? $settingEngineeringWorksTextValue : 'The site is undergoing technical work :C';

    /** @var string */
    $settingSectionEntriesStatusValue = $this->CMSCore->configurator->getSectionEntriesStatus();
    /** @var string */
    $settingSectionStaticPagesValue = $this->CMSCore->configurator->getSectionStaticPagesStatus();
    /** @var string */
    $settingSectionModulesStatusValue = $this->CMSCore->configurator->getSectionModulesStatus();
    /** @var string */
    $settingSectionThemesStatusValue = $this->CMSCore->configurator->getSectionTemplatesStatus();
    /** @var string */
    $settingSectionUsersStatusValue = $this->CMSCore->configurator->getSectionUsersStatus();
    /** @var string */
    $settingSectionMediaStatusValue = $this->CMSCore->configurator->getSectionMediaStatus();
    /** @var string */
    $settingSectionFeedsStatusValue = $this->CMSCore->configurator->getSectionFeedsStatus();
    /** @var string */
    $settingSectionAnalyticsStatusValue = $this->CMSCore->configurator->getSectionAnalyticsStatus();

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_SITE_TITLE_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('base_site_title') ? $this->CMSCore->configurator->getDatabaseEntryValue('base_site_title') : '',
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
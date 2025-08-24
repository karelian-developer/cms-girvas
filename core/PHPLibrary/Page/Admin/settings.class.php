<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Page\Admin\Settings\SettingsPageInterface as SettingsPageInterface;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageSettings implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_SETTINGS_NAVIGATION_%s_LABEL';

  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false
    ],
  ];

  /**
   * __construct
   */
  public function __construct(
    public CMSCore $CMSCore,
    public Page $page
  ) {}

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;

    $availableSettingsCategories = $this->getAvailableSettingsCategoriesArray();
    if (!empty($availableSettingsCategories)) {
      $settingsName = $this->CMSCore->urlp->getPath(2) ?? 'base';

      foreach ($availableSettingsCategories as $category) {
        $this->navigationSubsections[$category] = [
          'name' => $category,
          'iconName' => 'settingsGroup' . ucfirst($category),
          'link' => '/settings/' . $category,
          'permanent' => true,
          'isActive' => $settingsName === $category ? true : false
        ];
      }
    }

    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function getAvailableSettingsCategoriesArray() : array
  {
    $settings = [];

    $settingsClassesFilesPath = $this->CMSCore->getCMSPath() . '/core/PHPLibrary/Page/Admin/Settings';
    $settingsClassesFiles = array_diff(scandir($settingsClassesFilesPath), ['.', '..']);

    foreach ($settingsClassesFiles as $file) {
      if (preg_match('/^([a-zA-Z_]+)\.class\.php$/', $file, $matches)) {
        array_push($settings, $matches[1]);
      }
    }

    return $settings;
  }

  /**
   * Конвертировать имя настройки в константу
   * 
   * @param string $settingName
   * 
   * @return string
   */
  private function convertSettingNameToConstant(string $settingName) : string
  {
    return match ($settingName) {
      'pages' => 'STATIC_PAGES',
      default => strtoupper($settingName)
    };
  }

  /**
   * Получить пространство имен страницы с настройками
   * 
   * @param string $settingName
   * 
   * @return string
   */
  private function getSettingsPageClassNamespace(string $settingName) : string
  {
    return '\\core\\PHPLibrary\\Page\\Admin\\Settings\\Settings' . ucfirst($settingsName);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/settings.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $settingsName = $this->CMSCore->urlp->getPath(2) ?? 'base';

    $settingsCorePath = $this->CMSCore->getCMSPath() . '/core/PHPLibrary/Page/Admin/Settings/' . $settingsName . '.class.php';
    if (file_exists($settingsCorePath)) {
      http_response_code(200);

      $classNamespace = $this->getSettingsPageClassNamespace($settingsName);
      $settings = new $classNamespace($this->CMSCore, $settingsName);
      
      $settingsNameConstant = $this->convertSettingNameToConstant();

      $settings->setTitle('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_' . $settingsNameConstant . '_TITLE}');
      $settings->setDescription('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_' . $settingsNameConstant . '_DESCRIPTION}');

      $settingsTitle = $settings->getTitle();
      $settingsDescription = $settings->getDescription();
      $settings->assembly();
    } else {
      http_response_code(404);
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/settings.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'settings',
      'SETTINGS_TITLE' => $settingsTitle ?? $localeData['PAGE_SETTINGS_GROUP_NOT_FOUND_TITLE'],
      'SETTINGS_DESCRIPTION' => $settingsDescription ?? $localeData['PAGE_SETTINGS_GROUP_NOT_FOUND_DESCRIPTION'],
      'SETTINGS_FORM' => ThemeCollector::assembly($settings->assembled, [])
    ]);
  }
}
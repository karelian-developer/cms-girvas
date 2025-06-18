<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageSettings implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_SETTINGS_NAVIGATION_%s_LABEL';

    public SystemCore $CMSCore;
    public Page $page;
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

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;

      $availableSettingsCategories = $this->get_available_settings_categories_array();
      if (!empty($availableSettingsCategories)) {
        $settingsName = !is_null($this->CMSCore->urlp->get_path(2)) ? $this->CMSCore->urlp->get_path(2) : 'base';

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

      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function get_available_settings_categories_array() : array {
      $settings = [];

      $settingsClassesFilesPath = $this->CMSCore->get_cms_path() . '/core/PHPLibrary/Page/Admin/Settings';
      $settingsClassesFiles = array_diff(scandir($settingsClassesFilesPath), ['.', '..']);

      foreach ($settingsClassesFiles as $file) {
        if (preg_match('/^([a-zA-Z_]+)\.class\.php$/', $file, $matches)) {
          array_push($settings, $matches[1]);
        }
      }

      return $settings;
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/settings.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $settingsName = !is_null($this->CMSCore->urlp->get_path(2)) ? $this->CMSCore->urlp->get_path(2) : 'base';

      $settingsCorePath = $this->CMSCore->get_cms_path() . '/core/PHPLibrary/Page/Admin/Settings/' . $settingsName . '.class.php';
      if (file_exists($settingsCorePath)) {
        http_response_code(200);

        $classNamespace = '\\core\\PHPLibrary\\Page\\Admin\\Settings\\Settings' . ucfirst($settingsName);
        $settings = new $classNamespace($this->CMSCore, $settingsName);

        if ($settingsName == 'base') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_DESCRIPTION}');
        }

        if ($settingsName == 'files') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_FILES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_FILES_DESCRIPTION}');
        }

        if ($settingsName == 'seo') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SEO_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SEO_DESCRIPTION}');
        }

        if ($settingsName == 'security') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SECURITY_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SECURITY_DESCRIPTION}');
        }

        if ($settingsName == 'users') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_USERS_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_USERS_DESCRIPTION}');
        }

        if ($settingsName == 'entries') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_ENTRIES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_ENTRIES_DESCRIPTION}');
        }

        if ($settingsName == 'pages') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_STATIC_PAGES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_STATIC_PAGES_DESCRIPTION}');
        }

        $settingsTitle = $settings->get_title();
        $settingsDescription = $settings->get_description();
        $settings->assembly();
      } else {
        http_response_code(404);
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/settings.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'settings',
        'SETTINGS_TITLE' => isset($settingsTitle) ? $settingsTitle : $localeData['PAGE_SETTINGS_GROUP_NOT_FOUND_TITLE'],
        'SETTINGS_DESCRIPTION' => isset($settingsDescription) ? $settingsDescription : $localeData['PAGE_SETTINGS_GROUP_NOT_FOUND_DESCRIPTION'],
        'SETTINGS_FORM' => TemplateCollector::assembly($settings->assembled, [])
      ]);
    }
  }
}

?>
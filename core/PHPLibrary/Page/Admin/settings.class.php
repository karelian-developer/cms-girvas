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

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'index' => [
        'name' => 'index',
        'iconName' => 'index',
        'link' => '/',
        'permanent' => true,
        'isActive' => false
      ],
    ];

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;

      $available_settings_categories_array = $this->get_available_settings_categories_array();
      if (!empty($available_settings_categories_array)) {
        $settings_name = (!is_null($this->system_core->urlp->get_path(2))) ? $this->system_core->urlp->get_path(2) : 'base';

        foreach ($available_settings_categories_array as $available_setting_category) {
          $this->navigation_subsections_array[$available_setting_category] = [
            'name' => $available_setting_category,
            'iconName' => sprintf('settingsGroup%s', ucfirst($available_setting_category)),
            'link' => sprintf('/settings/%s', $available_setting_category),
            'permanent' => true,
            'isActive' => ($settings_name == $available_setting_category) ? true : false
          ];
        }
      }

      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function get_available_settings_categories_array() : array {
      $settings = [];

      $settings_classes_files_path = sprintf('%s/core/PHPLibrary/Page/Admin/Settings', $this->system_core->get_cms_path());
      $settings_classes_files_array = array_diff(scandir($settings_classes_files_path), ['.', '..']);

      foreach ($settings_classes_files_array as $setting_class_file) {
        if (preg_match('/^([a-zA-Z_]+)\.class\.php$/', $setting_class_file, $matches)) {
          array_push($settings, $matches[1]);
        }
      }

      return $settings;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/settings.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();
      $settings_name = (!is_null($this->system_core->urlp->get_path(2))) ? $this->system_core->urlp->get_path(2) : 'base';

      $settings_core_path = sprintf('%s/core/PHPLibrary/Page/Admin/Settings/%s.class.php', $this->system_core->get_cms_path(), $settings_name);
      if (file_exists($settings_core_path)) {
        http_response_code(200);

        $class_namespace = sprintf('\\core\\PHPLibrary\\Page\\Admin\\Settings\\Settings%s', ucfirst($settings_name));
        $settings = new $class_namespace($this->system_core, $settings_name);

        if ($settings_name == 'base') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_DESCRIPTION}');
        }

        if ($settings_name == 'files') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_FILES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_FILES_DESCRIPTION}');
        }

        if ($settings_name == 'seo') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SEO_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SEO_DESCRIPTION}');
        }

        if ($settings_name == 'security') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SECURITY_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_SECURITY_DESCRIPTION}');
        }

        if ($settings_name == 'users') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_USERS_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_USERS_DESCRIPTION}');
        }

        if ($settings_name == 'entries') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_ENTRIES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_ENTRIES_DESCRIPTION}');
        }

        if ($settings_name == 'pages') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_STATIC_PAGES_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_STATIC_PAGES_DESCRIPTION}');
        }

        $settings_title = $settings->get_title();
        $settings_description = $settings->get_description();
        $settings->assembly();
      } else {
        http_response_code(404);
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/settings.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'settings',
        'SETTINGS_TITLE' => (isset($settings_title)) ? $settings_title : $locale_data['PAGE_SETTINGS_GROUP_NOT_FOUND_TITLE'],
        'SETTINGS_DESCRIPTION' => (isset($settings_description)) ? $settings_description : $locale_data['PAGE_SETTINGS_GROUP_NOT_FOUND_DESCRIPTION'],
        'SETTINGS_FORM' => TemplateCollector::assembly($settings->assembled, [])
      ]);
    }

  }

}

?>
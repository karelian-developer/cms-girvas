<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageSettings implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
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

      $available_settings_transformed = [];
      $available_settings_categories_array = $this->get_available_settings_categories_array();
      if (!empty($available_settings_categories_array)) {
        foreach ($available_settings_categories_array as $available_setting_category) {
          $item_class_is_active = ($settings_name == $available_setting_category) ? 'navigation-item__link_is-active' : '';
          
          array_push($available_settings_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
            'NAVIGATION_ITEM_TITLE' => sprintf('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_%s_TITLE}', mb_strtoupper($available_setting_category)),
            'NAVIGATION_ITEM_URL' => sprintf('/admin/settings/%s', $available_setting_category),
            'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => $item_class_is_active
          ]));
        }
      }

      if (!empty($available_settings_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($available_settings_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $settings_core_path = sprintf('%s/core/PHPLibrary/Page/Admin/Settings/%s.class.php', $this->system_core->get_cms_path(), $settings_name);
      if (file_exists($settings_core_path)) {
        http_response_code(200);

        $class_namespace = sprintf('\\core\\PHPLibrary\\Page\\Admin\\Settings\\Settings%s', ucfirst($settings_name));
        $settings = new $class_namespace($this->system_core, $settings_name);

        if ($settings_name == 'base') {
          $settings->set_title('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_TITLE}');
          $settings->set_description('{LANG:PAGE_SETTINGS_SETTINGS_GROUP_BASE_DESCRIPTION}');
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

        $settings_title = $settings->get_title();
        $settings_description = $settings->get_description();
        $settings->assembly();
      } else {
        http_response_code(404);
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/settings.tpl', [
        'PAGE_NAVIGATION' => $page_navigation_transformed,
        'SETTINGS_TITLE' => (isset($settings_title)) ? $settings_title : $locale_data['PAGE_SETTINGS_GROUP_NOT_FOUND_TITLE'],
        'SETTINGS_DESCRIPTION' => (isset($settings_description)) ? $settings_description : $locale_data['PAGE_SETTINGS_GROUP_NOT_FOUND_DESCRIPTION'],
        'SETTINGS_FORM' => TemplateCollector::assembly($settings->assembled, [])
      ]);
    }

  }

}

?>
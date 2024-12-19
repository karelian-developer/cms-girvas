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
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Module as Module;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageModules implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/modules.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $parsedown = new Parsedown();

      $subpage_name = (!is_null($this->system_core->urlp->get_path(2))) ? $this->system_core->urlp->get_path(2) : 'local';

      $navigations_items_transformed = [];
      $navigations_items = ['local', 'repository'];

      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_MODULES_NAVIGATION_INDEX_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));

      foreach ($navigations_items as $navigation_item) {
        $item_class_is_active = ($subpage_name == $navigation_item) ? 'navigation-item__link_is-active' : '';

        array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
          'NAVIGATION_ITEM_TITLE' => sprintf('{LANG:TEMPLATES_PAGE_%s_TITLE}', mb_strtoupper($navigation_item)),
          'NAVIGATION_ITEM_URL' => sprintf('/admin/modules/%s', $navigation_item),
          'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => $item_class_is_active
        ]));
      }

      if (!empty($navigations_items_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $modules_count_total = 0;

      if ($this->system_core->urlp->get_path(2) == 'repository') {
        
        if (is_null($this->system_core->urlp->get_path(3))) {
          $ch = curl_init('https://repository.cms-girvas.ru/modules');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $curl_exucute_result = json_decode(curl_exec($ch), true);
          curl_close($ch);

          if (isset($curl_exucute_result['outputData'])) {
            $modules_list_items_transformed_array = [];

            if (count($curl_exucute_result['outputData']) > 0) {
              $modules_count_total = count($curl_exucute_result['outputData']);
              $curl_exucute_result['outputData'] = array_slice($curl_exucute_result['outputData'], $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);

              foreach ($curl_exucute_result['outputData'] as $module_name => $module_data) {
                $module = new Module($this->system_core, $module_name);
                $module_installed_status = ($module->exists_file_metadata_json()) ? 'installed' : 'not-installed';
                $module_enabled_status = ($module->is_enabled()) ? 'enabled' : 'disabled';

                $module_metadata_title = isset($module_data['metadata']['title']) ? $module_data['metadata']['title'] : 'Anonymous Module';
                $module_metadata_description = isset($module_data['metadata']['description']) ? $module_data['metadata']['description'] : 'Without description.';
                $module_metadata_datetime_created_unix = isset($module_data['metadata']['datetimeCreatedUnix']) ? $module_data['metadata']['datetimeCreatedUnix'] : 0;
                $module_metadata_author_name = isset($module_data['metadata']['authorName']) ? $module_data['metadata']['authorName'] : 'Anonymous';
                $module_metadata_category_name = isset($module_data['metadata']['categoryName']) ? $module_data['metadata']['categoryName'] : 'default';

                array_push($modules_list_items_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/modules/listItem.tpl', [
                  'MODULE_NAME' => $module_name,
                  'MODULE_TITLE' => $module_metadata_title,
                  'MODULE_DESCRIPTION' => $parsedown->text($module_metadata_description),
                  'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $module_metadata_datetime_created_unix),
                  'MODULE_AUTHOR_NAME' => $module_metadata_author_name,
                  'MODULE_LINK' => sprintf('/admin/modules/repository/%s', $module->get_name()),
                  'MODULE_PREVIEW_URL' => $module_data['preview'],
                  'MODULE_INSTALLED_STATUS' => $module_installed_status,
                  'MODULE_ENABLED_STATUS' => $module_enabled_status,
                  'TEMPLATE_CATEGORY_NAME' => $module_metadata_category_name
                ]));
              }
            }
          } else {
            $modules_list_items_transformed_array = [];
          }
        }

      } elseif ($this->system_core->urlp->get_path(2) == 'local' || is_null($this->system_core->urlp->get_path(2))) {

        $modules_list_items_transformed_array = [];
        $uploaded_modules_names = $this->system_core->get_array_uploaded_modules_names();
        if (count($uploaded_modules_names) > 0) {
          $modules_count_total = count($uploaded_modules_names);
          $uploaded_modules_names = array_slice($uploaded_modules_names, $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);

          foreach ($uploaded_modules_names as $module_name) {
            $module = new Module($this->system_core, $module_name);
            $module_installed_status = ($module->exists_file_metadata_json()) ? 'installed' : 'not-installed';
            $module_enabled_status = ($module->is_enabled()) ? 'enabled' : 'disabled';

            if ($module->exists_file_metadata_json()) {
              array_push($modules_list_items_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/modules/listItem.tpl', [
                'MODULE_NAME' => $module->get_name(),
                'MODULE_TITLE' => $module->get_title(),
                'MODULE_DESCRIPTION' => $parsedown->text($module->get_description()),
                'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $module->get_core_created_unix_timestamp()),
                'MODULE_AUTHOR' => $module->get_author_name(),
                'MODULE_PREVIEW_URL' => $module->get_preview_url(),
                'MODULE_LINK' => sprintf('/admin/module/%s', $module->get_name()),
                'MODULE_INSTALLED_STATUS' => $module_installed_status,
                'MODULE_ENABLED_STATUS' => $module_enabled_status
              ]));
            }

            unset($module);
          }
        }

      }

      $pagination = new Pagination($this->system_core, $modules_count_total, $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      if ($this->system_core->urlp->get_path(2) == 'repository' && !is_null($this->system_core->urlp->get_path(3))) {
        $module_name = $this->system_core->urlp->get_path(3);
        $module_page = new PageModule($this->system_core, $this->page);
        
        $module_page->assembly();
        $this->assembled = $module_page->assembled;
      } else {
        /** @var string $assembled Содержимое шаблона страницы */
        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/modules.tpl', [
          'PAGE_NAVIGATION' => $page_navigation_transformed,
          'PAGE_MODULES_PAGINATION' => $pagination->assembled,
          'ADMIN_PANEL_PAGE_NAME' => 'modules',
          'MODULES_LIST' => (!empty($modules_list_items_transformed_array)) ? TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/modules/list.tpl', [
            'MODULES_LIST_ITEMS' => implode($modules_list_items_transformed_array)
          ]) : sprintf('<p class="page__content-phar">%s</p>', $locale_data['PAGE_MODULES_MODULES_INSTALLED_NOT_FOUND_TITLE'])
        ]);
      }
    }
  }
}

?>
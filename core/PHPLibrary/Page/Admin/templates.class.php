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
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageTemplates implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/templates.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $parsedown = new Parsedown();

      $subpage_name = (!is_null($this->system_core->urlp->get_path(2))) ? $this->system_core->urlp->get_path(2) : 'local';

      $navigations_items_transformed = [];
      $navigations_items = ['local', 'repository'];

      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_TEMPLATES_NAVIGATION_INDEX_LABEL']),
        'NAVIGATION_ITEM_URL' => '/admin',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));

      foreach ($navigations_items as $navigation_item) {
        $item_class_is_active = ($subpage_name == $navigation_item) ? 'navigation-item__link_is-active' : '';

        array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
          'NAVIGATION_ITEM_TITLE' => sprintf('{LANG:TEMPLATES_PAGE_%s_TITLE}', mb_strtoupper($navigation_item)),
          'NAVIGATION_ITEM_URL' => sprintf('/admin/templates/%s', $navigation_item),
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

      $templates_count_total = 0;

      $templates_list_items_transformed_array = [];

      if ($this->system_core->urlp->get_path(2) == 'repository') {
        $ch = curl_init('https://repository.cms-girvas.ru/templates');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($curl_result['outputData'])) {
          if (count($curl_result['outputData']) > 0) {
            $templates_count_total = count($curl_result['outputData']);
            $curl_result['outputData'] = array_slice($curl_result['outputData'], $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);

            foreach ($curl_result['outputData'] as $template_name => $template_data) {
              $template = new Template($this->system_core, $template_name);
              $template_installed_status = ($template->exists_file_metadata_json()) ? 'installed' : 'not-installed';

              array_push($templates_list_items_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/templates/listItem.tpl', [
                'TEMPLATE_NAME' => $template_name,
                'TEMPLATE_TITLE' => $template_data['metadata']['title'],
                'TEMPLATE_DESCRIPTION' => $parsedown->text($template_data['metadata']['description']),
                'TEMPLATE_CREATED_TIMESTAMP' => date('d.m.Y', $template_data['metadata']['createdUnixTimestamp']),
                'TEMPLATE_AUTHOR' => $template_data['metadata']['authorName'],
                'TEMPLATE_PREVIEW_URL' => $template_data['previews'][0],
                'TEMPLATE_INSTALLED_STATUS' => $template_installed_status,
                'TEMPLATE_CATEGORY_NAME' => (isset($template_data['metadata']['categoryName'])) ? $template_data['metadata']['categoryName'] : 'default'
              ]));
            }
          }
        }
      } elseif ($this->system_core->urlp->get_path(2) == 'local' || is_null($this->system_core->urlp->get_path(2))) {
        $uploaded_templates_names = $this->system_core->get_array_uploaded_templates_names();
        $uploaded_templates_names = array_diff($uploaded_templates_names, ['admin', 'install']);

        if (count($uploaded_templates_names) > 0) {
          $templates_count_total = count($uploaded_templates_names);
          $uploaded_templates_names = array_slice($uploaded_templates_names, $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);

          foreach ($uploaded_templates_names as $template_name) {
            $template = new Template($this->system_core, $template_name);
            $template_installed_status = ($template->exists_file_metadata_json()) ? 'installed' : 'not-installed';
            
            if ($template->exists_file_metadata_json()) {
              array_push($templates_list_items_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/templates/listItem.tpl', [
                'TEMPLATE_NAME' => $template->get_name(),
                'TEMPLATE_TITLE' => $template->get_title(),
                'TEMPLATE_DESCRIPTION' => $parsedown->text($template->get_description()),
                'TEMPLATE_CREATED_TIMESTAMP' => date('d.m.Y', $template->get_core_created_unix_timestamp()),
                'TEMPLATE_AUTHOR' => $template->get_author_name(),
                'TEMPLATE_PREVIEW_URL' => $template->get_preview_url(),
                'TEMPLATE_PAGE_URL' => sprintf('/admin/template/%s', $template->get_name()),
                'TEMPLATE_INSTALLED_STATUS' => $template_installed_status,
                'TEMPLATE_CATEGORY_NAME' => $template->get_category_name(),
              ]));
            }

            unset($template);
          }
        }

      }

      $pagination = new Pagination($this->system_core, $templates_count_total, $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      if ($this->system_core->urlp->get_path(2) == 'repository' && !is_null($this->system_core->urlp->get_path(3))) {
        $template_name = $this->system_core->urlp->get_path(3);
        $template_page = new PageTemplate($this->system_core, $this->page);
        
        $template_page->assembly();
        $this->assembled = $template_page->assembled;
      } else {
        /** @var string $site_page Содержимое шаблона страницы */
        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/templates.tpl', [
          'PAGE_NAVIGATION' => $page_navigation_transformed,
          'PAGE_TEMPLATES_PAGINATION' => $pagination->assembled,
          'ADMIN_PANEL_PAGE_NAME' => 'templates',
          'TEMPLATES_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/templates/list.tpl', [
            'TEMPLATES_LIST_ITEMS' => implode('', $templates_list_items_transformed_array)
          ])
        ]);
      }
    }
  }
}

?>
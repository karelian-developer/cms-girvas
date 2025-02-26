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
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageTemplates implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_TEMPLATES_NAVIGATION_%s_LABEL';

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
      'local' => [
        'name' => 'local',
        'iconName' => 'local',
        'link' => '/templates/local',
        'permanent' => true,
        'isActive' => false
      ],
      'repository' => [
        'name' => 'repository',
        'iconName' => 'repository',
        'link' => '/templates/repository',
        'permanent' => true,
        'isActive' => false
      ]
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
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/templates.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $parsedown = new Parsedown();

      $subpage_name = (!is_null($this->system_core->urlp->get_path(2))) ? $this->system_core->urlp->get_path(2) : 'local';
      if (isset($this->navigation_subsections_array[$subpage_name])) {
        $this->navigation_subsections_array[$subpage_name]['isActive'] = true;
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

              $template_metadata_title = isset($template_data['metadata']['title']) ? $template_data['metadata']['title'] : 'Anonymous Template';
              $template_metadata_description = isset($template_data['metadata']['description']) ? $template_data['metadata']['description'] : 'Without description.';
              $template_metadata_datetime_created_unix = isset($template_data['metadata']['createdUnixTimestamp']) ? $template_data['metadata']['createdUnixTimestamp'] : 0;
              $template_metadata_author_name = isset($template_data['metadata']['authorName']) ? $template_data['metadata']['authorName'] : 'Anonymous';
              $template_metadata_category_name = isset($template_data['metadata']['categoryName']) ? $template_data['metadata']['categoryName'] : 'default';

              array_push($templates_list_items_transformed_array, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/templates/listItem.tpl', [
                'TEMPLATE_NAME' => $template_name,
                'TEMPLATE_TITLE' => $template_metadata_title,
                'TEMPLATE_DESCRIPTION' => $parsedown->text($template_metadata_description),
                'TEMPLATE_CREATED_TIMESTAMP' => date('d.m.Y', $template_metadata_datetime_created_unix),
                'TEMPLATE_AUTHOR_NAME' => $template_metadata_author_name,
                'TEMPLATE_LINK' => sprintf('/admin/templates/repository/%s', $template->get_name()),
                'TEMPLATE_PREVIEW_URL' => $template_data['preview'],
                'TEMPLATE_INSTALLED_STATUS' => $template_installed_status,
                'TEMPLATE_CATEGORY_NAME' => $template_metadata_category_name
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
                'TEMPLATE_LINK' => sprintf('/admin/template/%s', $template->get_name()),
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
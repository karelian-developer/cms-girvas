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
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageTemplate implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/template.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $navigations_items_transformed = [];
      array_push($navigations_items_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/item.tpl', [
        'NAVIGATION_ITEM_TITLE' => sprintf('< %s', $locale_data['PAGE_TEMPLATE_NAVIGATION_BACK_LABEL']),
        'NAVIGATION_ITEM_URL' => ($this->system_core->urlp->get_path(2) == 'repository') ? '/admin/templates/repository' : '/admin/templates',
        'NAVIGATION_ITEM_LINK_CLASS_IS_ACTIVE' => ''
      ]));

      if (!empty($navigations_items_transformed)) {
        $page_navigation_transformed = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal.tpl', [
          'NAVIGATION_LIST' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/navigationHorizontal/list.tpl', [
            'NAVIGATION_ITEMS' => implode($navigations_items_transformed)
          ])
        ]);
      } else {
        $page_navigation_transformed = '';
      }

      $template_name = ($this->system_core->urlp->get_path(2) == 'repository') ? $this->system_core->urlp->get_path(3) : $this->system_core->urlp->get_path(2);
      $template = new Template($this->system_core, $template_name);
      $template_screenshots_list_items = [];
      $template_metadata_items_transformed = [];

      $template_exists = false;

      if ($this->system_core->urlp->get_path(2) == 'repository') {
        $template_repository_url = sprintf('https://repository.cms-girvas.ru/templates/%s', $template_name);
        $ch = curl_init($template_repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curl_exucute_result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $template_data = $curl_exucute_result['outputData'];
        if (isset($template_data['metadata'])) {
          $template_exists = true;
        }

        if ($template_exists) {
          $parsedown = new Parsedown();

          $template_metadata = $template_data['metadata'];
          $template_title = $template_metadata['title'];
          $template_description = file_get_contents($template_data['readme_url']);
          $template_description = (!empty($template_description)) ? $parsedown->text($template_description) : $locale_data['DEFAULT_TEXT_DESCRIPTION_NOT_FOUND'];

          if (count($template_data['previews']) > 0) {
            foreach ($template_data['previews'] as $screenshot_url) {
              array_push($template_screenshots_list_items, TemplateCollector::assembly('<li class="gallery__item"><img class="gallery__item-image" src="{TEMPLATE_SCREENSHOT_URL}"></li>', [
                'TEMPLATE_SCREENSHOT_URL' => $screenshot_url
              ]));
            }
          }
        }
      } else {
        if ($template->exists_file_metadata_json()) {
          $template_exists = true;
        }

        if ($template_exists) {
          $parsedown = new Parsedown();

          $template_metadata = $template->get_metadata();
          $template_title = $template->get_title();
          $template_description = $template->get_content_file_readme_md();
          $template_description = $parsedown->text($template_description);

          $template_screenshots_files_array = $template->get_screenshots_array();
          if (count($template_screenshots_files_array) > 0) {
            $template_screenshots_url = $template->get_screenshots_url();
            foreach ($template_screenshots_files_array as $screenshot_file) {
              array_push($template_screenshots_list_items, TemplateCollector::assembly('<li class="gallery__item"><img class="gallery__item-image" src="{TEMPLATE_SCREENSHOT_URL}"></li>', [
                'TEMPLATE_SCREENSHOT_URL' => sprintf('%s/%s', $template_screenshots_url, $screenshot_file)
              ]));
            }
          }
        }
      }

      if ($template_exists) {
        $allowed_metadata = ['authorName'];

        foreach ($template_metadata as $metadata_name => $metadata_value) {
          if (in_array($metadata_name, $allowed_metadata)) {
            $metadata_title = $metadata_name;
            switch ($metadata_name) {
              case 'authorName': $metadata_title = $locale_data['PAGE_TEMPLATE_AUTHOR_LABEL']; break;
            }

            array_push($template_metadata_items_transformed, TemplateCollector::assembly('<li class="template__metadata-item"><b>{METADATA_TITLE}:</b> {METADATA_VALUE}</li>', [
              'METADATA_TITLE' => $metadata_title,
              'METADATA_VALUE' => $metadata_value
            ]));
          }
        }

        if (count($template_screenshots_list_items) > 0) {
          $template_gallery_list = TemplateCollector::assembly('<ul class="gallery__list list-reset">{TEMPLATE_GALLARY_LIST_ITEMS}</ul>', [
            'TEMPLATE_GALLARY_LIST_ITEMS' => implode($template_screenshots_list_items)
          ]);
        } else {
          $template_gallery_list = '';
        }

        if (count($template_metadata_items_transformed) > 0) {
          $template_metadata_list_transformed = TemplateCollector::assembly('<ul class="template__metadata-list list-reset">{METADATA_LIST}</ul>', [
            'METADATA_LIST' => implode($template_metadata_items_transformed)
          ]);
        } else {
          $template_metadata_list_transformed = $locale_data['PAGE_TEMPLATE_METADATA_BLOCK_METADATA_NOT_FOUND_TITLE'];
        }

        $parsedown = new Parsedown();

        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/template.tpl', [
          'PAGE_NAVIGATION' => $page_navigation_transformed,
          'ADMIN_PANEL_PAGE_NAME' => 'template',
          'TEMPLATE_NAME' => $template_name,
          'TEMPLATE_TITLE' => $template_title,
          'TEMPLATE_DESCRIPTION' => $template_description,
          'TEMPLATE_GALLARY_LIST' => $template_gallery_list,
          'TEMPLATE_METADATA_LIST' => $template_metadata_list_transformed,
          'TEMPLATE_DOWNLOADED_STATUS' => ($template->exists_file_metadata_json()) ? 'downloaded' : 'not-downloaded',
          'TEMPLATE_INSTALLED_STATUS' => ($template->get_name() == $this->system_core->configurator->get_database_entry_value('base_template')) ? 'installed' : 'not-installed'
        ]);
      } else {
        http_response_code(404);

        $page_error = new PageError($this->system_core, $this->page, 404);
        $page_error->assembly();
        $this->assembled = $page_error->assembled;
      }
    }
  }
}

?>
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
  use \core\PHPLibrary\Template\EnumMetadata as TemplateEnumMetadata;
  use \core\PHPLibrary\Template\EnumWeight as TemplateEnumWeight;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;

  class PageTemplate implements InterfacePage {
    /** @property SystemCore Объект системного ядра*/
    public SystemCore $system_core;

    /** @property Page Объект страницы */
    public Page $page;

    /** @property array Массив разрешенных типов метаданных */
    public array $allowed_metadata = [
      TemplateEnumMetadata::AUTHOR_NAME,
      TemplateEnumMetadata::AUTHOR_CODE_NAME,
      TemplateEnumMetadata::AUTHOR_CODE_SERVER_NAME,
      TemplateEnumMetadata::AUTHOR_CODE_CLIENT_NAME,
      TemplateEnumMetadata::AUTHOR_DESIGNER_NAME,
      TemplateEnumMetadata::AUTHOR_LAYOUT_NAME,
      TemplateEnumMetadata::AUTHOR_SITE_LINK,
      TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK,
      TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK,
      TemplateEnumMetadata::CATEGORY_NAME,
      TemplateEnumMetadata::WEIGHT,
      TemplateEnumMetadata::DATETIME_CREATED_UNIX,
      TemplateEnumMetadata::DATETIME_UPDATED_UNIX,
      TemplateEnumMetadata::VERSION
    ];
    
    /** @property string Итоговая сборка шаблона в виде строки */
    public string $assembled = '';

    /**
     * __construct
     * 
     * @return void
     */
    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }


    /**
     * Сборка шаблона
     * 
     * @return void
     */
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

          if (count($template_data['screenshots']) > 0) {
            foreach ($template_data['screenshots'] as $screenshot_url) {
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
        foreach ($this->allowed_metadata as $enum_metadata) {
          /** @var string Имя ячейки метаданных */
          $metadata_name = Template::get_metadata_name($enum_metadata);

          if (array_key_exists($metadata_name, $template_metadata) || $enum_metadata === TemplateEnumMetadata::WEIGHT) {
            $get_metadata_value = function (Template $template, array $template_metadata, TemplateEnumMetadata $enum_metadata) {
              $metadata_name = Template::get_metadata_name($enum_metadata);
              
              if ($enum_metadata === TemplateEnumMetadata::WEIGHT && $this->system_core->urlp->get_path(2) != 'repository') {
                $template_weight = Template::get_weight($template, TemplateEnumWeight::BYTES); 
                
                if ($template_weight < 1024) {
                  return sprintf('%s B', $template_weight);
                }
                
                if ($template_weight >= 1024 && $template_weight < 1024 ^ 2) {
                  return sprintf('%s KB', round($template_weight / 1024, 2));
                }

                if ($template_weight >= 1024 ^ 2 && $template_weight < 1024 ^ 3) {
                  return sprintf('%s MB', round($template_weight / (1024 ^ 2), 2));
                }

                if ($template_weight >= 1024 ^ 3) {
                  return sprintf('%s GB', round($template_weight / (1024 ^ 3), 2));
                }
              }

              return isset($template_metadata[$metadata_name]) ? $template_metadata[$metadata_name] : '[???]';
            };

            /** @var string Заголовок ячейки метаданных */
            $metadata_title = match ($enum_metadata) {
              TemplateEnumMetadata::AUTHOR_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_CODE_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_CODE_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_CODE_SERVER_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_CODE_SERVER_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_CODE_CLIENT_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_CODE_CLIENT_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_DESIGNER_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_DESIGNER_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_LAYOUT_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_LAYOUT_NAME_LABEL'),
              TemplateEnumMetadata::AUTHOR_SITE_LINK => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_SITE_LINK_LABEL'),
              TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_SOCIAL_VK_LINK_LABEL'),
              TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_AUTHOR_SOCIAL_OK_LINK_LABEL'),
              TemplateEnumMetadata::CATEGORY_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_CATEGORY_NAME_LABEL'),
              TemplateEnumMetadata::WEIGHT => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_SIZE_LABEL'),
              TemplateEnumMetadata::DATETIME_CREATED_UNIX => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_DATETIME_CREATED_UNIX_LABEL'),
              TemplateEnumMetadata::DATETIME_UPDATED_UNIX => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_DATETIME_UPDATED_UNIX_LABEL'),
              TemplateEnumMetadata::VERSION => $template->system_core->locale::get_data_value($locale_data, 'PAGE_TEMPLATE_VERSION_LABEL')
            };

            switch ($enum_metadata) {
              case TemplateEnumMetadata::AUTHOR_SITE_LINK: $metadata_value_template = '<li class="template__metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              case TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK: $metadata_value_template = '<li class="template__metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              case TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK: $metadata_value_template = '<li class="template__metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              default: $metadata_value_template = '<li class="template__metadata-item"><b>{METADATA_TITLE}:</b> {METADATA_VALUE}</li>';
            }

            array_push($template_metadata_items_transformed, TemplateCollector::assembly($metadata_value_template, [
              'METADATA_TITLE' => $metadata_title,
              'METADATA_VALUE' => $get_metadata_value($template, $template_metadata, $enum_metadata)
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
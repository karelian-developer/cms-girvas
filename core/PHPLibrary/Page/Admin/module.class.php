<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \DOMDocument as DOMDocument;
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Module as Module;
  use \core\PHPLibrary\Module\EnumMetadata as ModuleEnumMetadata;
  use \core\PHPLibrary\Module\EnumWeight as ModuleEnumWeight;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageModule implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_MODULE_NAVIGATION_%s_LABEL';

    /** @property SystemCore Объект системного ядра*/
    public SystemCore $system_core;

    /** @property Page Объект страницы */
    public Page $page;

    /** @property array Массив разрешенных типов метаданных */
    public array $allowed_metadata = [
      ModuleEnumMetadata::AUTHOR_NAME,
      ModuleEnumMetadata::AUTHOR_CODE_NAME,
      ModuleEnumMetadata::AUTHOR_CODE_SERVER_NAME,
      ModuleEnumMetadata::AUTHOR_CODE_CLIENT_NAME,
      ModuleEnumMetadata::AUTHOR_DESIGNER_NAME,
      ModuleEnumMetadata::AUTHOR_LAYOUT_NAME,
      ModuleEnumMetadata::AUTHOR_SITE_LINK,
      ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK,
      ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK,
      ModuleEnumMetadata::CATEGORY_NAME,
      ModuleEnumMetadata::WEIGHT,
      ModuleEnumMetadata::DATETIME_CREATED_UNIX,
      ModuleEnumMetadata::DATETIME_UPDATED_UNIX,
      ModuleEnumMetadata::VERSION
    ];

    /** @property string Итоговая сборка шаблона в виде строки */
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/modules',
        'permanent' => true,
        'isActive' => false
      ],
    ];

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
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    /**
     * Сборка шаблона
     * 
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/module.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $module_name = ($this->system_core->urlp->get_path(2) == 'repository') ? $this->system_core->urlp->get_path(3) : $this->system_core->urlp->get_path(2);
      $module = new Module($this->system_core, $module_name);
      $module_screenshots_list_items = [];
      $module_metadata_items_transformed = [];

      $module_exists = false;
      if ($this->system_core->urlp->get_path(2) == 'repository') {
        $module_repository_url = sprintf('https://repository.cms-girvas.ru/modules/%s', $module_name);
        $ch = curl_init($module_repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curl_exucute_result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $module_data = $curl_exucute_result['outputData'];
        if (isset($module_data['metadata'])) {
          $module_exists = true;
        }

        if ($module_exists) {
          $parsedown = new Parsedown();

          $module_metadata = $module_data['metadata'];
          $module_title = $module_metadata['title'];
          $module_description = file_get_contents($module_data['readme_url']);
          $module_description = $parsedown->text($module_description);

          if (count($module_data['screenshots']) > 0) {
            foreach ($module_data['screenshots'] as $screenshot_url) {
              array_push($module_screenshots_list_items, TemplateCollector::assembly('<li class="gallery__item"><img class="gallery__item-image" src="{MODULE_SCREENSHOT_URL}"></li>', [
                'MODULE_SCREENSHOT_URL' => $screenshot_url
              ]));
            }
          }
        }
      } else {
        if ($module->exists_core_file()) {
          if ($module->exists_file_metadata_json()) {
            $module_exists = true;
          }
        }

        if ($module_exists) {
          $parsedown = new Parsedown();

          $module_metadata = $module->get_metadata();
          $module_title = $module->get_title();
          $module_description = $module->get_content_file_readme_md();
          $module_description = (!empty($module_description)) ? $parsedown->text($module_description) : $locale_data['DEFAULT_TEXT_DESCRIPTION_NOT_FOUND'];

          $module_screenshots_files_array = $module->get_screenshots_array();
          if (count($module_screenshots_files_array) > 0) {
            $module_screenshots_url = $template->get_screenshots_url();
            foreach ($module_screenshots_files_array as $screenshot_file) {
              array_push($module_screenshots_list_items, TemplateCollector::assembly('<li class="gallery__item"><img class="gallery__item-image" src="{MODULE_SCREENSHOT_URL}"></li>', [
                'MODULE_SCREENSHOT_URL' => sprintf('%s/%s', $module_screenshots_url, $screenshot_file)
              ]));
            }
          }
        }
      }

      if ($module_exists) {
        foreach ($this->allowed_metadata as $enum_metadata) {
          /** @var string Имя ячейки метаданных */
          $metadata_name = Module::get_metadata_name($enum_metadata);

          if (array_key_exists($metadata_name, $module_metadata) || $enum_metadata === ModuleEnumMetadata::WEIGHT) {
            $get_metadata_value = function (Module $module, array $module_metadata, ModuleEnumMetadata $enum_metadata) {
              $metadata_name = Module::get_metadata_name($enum_metadata);
              
              if ($enum_metadata === ModuleEnumMetadata::WEIGHT) {
                $module_weight = ($this->system_core->urlp->get_path(2) != 'repository') ? Module::get_weight($module, ModuleEnumWeight::BYTES) : $module_metadata[$metadata_name];

                if ($module_weight < 1024) {
                  return sprintf('%s B', $module_weight);
                }
                
                if ($module_weight >= 1024 && $module_weight < 1024 ^ 2) {
                  return sprintf('%s KB', round($module_weight / 1024, 2));
                }

                if ($module_weight >= 1024 ^ 2 && $module_weight < 1024 ^ 3) {
                  return sprintf('%s MB', round($module_weight / (1024 ^ 2), 2));
                }

                if ($module_weight >= 1024 ^ 3) {
                  return sprintf('%s GB', round($module_weight / (1024 ^ 3), 2));
                }
              }

              if ($enum_metadata === ModuleEnumMetadata::DATETIME_CREATED_UNIX || $enum_metadata === ModuleEnumMetadata::DATETIME_UPDATED_UNIX) {
                return date('d.m.Y', $module_metadata[$metadata_name]);
              }

              return isset($module_metadata[$metadata_name]) ? $module_metadata[$metadata_name] : '[???]';
            };

            /** @var string Заголовок ячейки метаданных */
            $metadata_title = match ($enum_metadata) {
              ModuleEnumMetadata::AUTHOR_NAME => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_CODE_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_CODE_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_CODE_SERVER_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_CODE_SERVER_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_CODE_CLIENT_NAME => $template->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_CODE_CLIENT_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_DESIGNER_NAME => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_DESIGNER_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_LAYOUT_NAME => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_LAYOUT_NAME_LABEL'),
              ModuleEnumMetadata::AUTHOR_SITE_LINK => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_SITE_LINK_LABEL'),
              ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_SOCIAL_VK_LINK_LABEL'),
              ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_AUTHOR_SOCIAL_OK_LINK_LABEL'),
              ModuleEnumMetadata::CATEGORY_NAME => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_CATEGORY_NAME_LABEL'),
              ModuleEnumMetadata::WEIGHT => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_SIZE_LABEL'),
              ModuleEnumMetadata::DATETIME_CREATED_UNIX => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_DATETIME_CREATED_UNIX_LABEL'),
              ModuleEnumMetadata::DATETIME_UPDATED_UNIX => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_DATETIME_UPDATED_UNIX_LABEL'),
              ModuleEnumMetadata::VERSION => $module->system_core->locale::get_data_value($locale_data, 'PAGE_MODULE_VERSION_LABEL')
            };

            switch ($enum_metadata) {
              case ModuleEnumMetadata::AUTHOR_SITE_LINK: $metadata_value_template = '<li class="module__metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              case ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK: $metadata_value_template = '<li class="module__metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              case ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK: $metadata_value_template = '<li class="module__metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>'; break;
              default: $metadata_value_template = '<li class="module__metadata-item"><b>{METADATA_TITLE}:</b> {METADATA_VALUE}</li>';
            }

            array_push($module_metadata_items_transformed, TemplateCollector::assembly($metadata_value_template, [
              'METADATA_TITLE' => $metadata_title,
              'METADATA_VALUE' => $get_metadata_value($module, $module_metadata, $enum_metadata)
            ]));
          }
        }

        if (count($module_screenshots_list_items) > 0) {
          $module_gallery_list = TemplateCollector::assembly('<ul class="gallery__list list-reset">{MODULE_GALLARY_LIST_ITEMS}</ul>', [
            'MODULE_GALLARY_LIST_ITEMS' => implode($module_screenshots_list_items)
          ]);
        } else {
          $module_gallery_list = '';
        }

        if (count($module_metadata_items_transformed) > 0) {
          $metadata_list_transformed = TemplateCollector::assembly('<ul class="module__metadata-list list-reset">{METADATA_LIST}</ul>', [
            'METADATA_LIST' => implode($module_metadata_items_transformed)
          ]);
        } else {
          $metadata_list_transformed = $locale_data['PAGE_MODULE_METADATA_BLOCK_METADATA_NOT_FOUND_TITLE'];
        }

        $parsedown = new Parsedown();

        $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/module.tpl', [
          'ADMIN_PANEL_PAGE_NAME' => 'module',
          'MODULE_NAME' => $module_name,
          'MODULE_TITLE' => $module_title,
          'MODULE_DESCRIPTION' => $parsedown->text($module_description),
          'MODULE_GALLARY_LIST' => $module_gallery_list,
          'MODULE_METADATA_LIST' => $metadata_list_transformed,
          'MODULE_ENABLED_STATUS' => ($module->is_enabled()) ? 'enabled' : 'disabled',
          'MODULE_INSTALLED_STATUS' => ($module->is_installed()) ? 'installed' : 'not-installed'
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
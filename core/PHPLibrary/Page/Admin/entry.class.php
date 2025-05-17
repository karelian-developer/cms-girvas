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
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \DOMDocument as DOMDocument;

  class PageEntry implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRY_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/entries',
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
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_style(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $entry = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $entry_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $entry = (Entry::exists_by_id($this->system_core, $entry_id)) ? new Entry($this->system_core, $entry_id) : null;
        
        if (!is_null($entry)) {
          $entry->init_data(['id', 'texts', 'name', 'metadata']);
        }
      }

      /** ===================
       *  Дополнительные поля
       *  ===================
       */

      /** @var array Типы полей */
      $fields_types = ($this->system_core->configurator->exists_database_entry_value('entries_additional_field_type')) ? json_decode($this->system_core->configurator->get_database_entry_value('entries_additional_field_type'), true) : [];
      /** @var array Категории полей */
      $fields_categories_ids = ($this->system_core->configurator->exists_database_entry_value('entries_additional_field_category_id')) ? json_decode($this->system_core->configurator->get_database_entry_value('entries_additional_field_category_id'), true) : [];
      /** @var array Заголовки полей */
      $fields_titles = ($this->system_core->configurator->exists_database_entry_value('entries_additional_field_title')) ? json_decode($this->system_core->configurator->get_database_entry_value('entries_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fields_descriptions = ($this->system_core->configurator->exists_database_entry_value('entries_additional_field_description')) ? json_decode($this->system_core->configurator->get_database_entry_value('entries_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fields_names = ($this->system_core->configurator->exists_database_entry_value('entries_additional_field_name')) ? json_decode($this->system_core->configurator->get_database_entry_value('entries_additional_field_name'), true) : [];
      /** @var string Имя языкового базового пакета CMS */
      $cms_locale_setted = $this->system_core->configurator->get_database_entry_value('base_locale');

      $additional_fields_elements = [];
      foreach ($fields_types as $field_index => $field_type) {
        $field_name_exploded = explode('_', $fields_names[$field_index]);

        foreach ($field_name_exploded as $string_index => $string) {
          if ($string_index > 0) {
            $field_name_exploded[$string_index] = ucfirst($string);
          }
        }

        $field_name_transformed = implode($field_name_exploded);

        if ($field_type == 'textarea') {
          if (!is_null($entry)) {
            $field_value = (!is_null($entry->get_additional_field_data($fields_names[$field_index]))) ? $entry->get_additional_field_data($fields_names[$field_index]) : '';
          }

          /** @var DOMDocument */
          $dom_document = new DOMDocument('1.0', 'UTF-8');

          $element_value = (isset($field_value)) ? $field_value : '';
          $element = $dom_document->createElement('textarea', $element_value);
          $element->setAttribute('name', sprintf('entry_additional_field_%s', $fields_names[$field_index]));

          $dom_document->appendChild($element);

          $dom_document_string = $dom_document->saveHTML($element);

          array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fields_descriptions[$cms_locale_setted][$field_index],
            'FIELD_TITLE' => $fields_titles[$cms_locale_setted][$field_index],
            'FIELD_INPUT' => $dom_document_string
          ]));

        } else {
          if (!is_null($entry)) {
            $field_value = (!is_null($entry->get_additional_field_data($fields_names[$field_index]))) ? $entry->get_additional_field_data($fields_names[$field_index]) : '';
          }

          /** @var DOMDocument */
          $dom_document = new DOMDocument('1.0', 'UTF-8');

          $element_value = (isset($field_value)) ? $field_value : '';
          $element = $dom_document->createElement('input');
          $element->setAttribute('name', sprintf('entry_additional_field_%s', $fields_names[$field_index]));
          $element->setAttribute('type', $fields_types[$field_index]);
          $element->setAttribute('data-category-id', $fields_categories_ids[$field_index]);
          $element->setAttribute('value', $element_value);

          $dom_document->appendChild($element);

          $dom_document_string = $dom_document->saveHTML($element);

          array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fields_descriptions[$cms_locale_setted][$field_index],
            'FIELD_TITLE' => $fields_titles[$cms_locale_setted][$field_index],
            'FIELD_INPUT' => $dom_document_string
          ]));
        }
      }

      $media_files_path = sprintf('%s/uploads/media', $this->system_core->get_cms_path());
      $media_files = array_diff(scandir($media_files_path), ['.', '..']);
      $media_files = array_slice($media_files, 0, 6);

      $media_files_transformed = [];
      foreach ($media_files as $media_file) {
        $media_file_url = sprintf('/uploads/media/%s', $media_file);
        array_push($media_files_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/mediaManager/listItem.tpl', [
          'MEDIA_FILE_URL' => $media_file_url,
          'MEDIA_FILE_FULLNAME' => $media_file
        ]));
      }

      if (!empty($media_files_transformed)) {
        $media_manager_list = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/mediaManager/list.tpl', [
          'MEDIA_LIST_ITEMS' => implode($media_files_transformed)
        ]);
      } else {
        $media_manager_list = $locale_data['PAGE_ENTRY_MEDIA_FILES_NOT_FOUND_LABEL'];
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entry',
        'ENTRY_EDITOR' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/editor.tpl', []),
        'ENTRY_ID' => (!is_null($entry)) ? $entry->get_id() : 0,
        'ENTRY_TITLE' => (!is_null($entry)) ? $entry->get_title() : '',
        'ENTRY_DESCRIPTION' => (!is_null($entry)) ? $entry->get_description() : '',
        'ENTRY_CONTENT' => (!is_null($entry)) ? $entry->get_content() : '',
        'ENTRY_KEYWORDS' => (!is_null($entry)) ? implode(', ', $entry->get_keywords()) : '',
        'ENTRY_NAME' => (!is_null($entry)) ? $entry->get_name() : '',
        'ENTRY_ADDITIONAL_FIELDS' => implode($additional_fields_elements),
        'ENTRY_FORM_METHOD' => (!is_null($entry)) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
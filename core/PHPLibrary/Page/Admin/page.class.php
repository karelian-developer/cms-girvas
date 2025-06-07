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
  use \core\PHPLibrary\PageStatic as PageStatic;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \DOMDocument as DOMDocument;

  class PagePage implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_STATIC_PAGE_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/pages',
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
      $this->system_core->template->add_style(['href' => 'styles/page/pageStatic.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_style(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      $page_static = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        $page_static_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        $page_static = (PageStatic::exists_by_id($this->system_core, $page_static_id)) ? new PageStatic($this->system_core, $page_static_id) : null;
        
        if (!is_null($page_static)) {
          $page_static->init_data(['id', 'texts', 'metadata', 'name']);
        }
      }

      /** ===================
       *  Дополнительные поля
       *  ===================
       */

      /** @var array Типы полей */
      $fields_types = ($this->system_core->configurator->exists_database_entry_value('static_pages_additional_field_type')) ? json_decode($this->system_core->configurator->get_database_entry_value('static_pages_additional_field_type'), true) : [];
      /** @var array Заголовки полей */
      $fields_titles = ($this->system_core->configurator->exists_database_entry_value('static_pages_additional_field_title')) ? json_decode($this->system_core->configurator->get_database_entry_value('static_pages_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fields_descriptions = ($this->system_core->configurator->exists_database_entry_value('static_pages_additional_field_description')) ? json_decode($this->system_core->configurator->get_database_entry_value('static_pages_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fields_names = ($this->system_core->configurator->exists_database_entry_value('static_pages_additional_field_name')) ? json_decode($this->system_core->configurator->get_database_entry_value('static_pages_additional_field_name'), true) : [];
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
            $field_value = (!is_null($page_static->get_additional_field_data($fields_names[$field_index]))) ? $page_static->get_additional_field_data($fields_names[$field_index]) : '';
          }

          /** @var DOMDocument */
          $dom_document = new DOMDocument('1.0', 'UTF-8');

          $element_value = (isset($field_value)) ? $field_value : '';
          $element = $dom_document->createElement('textarea', $element_value);
          $element->setAttribute('name', sprintf('page_static_additional_field_%s', $fields_names[$field_index]));

          $dom_document->appendChild($element);

          $dom_document_string = $dom_document->saveHTML($element);

          array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fields_descriptions[$cms_locale_setted][$field_index],
            'FIELD_TITLE' => $fields_titles[$cms_locale_setted][$field_index],
            'FIELD_INPUT' => $dom_document_string
          ]));

        } else {
          if (!is_null($page_static)) {
            $field_value = (!is_null($page_static->get_additional_field_data($fields_names[$field_index]))) ? $page_static->get_additional_field_data($fields_names[$field_index]) : '';
          }

          /** @var DOMDocument */
          $dom_document = new DOMDocument('1.0', 'UTF-8');

          $element_value = (isset($field_value)) ? $field_value : '';
          $element = $dom_document->createElement('input');
          $element->setAttribute('name', sprintf('page_static_additional_field_%s', $fields_names[$field_index]));
          $element->setAttribute('type', $fields_types[$field_index]);
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

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/pageStatic.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'page-static',
        'PAGE_STATIC_EDITOR' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/pageStatic/editor.tpl', []),
        'PAGE_STATIC_ID' => (!is_null($page_static)) ? $page_static->get_id() : 0,
        'PAGE_STATIC_TITLE' => (!is_null($page_static)) ? $page_static->get_title() : '',
        'PAGE_STATIC_DESCRIPTION' => (!is_null($page_static)) ? $page_static->get_description() : '',
        'PAGE_STATIC_CONTENT' => (!is_null($page_static)) ? $page_static->get_content() : '',
        'PAGE_STATIC_KEYWORDS' => (!is_null($page_static)) ? implode(', ', $page_static->get_keywords()) : '',
        'PAGE_STATIC_NAME' => (!is_null($page_static)) ? $page_static->get_name() : '',
        'PAGE_STATIC_ADDITIONAL_FIELDS' => implode($additional_fields_elements),
        'PAGE_STATIC_PERSONAL_TEMPLATE_PATH' => (!is_null($page_static)) ? $page_static->get_personal_template_path() : '',
        'PAGE_STATIC_FORM_METHOD' => (!is_null($page_static)) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
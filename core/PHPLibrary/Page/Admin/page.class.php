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

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/pages',
        'permanent' => true,
        'isActive' => false
      ],
    ];

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/pageStatic.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $pageStatic = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        $pageStaticID = is_numeric($this->CMSCore->urlp->get_path(2)) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        $pageStatic = PageStatic::exists_by_id($this->CMSCore, $pageStaticID) ? new PageStatic($this->CMSCore, $pageStaticID) : null;
        
        if (!is_null($pageStatic)) {
          $pageStatic->init_data(['id', 'texts', 'metadata', 'name']);
        }
      }

      /** ===================
       *  Дополнительные поля
       *  ===================
       */

      /** @var array Типы полей */
      $fieldsTypes = $this->CMSCore->configurator->exists_database_entry_value('static_pages_additional_field_type') ? json_decode($this->CMSCore->configurator->get_database_entry_value('static_pages_additional_field_type'), true) : [];
      /** @var array Заголовки полей */
      $fieldsTitles = $this->CMSCore->configurator->exists_database_entry_value('static_pages_additional_field_title') ? json_decode($this->CMSCore->configurator->get_database_entry_value('static_pages_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fieldsDescriptions = $this->CMSCore->configurator->exists_database_entry_value('static_pages_additional_field_description') ? json_decode($this->CMSCore->configurator->get_database_entry_value('static_pages_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fieldsNames = $this->CMSCore->configurator->exists_database_entry_value('static_pages_additional_field_name') ? json_decode($this->CMSCore->configurator->get_database_entry_value('static_pages_additional_field_name'), true) : [];

      $additionaFieldsElements = [];
      foreach ($fieldsTypes as $index => $type) {
        $fieldNameExploded = explode('_', $fieldsNames[$index]);

        foreach ($fieldNameExploded as $stringIndex => $string) {
          if ($stringIndex > 0) {
            $fieldNameExploded[$stringIndex] = ucfirst($string);
          }
        }

        $fieldNameTransformed = implode($fieldNameExploded);

        if ($type === 'textarea') {
          if (!is_null($entry)) {
            $fieldValue = !is_null($pageStatic->get_additional_field_data($fieldsNames[$index])) ? $pageStatic->get_additional_field_data($fieldsNames[$index]) : '';
          }

          /** @var DOMDocument */
          $document = new DOMDocument('1.0', 'UTF-8');

          $elementValue = isset($fieldValue) ? $fieldValue : '';
          $element = $document->createElement('textarea', $elementValue);
          $element->setAttribute('name', 'page_static_additional_field_' . $fieldsNames[$index]);

          $document->appendChild($element);

          $documentString = $document->saveHTML($element);

          array_push($additionaFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]));

        } else {
          if (!is_null($pageStatic)) {
            $fieldValue = !is_null($pageStatic->get_additional_field_data($fieldsNames[$index])) ? $pageStatic->get_additional_field_data($fieldsNames[$index]) : '';
          }

          /** @var DOMDocument */
          $document = new DOMDocument('1.0', 'UTF-8');

          $elementValue = (isset($fieldValue)) ? $fieldValue : '';
          $element = $document->createElement('input');
          $element->setAttribute('name', 'page_static_additional_field_' . $fieldsNames[$index]);
          $element->setAttribute('type', $fieldsTypes[$index]);
          $element->setAttribute('value', $elementValue);

          $document->appendChild($element);

          $documentString = $document->saveHTML($element);

          array_push($additionaFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]));
        }
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/pageStatic.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'page-static',
        'PAGE_STATIC_EDITOR' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/pageStatic/editor.tpl', []),
        'PAGE_STATIC_ID' => !is_null($pageStatic) ? $pageStatic->get_id() : 0,
        'PAGE_STATIC_TITLE' => !is_null($pageStatic) ? $pageStatic->get_title($localeName) : '',
        'PAGE_STATIC_DESCRIPTION' => !is_null($pageStatic) ? $pageStatic->get_description($localeName) : '',
        'PAGE_STATIC_CONTENT' => !is_null($pageStatic) ? $pageStatic->get_content($localeName) : '',
        'PAGE_STATIC_KEYWORDS' => !is_null($pageStatic) ? implode(', ', $pageStatic->get_keywords($localeName)) : '',
        'PAGE_STATIC_NAME' => !is_null($pageStatic) ? $pageStatic->get_name() : '',
        'PAGE_STATIC_ADDITIONAL_FIELDS' => implode($additionaFieldsElements),
        'PAGE_STATIC_PERSONAL_TEMPLATE_PATH' => !is_null($pageStatic) ? $pageStatic->get_personal_template_path() : '',
        'PAGE_STATIC_FORM_METHOD' => !is_null($pageStatic) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
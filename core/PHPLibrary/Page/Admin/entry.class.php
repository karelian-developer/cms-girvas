<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PageEntry implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRY_NAVIGATION_%s_LABEL';

<<<<<<< HEAD
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
          $element->setAttribute('data-category-id', $fields_categories_ids[$field_index]);

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
=======
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/entries',
      'permanent' => true,
      'isActive' => false
    ],
  ];
>>>>>>> develop

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $entry = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $entryID = is_numeric($this->CMSCore->urlp->getPath(2))
        ? (int) $this->CMSCore->urlp->getPath(2)
        : 0;
        
      $entry = Entry::existsByID($this->CMSCore, $entryID)
        ? new Entry($this->CMSCore, $entryID)
        : null;
      
      if ($entry !== null) {
        $entry->initData(['id', 'texts', 'name', 'metadata']);
      }
    }

    /** ===================
     *  Дополнительные поля
     *  ===================
     */

    /** @var array Типы полей */
    $fieldsTypes = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_type')
      ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_type'), true)
      : [];
    /** @var array Категории полей */
    $fieldsCategoriesIDs = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_category_id')
      ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_category_id'), true)
      : [];
    /** @var array Заголовки полей */
    $fieldsTitles = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_title')
      ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_title'), true)
      : [];
    /** @var array Описания полей */
    $fieldsDescriptions = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_description')
      ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_description'), true)
      : [];
    /** @var array Имена полей */
    $fieldsNames = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_name')
      ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_name'), true)
      : [];

    $additionalFieldsElements = [];
    foreach ($fieldsTypes as $index => $type) {
      $fieldNameExploded = explode('_', $fieldsNames[$index]);

      foreach ($fieldNameExploded as $stringIndex => $string) {
        if ($stringIndex > 0) {
          $fieldNameExploded[$stringIndex] = ucfirst($string);
        }
      }

      $fieldNameTransformed = implode($fieldNameExploded);

      if ($type === 'textarea') {
        if ($entry !== null) {
          $fieldValue = $entry->getAdditionalFieldData($fieldsNames[$index]) !== null
            ? $entry->getAdditionalFieldData($fieldsNames[$index])
            : '';
        }

        /** @var DOMDocument */
        $document = new DOMDocument('1.0', 'UTF-8');

        $elementValue = isset($fieldValue) ? $fieldValue : '';
        $element = $document->createElement('textarea', $elementValue);
        $element->setAttribute('name', 'entry_additional_field_' . $fieldsNames[$index]);
        $element->setAttribute('data-category-id', $fieldsCategoriesIDs[$index]);

        $document->appendChild($element);

        $documentString = $document->saveHTML($element);

        $additionalFieldsElements[] = ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/entry/form/field.tpl',
          [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]
        );
      } else {
        if ($entry !== null) {
          $fieldValue = $entry->getAdditionalFieldData($fieldsNames[$index]) !== null
            ? $entry->getAdditionalFieldData($fieldsNames[$index])
            : '';
        }

        /** @var DOMDocument */
        $document = new DOMDocument('1.0', 'UTF-8');

        $elementValue = (isset($fieldValue)) ? $fieldValue : '';
        $element = $document->createElement('input');
        $element->setAttribute('name', 'entry_additional_field_' . $fieldsNames[$index]);
        $element->setAttribute('type', $fieldsTypes[$index]);
        $element->setAttribute('data-category-id', $fieldsCategoriesIDs[$index]);
        $element->setAttribute('value', $elementValue);

        $document->appendChild($element);

        $documentString = $document->saveHTML($element);

        $additionalFieldsElements[] = ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/entry/form/field.tpl',
          [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]
        );
      }
    }

    $mediaFilesPath = $this->CMSCore->getCMSPath() . '/uploads/media';
    $mediaFiles = array_diff(scandir($mediaFilesPath), ['.', '..']);
    $mediaFiles = array_slice($mediaFiles, 0, 6);

    $mediaFilesTransformed = [];
    foreach ($mediaFiles as $fileName) {
      $mediaFileURL = '/uploads/media/' . $fileName;
      $mediaFilesTransformed[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme, 'templates/page/entry/mediaManager/listItem.tpl',
        [
          'MEDIA_FILE_URL' => $mediaFileURL,
          'MEDIA_FILE_FULLNAME' => $fileName
        ]
      );
    }

    if (!empty($mediaFilesTransformed)) {
      $mediaManagerList = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme, 'templates/page/entry/mediaManager/list.tpl',
        [
          'MEDIA_LIST_ITEMS' => implode($mediaFilesTransformed)
        ]
      );
    } else {
      $mediaManagerList = $localeData['PAGE_ENTRY_MEDIA_FILES_NOT_FOUND_LABEL'];
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/entry.tpl',
      [
        'ADMIN_PANEL_PAGE_NAME' => 'entry',
        'ENTRY_EDITOR' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/entry/editor.tpl',
          []
        ),
        'ENTRY_ID' => $entry !== null ? $entry->getID() : 0,
        'ENTRY_TITLE' => $entry !== null ? $entry->getTitle($localeName) : '',
        'ENTRY_DESCRIPTION' => $entry !== null ? $entry->getDescription($localeName) : '',
        'ENTRY_CONTENT' => $entry !== null ? $entry->getContent($localeName) : '',
        'ENTRY_KEYWORDS' => $entry !== null ? implode(', ', $entry->getKeywords($localeName)) : '',
        'ENTRY_NAME' => $entry !== null ? $entry->getName() : '',
        'ENTRY_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
        'ENTRY_FORM_METHOD' => $entry !== null ? 'PATCH' : 'PUT'
      ]
    );
  }
}
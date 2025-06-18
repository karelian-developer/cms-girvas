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

    public SystemCore $CMSCore;
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entry.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $entry = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        $entryID = is_numeric($this->CMSCore->urlp->get_path(2)) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        $entry = Entry::exists_by_id($this->CMSCore, $entryID) ? new Entry($this->CMSCore, $entryID) : null;
        
        if (!is_null($entry)) {
          $entry->init_data(['id', 'texts', 'name', 'metadata']);
        }
      }

      /** ===================
       *  Дополнительные поля
       *  ===================
       */

      /** @var array Типы полей */
      $fieldsTypes = ($this->CMSCore->configurator->exists_database_entry_value('entries_additional_field_type')) ? json_decode($this->CMSCore->configurator->get_database_entry_value('entries_additional_field_type'), true) : [];
      /** @var array Категории полей */
      $fieldsCategoriesIDs = ($this->CMSCore->configurator->exists_database_entry_value('entries_additional_field_category_id')) ? json_decode($this->CMSCore->configurator->get_database_entry_value('entries_additional_field_category_id'), true) : [];
      /** @var array Заголовки полей */
      $fieldsTitles = ($this->CMSCore->configurator->exists_database_entry_value('entries_additional_field_title')) ? json_decode($this->CMSCore->configurator->get_database_entry_value('entries_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fieldsDescriptions = ($this->CMSCore->configurator->exists_database_entry_value('entries_additional_field_description')) ? json_decode($this->CMSCore->configurator->get_database_entry_value('entries_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fieldsNames = ($this->CMSCore->configurator->exists_database_entry_value('entries_additional_field_name')) ? json_decode($this->CMSCore->configurator->get_database_entry_value('entries_additional_field_name'), true) : [];

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
          if (!is_null($entry)) {
            $fieldValue = !is_null($entry->get_additional_field_data($fieldsNames[$index])) ? $entry->get_additional_field_data($fieldsNames[$index]) : '';
          }

          /** @var DOMDocument */
          $document = new DOMDocument('1.0', 'UTF-8');

          $elementValue = isset($fieldValue) ? $fieldValue : '';
          $element = $document->createElement('textarea', $elementValue);
          $element->setAttribute('name', 'entry_additional_field_' . $fieldsNames[$index]);
          $element->setAttribute('data-category-id', $fieldsCategoriesIDs[$index]);

          $document->appendChild($element);

          $documentString = $document->saveHTML($element);

          array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]));

        } else {
          if (!is_null($entry)) {
            $fieldValue = !is_null($entry->get_additional_field_data($fieldsNames[$index])) ? $entry->get_additional_field_data($fieldsNames[$index]) : '';
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

          array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]));
        }
      }

      $mediaFilesPath = $this->CMSCore->get_cms_path() . '/uploads/media';
      $mediaFiles = array_diff(scandir($mediaFilesPath), ['.', '..']);
      $mediaFiles = array_slice($mediaFiles, 0, 6);

      $mediaFilesTransformed = [];
      foreach ($mediaFiles as $fileName) {
        $mediaFileURL = '/uploads/media/' . $fileName;
        array_push($mediaFilesTransformed, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/mediaManager/listItem.tpl', [
          'MEDIA_FILE_URL' => $mediaFileURL,
          'MEDIA_FILE_FULLNAME' => $fileName
        ]));
      }

      if (!empty($mediaFilesTransformed)) {
        $mediaManagerList = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/mediaManager/list.tpl', [
          'MEDIA_LIST_ITEMS' => implode($mediaFilesTransformed)
        ]);
      } else {
        $mediaManagerList = $localeData['PAGE_ENTRY_MEDIA_FILES_NOT_FOUND_LABEL'];
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'entry',
        'ENTRY_EDITOR' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entry/editor.tpl', []),
        'ENTRY_ID' => !is_null($entry) ? $entry->get_id() : 0,
        'ENTRY_TITLE' => !is_null($entry) ? $entry->get_title($localeName) : '',
        'ENTRY_DESCRIPTION' => !is_null($entry) ? $entry->get_description($localeName) : '',
        'ENTRY_CONTENT' => !is_null($entry) ? $entry->get_content($localeName) : '',
        'ENTRY_KEYWORDS' => !is_null($entry) ? implode(', ', $entry->get_keywords($localeName)) : '',
        'ENTRY_NAME' => !is_null($entry) ? $entry->get_name() : '',
        'ENTRY_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
        'ENTRY_FORM_METHOD' => !is_null($entry) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>
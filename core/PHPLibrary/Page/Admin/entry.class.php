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
use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PageEntry implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRY_NAVIGATION_%s_LABEL';

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
      $entryID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      $entry = Entry::existsByID($this->CMSCore, $entryID) ? new Entry($this->CMSCore, $entryID) : null;
      
      if ($entry !== null) {
        $entry->initData(['id', 'texts', 'name', 'metadata']);
      }
    }

    /** ===================
     *  Дополнительные поля
     *  ===================
     */

    /** @var array Типы полей */
    $fieldsTypes = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_type') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_type'), true) : [];
    /** @var array Категории полей */
    $fieldsCategoriesIDs = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_category_id') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_category_id'), true) : [];
    /** @var array Заголовки полей */
    $fieldsTitles = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_title') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_title'), true) : [];
    /** @var array Описания полей */
    $fieldsDescriptions = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_description') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_description'), true) : [];
    /** @var array Имена полей */
    $fieldsNames = $this->CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_name') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('entries_additional_field_name'), true) : [];

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
          $fieldValue = $entry->getAdditionalFieldData($fieldsNames[$index]) !== null ? $entry->getAdditionalFieldData($fieldsNames[$index]) : '';
        }

        /** @var DOMDocument */
        $document = new DOMDocument('1.0', 'UTF-8');

        $elementValue = isset($fieldValue) ? $fieldValue : '';
        $element = $document->createElement('textarea', $elementValue);
        $element->setAttribute('name', 'entry_additional_field_' . $fieldsNames[$index]);
        $element->setAttribute('data-category-id', $fieldsCategoriesIDs[$index]);

        $document->appendChild($element);

        $documentString = $document->saveHTML($element);

        array_push($additionalFieldsElements, TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
          'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
          'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
          'FIELD_INPUT' => $documentString
        ]));

      } else {
        if ($entry !== null) {
          $fieldValue = $entry->getAdditionalFieldData($fieldsNames[$index]) !== null ? $entry->getAdditionalFieldData($fieldsNames[$index]) : '';
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

        array_push($additionalFieldsElements, TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
          'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
          'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
          'FIELD_INPUT' => $documentString
        ]));
      }
    }

    $mediaFilesPath = $this->CMSCore->getCMSPath() . '/uploads/media';
    $mediaFiles = array_diff(scandir($mediaFilesPath), ['.', '..']);
    $mediaFiles = array_slice($mediaFiles, 0, 6);

    $mediaFilesTransformed = [];
    foreach ($mediaFiles as $fileName) {
      $mediaFileURL = '/uploads/media/' . $fileName;
      array_push($mediaFilesTransformed, TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/mediaManager/listItem.tpl', [
        'MEDIA_FILE_URL' => $mediaFileURL,
        'MEDIA_FILE_FULLNAME' => $fileName
      ]));
    }

    if (!empty($mediaFilesTransformed)) {
      $mediaManagerList = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/mediaManager/list.tpl', [
        'MEDIA_LIST_ITEMS' => implode($mediaFilesTransformed)
      ]);
    } else {
      $mediaManagerList = $localeData['PAGE_ENTRY_MEDIA_FILES_NOT_FOUND_LABEL'];
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'entry',
      'ENTRY_EDITOR' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/editor.tpl', []),
      'ENTRY_ID' => $entry !== null ? $entry->getID() : 0,
      'ENTRY_TITLE' => $entry !== null ? $entry->getTitle($localeName) : '',
      'ENTRY_DESCRIPTION' => $entry !== null ? $entry->getDescription($localeName) : '',
      'ENTRY_CONTENT' => $entry !== null ? $entry->getContent($localeName) : '',
      'ENTRY_KEYWORDS' => $entry !== null ? implode(', ', $entry->getKeywords($localeName)) : '',
      'ENTRY_NAME' => $entry !== null ? $entry->getName() : '',
      'ENTRY_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
      'ENTRY_FORM_METHOD' => $entry !== null ? 'PATCH' : 'PUT'
    ]);
  }
}
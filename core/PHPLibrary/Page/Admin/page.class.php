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
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PagePage implements InterfacePage
{
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/pageStatic.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $pageStatic = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $pageStaticID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      $pageStatic = PageStatic::existsByID($this->CMSCore, $pageStaticID) ? new PageStatic($this->CMSCore, $pageStaticID) : null;
      
      if ($pageStatic !== null) {
        $pageStatic->initData(['id', 'texts', 'metadata', 'name']);
      }
    }

    /** ===================
     *  Дополнительные поля
     *  ===================
     */

    /** @var array Типы полей */
    $fieldsTypes = $this->CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_type') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_type'), true) : [];
    /** @var array Заголовки полей */
    $fieldsTitles = $this->CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_title') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_title'), true) : [];
    /** @var array Описания полей */
    $fieldsDescriptions = $this->CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_description') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_description'), true) : [];
    /** @var array Имена полей */
    $fieldsNames = $this->CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_name') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('static_pages_additional_field_name'), true) : [];

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
        if ($pageStatic !== null) {
          $fieldValue = $pageStatic->getAdditionalFieldData($fieldsNames[$index]) !== null ? $pageStatic->getAdditionalFieldData($fieldsNames[$index]) : '';
        }

        /** @var DOMDocument */
        $document = new DOMDocument('1.0', 'UTF-8');

        $elementValue = $fieldValue ?? '';
        $element = $document->createElement('textarea', $elementValue);
        $element->setAttribute('name', 'page_static_additional_field_' . $fieldsNames[$index]);

        $document->appendChild($element);

        $documentString = $document->saveHTML($element);

        array_push($additionaFieldsElements, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
          'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
          'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
          'FIELD_INPUT' => $documentString
        ]));

      } else {
        if ($pageStatic !== null) {
          $fieldValue = $pageStatic->getAdditionalFieldData($fieldsNames[$index]) !== null ? $pageStatic->getAdditionalFieldData($fieldsNames[$index]) : '';
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

        array_push($additionaFieldsElements, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entry/form/field.tpl', [
          'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
          'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
          'FIELD_INPUT' => $documentString
        ]));
      }
    }

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/pageStatic.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'page-static',
      'PAGE_STATIC_EDITOR' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/pageStatic/editor.tpl', []),
      'PAGE_STATIC_ID' => $pageStatic !== null ? $pageStatic->getID() : 0,
      'PAGE_STATIC_TITLE' => $pageStatic !== null ? $pageStatic->getTitle($localeName) : '',
      'PAGE_STATIC_DESCRIPTION' => $pageStatic !== null ? $pageStatic->getDescription($localeName) : '',
      'PAGE_STATIC_CONTENT' => $pageStatic !== null ? $pageStatic->getContent($localeName) : '',
      'PAGE_STATIC_KEYWORDS' => $pageStatic !== null ? implode(', ', $pageStatic->geKeywords($localeName)) : '',
      'PAGE_STATIC_NAME' => $pageStatic !== null ? $pageStatic->getName() : '',
      'PAGE_STATIC_ADDITIONAL_FIELDS' => implode($additionaFieldsElements),
      'PAGE_STATIC_PERSONAL_TEMPLATE_PATH' => $pageStatic !== null ? $pageStatic->getPersonalTemplatePath() : '',
      'PAGE_STATIC_FORM_METHOD' => $pageStatic !== null ? 'PATCH' : 'PUT'
    ]);
  }

}
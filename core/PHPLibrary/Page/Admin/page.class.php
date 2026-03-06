<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
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

        $additionalFieldsElements[] = ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/entry/form/field.tpl',
          [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]
        );
      } else {
        if ($pageStatic !== null) {
          $fieldValue = $pageStatic->getAdditionalFieldData($fieldsNames[$index]) !== null
            ? $pageStatic->getAdditionalFieldData($fieldsNames[$index])
            : '';
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

        $additionalFieldsElements[] = ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/entry/form/field.tpl',
          [
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_INPUT' => $documentString
          ]
        );
      }
    }

    $templatesAssembled = [];
    $templatesEditorAssembled = [];
    $templateContent = ThemeCollector::getTemplateFileContent(
      $this->CMSCore->theme,
      'templates/page/pageStatic.tpl'
    );
    $templateEditorContent = ThemeCollector::getTemplateFileContent(
      $this->CMSCore->theme,
      'templates/page/pageStatic/editor.tpl'
    );


    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_ID')) {
      $value = $pageStatic !== null ? $pageStatic->getID() : 0;

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_ID',
        $value
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_TITLE')) {
      $value = $pageStatic !== null ? $pageStatic->getTitle($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_TITLE',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_SEO_TITLE')) {
      $value = $pageStatic !== null ? $pageStatic->getSEOTitle($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_SEO_TITLE',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_DESCRIPTION')) {
      $value = $pageStatic !== null ? $pageStatic->getDescription($localeName) : '';
      
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_DESCRIPTION',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_SEO_DESCRIPTION')) {
      $value = $pageStatic !== null ? $pageStatic->getSEODescription($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_SEO_DESCRIPTION',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateEditorContent, 'PAGE_STATIC_CONTENT')) {
      $value = $pageStatic !== null ? $pageStatic->getContent($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesEditorAssembled,
        'PAGE_STATIC_CONTENT',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_KEYWORDS')) {
      $value = $pageStatic !== null ? $pageStatic->getKeywords($localeName) : [];
      $valueArray = array_map(function($item) {
        return str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        );
      }, $value);
      
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_KEYWORDS',
        $pageStatic !== null ? implode(', ', $valueArray) : ''
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_NAME')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_NAME',
        $pageStatic !== null ? $pageStatic->getName() : ''
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PERSONAL_TEMPLATE_PATH')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_PERSONAL_TEMPLATE_PATH',
        $pageStatic !== null ? $pageStatic->getPersonalTemplatePath() : 'templates/page/static.tpl'
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_ADDITIONAL_FIELDS')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_ADDITIONAL_FIELDS',
        implode($additionalFieldsElements)
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_FORM_METHOD')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_FORM_METHOD',
        $pageStatic !== null ? 'PATCH' : 'PUT'
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'PAGE_STATIC_PUBLISHED_TIMESTAMP')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'PAGE_STATIC_PUBLISHED_TIMESTAMP',
        $pageStatic !== null
          ? date('Y-m-dTH:i', $pageStatic->getPublishedUnixTimestamp())
          : date('Y-m-dTH:i', time())
      );
    }

    $templatesAssembled['ADMIN_PANEL_PAGE_NAME'] = 'page-static';
    $templatesAssembled['PAGE_STATIC_EDITOR'] = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/pageStatic/editor.tpl',
      $templatesEditorAssembled
    );

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/pageStatic.tpl',
      $templatesAssembled
    );
  }

}
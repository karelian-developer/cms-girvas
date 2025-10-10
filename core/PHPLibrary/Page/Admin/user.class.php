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
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PageUser implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USER_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/users',
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/user.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    /** @var null Пустая переменная */
    $user = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      /** @var int Идентификационный номер пользователя */
      $userID = is_numeric($this->CMSCore->urlp->getPath(2)) ? (int) $this->CMSCore->urlp->getPath(2) : 0;
      /** @var User|null Объект пользователя */
      $user = User::existsByID($this->CMSCore, $userID) ? new User($this->CMSCore, $userID) : null;
      
      if ($user !== null) {
        // Инициализация набора данных пользователя
        $user->initData(['*']);
      }
    }

    /** ===================
     *  Дополнительные поля
     *  ===================
     */

    /** @var array Типы полей */
    $fieldsTypes = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_type') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_type'), true) : [];
    /** @var array Заголовки полей */
    $fieldsTitles = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_title') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_title'), true) : [];
    /** @var array Описания полей */
    $fieldsDescriptions = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_description') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_description'), true) : [];
    /** @var array Имена полей */
    $fieldsNames = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_name') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_name'), true) : [];

    $additionalFieldsElements = [];
    foreach ($fieldsTypes as $index => $type) {
      $field_name_exploded = explode('_', $fieldsNames[$index]);

      foreach ($field_name_exploded as $string_index => $string) {
        if ($string_index > 0) {
          $field_name_exploded[$string_index] = ucfirst($string);
        }
      }

      $fieldNameTransformed = implode($field_name_exploded);

      $document = new DOMDocument('1.0');
      $documentFragment = $document->createDocumentFragment();

      $gridTableCellTextElement = $document->createElement('div');
      $gridTableCellDataElement = $document->createElement('div');
      $gridTableCellTextTitleElement = $document->createElement('div');
      $gridTableCellTextDescriptionElement = $document->createElement('div');

      $gridTableCellTextTitleTextNode = $document->createTextNode($fieldsTitles[$localeName][$index]);
      $gridTableCellTextDescriptionTextNode = $document->createTextNode($fieldsDescriptions[$localeName][$index]);

      if ($user !== null) {
        $fieldValue = $user->getAdditionalFieldData($fieldNameTransformed) !== null
          ? $user->getAdditionalFieldData($fieldNameTransformed)
          : '';
      }

      if ($type === 'textarea') {
        $inputElement = $document->createElement('textarea');
        $textNode = $document->createTextNode($fieldValue);
        $inputElement->appendChild($textNode);
      } else {
        $inputElement = $document->createElement('input');
        $inputElement->setAttribute('type', $fieldsTypes[$index]);
        $inputElement->setAttribute('value', $fieldValue);
      }

      $inputElement->setAttribute('name', $fieldsNames[$index]);

      $gridTableCellTextElement->setAttribute('class', 'cell grid-table__cell grid-table__cell_text');
      $gridTableCellDataElement->setAttribute('class', 'cell grid-table__cell grid-table__cell_data');
      $gridTableCellTextTitleElement->setAttribute('class', 'cell__title');
      $gridTableCellTextDescriptionElement->setAttribute('class', 'cell__description');

      $gridTableCellTextTitleElement->appendChild($gridTableCellTextTitleTextNode);
      $gridTableCellTextDescriptionElement->appendChild($gridTableCellTextDescriptionTextNode);
      $gridTableCellTextElement->appendChild($gridTableCellTextTitleElement);
      $gridTableCellTextElement->appendChild($gridTableCellTextDescriptionElement);
      $documentFragment->appendChild($gridTableCellTextElement);
      $documentFragment->appendChild($gridTableCellDataElement);
      $document->appendChild($documentFragment);

      $additionalFieldsElements[] = $document->saveHTML();
    }

    /** @var string Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/user.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'user',
      'USER_ID' => $user !== null ? $user->getID() : 0,
      'USER_LOGIN' => $user !== null ? $user->getLogin() : '',
      'USER_EMAIL' => $user !== null ? $user->getEmail() : '',
      'USER_NAME' => $user !== null ? $user->getName() : '',
      'USER_SURNAME' => $user !== null ? $user->getSurname() : '',
      'USER_PATRONYMIC' => $user !== null ? $user->getPatronymic() : '',
      'USER_BIRTHDATE' => $user !== null ? date('Y-m-d', $user->getBirthdateUnixTimestamp()) : 0,
      'USER_BIRTHDATE_MINIMUM' => date('Y-m-d', time() - 3155760000),
      'USER_BIRTHDATE_MAXIMUM' => date('Y-m-d', time() - 441763200),
      'USER_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
      'USER_FORM_METHOD' => $user !== null ? 'PATCH' : 'PUT'
    ]);
  }
}
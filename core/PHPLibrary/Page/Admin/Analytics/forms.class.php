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

namespace core\PHPLibrary\Page\Admin\Analytics;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Forms as Forms;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;

/**
 * Страница со списком форм
 */
class PageForms implements InterfacePage
{
  use TraitPage;

  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false
    ],
    'forms' => [
      'name' => 'forms',
      'iconName' => 'forms',
      'link' => '/analytics/forms',
      'permanent' => true,
      'isActive' => true
    ]
  ];

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   * @param InterfacePage $page
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public InterfacePage $page
  ) {}

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

  /**
   * Сборка списка локализаций для форм
   * 
   * @param array $localesData
   * 
   * @return string
   */
  private function assemblyLocalesItems(array $localesData) : string
  {
    $document = new DOMDocument('1.0', 'UTF-8');

    foreach ($localesData as $localeData) {
      $itemElement = $document->createElement('li', $localeData['title']);
      $itemElement->setAttribute('class', 'grid-table__locale');

      if (!empty($localeData['iconURL'])) {
        $iconElement = $document->createElement('img');
        $iconElement->setAttribute('class', 'grid-table__locale-icon');
        $iconElement->setAttribute('src', $localeData['iconURL']);
        $itemElement->prepend($iconElement);
      }

      $document->appendChild($itemElement);
    }

    return $document->saveHTML();
  }

  /**
   * Сборка
   * 
   * @return void
   */
  public function assembly() : void
  {
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null
      ? (int) $this->CMSCore->urlp->getParam('pageNumber')
      : 0;
    $paginationItemsOnPage = 12;

    $formsTableItemsAssembled = [];
    $forms = new Forms($this->CMSCore);
    $formsObjects = $forms->getAll([
      'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
    ]);

    $pagination = new Pagination($this->CMSCore, $forms->getCountTotal(), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    unset($forms);

    foreach ($formsObjects as $index => $object) {
      $object->initData(['id', 'texts', 'name', 'elements', 'metadata', 'createdUnixTimestamp', 'updatedUnixTimestamp']);
      $objectID = $object->getID();
      $objectName = $object->getName();

      /** @var string Дата создания в формате d.m.Y H:i:s */
      $createdUnixTimestamp = date('d.m.Y H:i:s', $object->getCreatedUnixTimestamp());
      /** @var string Дата обновления в формате d.m.Y H:i:s */
      $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->getUpdatedUnixTimestamp());

      /** @var string Заголовок */
      $objectTitle = $object->getTitle($localeName);
      $objectTitle = strip_tags($objectTitle);

      /** @var string Описание */
      $objectDescription = $object->getDescription($localeName);
      $objectDescription = strip_tags($objectDescription);
      
      $completedLocalesData = $object->getCompletedLocalesData($this->CMSCore);
      $completedLocalesList = $this->assemblyLocalesItems($completedLocalesData);

      $formMethodID = $object->getMethodID();
      $formMethod = match ($formMethodID) {
        1 => 'GET',
        2 => 'POST',
        3 => 'PUT',
        4 => 'DELETE',
        5 => 'PATCH',
      };

      $formsTableItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/analytics/forms/item.tpl',
        [
          'FORM_INDEX' => $index,
          'FORM_ID' => $objectID,
          'FORM_NAME' => $objectName,
          'FORM_TITLE' => $objectTitle,
          'FORM_DESCRIPTION' => $objectDescription,
          'FORM_METHOD' => $formMethod,
          'FORM_LOCALES_LIST' => $completedLocalesList,
          'FORM_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'FORM_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/analytics/forms.tpl',
      [
        'PAGE_PAGINATION' => $pagination->assembled,
        'PAGE_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/analytics/forms/wrapper.tpl',
          [
            'PAGE_ITEMS' => implode($formsTableItemsAssembled)
          ]
        )
      ]
    );
  }
}
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
use \core\PHPLibrary\Form as Form;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \DOMDocument as DOMDocument;

/**
 * Страница со списком форм
 */
class PageForm implements InterfacePage
{
  use TraitPage;

  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/analytics/forms',
      'permanent' => true,
      'isActive' => false
    ],
  ];

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   * @param InterfacePage $page
   * @param EntityTypeContent $form
   */
  public function __construct(
    public CoreInterface $CMSCore,
    public InterfacePage $page,
    public EntityTypeContent $form
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

    $formsDataItemsAssembled = [];
    $formsElements = $this->form->getElements();
    $formsData = $this->form->getData();

    usort($formsData, function ($a, $b) {
      if ($a['createdUnixTimestamp'] !== $b['createdUnixTimestamp']) {
        return $a['createdUnixTimestamp'] < $b['createdUnixTimestamp'] ? 1 : -1;
      }

      return 0;
    });

    $formsDatas = array_slice($formsData, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);
    $pagination = new Pagination($this->CMSCore, count($formsData), $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    error_log(print_r($formsDatas, true));

    foreach ($formsDatas as $dataIndex => $data) {
      $dataArray = json_decode($data['data'], true);

      $document = new DOMDocument('1.0', 'UTF-8');
      $documentFragment = $document->createDocumentFragment();

      $listElement = $document->createElement('ul');
      $listElement->setAttribute('class', 'list');

      foreach ($formsElements as $elementIndex => $elementData) {
        $elementName = $elementData['name'];
        
        if (isset($dataArray[$elementName])) {
          $elementTitle = isset($elementData['texts'][$localeName]['title'])
            ? $elementData['texts'][$localeName]['title']
            : $elementName;
          
          $itemElement = $document->createElement('li');

          $titleElement =  $document->createElement('span', $elementTitle);
          $dataElement =  $document->createElement('span', $dataArray[$elementName]);

          $titleElement->setAttribute('class', 'list__title');
          $dataElement->setAttribute('class', 'list__data');

          $itemElement->appendChild($titleElement);
          $itemElement->appendChild($dataElement);

          $listElement->appendChild($itemElement);
        }
      }

      $documentFragment->appendChild($listElement);
      $document->appendChild($documentFragment);

      $formsDataItemsAssembled[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/analytics/form/item.tpl',
        [
          'FORM_DATA_ID' => $data['id'],
          'FORM_DATA_INDEX' => $dataIndex,
          'FORM_DATA' => $document->saveHTML(),
          'FORM_DATA_CREATED_DATE_TIMESTAMP' => date('d.m.Y H:i:s', $data['createdUnixTimestamp'])
        ]
      );
    }

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/analytics/form.tpl',
      [
        'PAGE_PAGINATION' => $pagination->assembled,
        'PAGE_TABLE' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/analytics/form/wrapper.tpl',
          [
            'PAGE_ITEMS' => implode($formsDataItemsAssembled)
          ]
        )
      ]
    );
  }
}
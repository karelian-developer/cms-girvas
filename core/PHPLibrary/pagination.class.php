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

namespace core\PHPLibrary;

use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \DOMDocument as DOMDocument;

if (!defined('IS_NOT_HACKED')) {
  die('Unauthorized access attempt detected!');
}

class Pagination
{
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $itemsTotalCount
   * @param int $itemsInPageCount
   * @param int $itemCurrent
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $itemsTotalCount,
    private int $itemsInPageCount,
    private int $itemCurrent = 0,
    private string $startPart = '',
    private bool $pageNumberParamIsFirst = true
  ) {}
  
  /**
   * Получить количество страниц
   *
   * @return int
   */
  public function getPagesCount() : int
  {
    return ceil($this->itemsTotalCount / $this->itemsInPageCount);
  }
  
  /**
   * Сборка шаблона пагинации
   *
   * @return void
   */
  public function assembly() : void
  {
    $paginationItems = [];

    $DOMDocument = new DOMDocument();

    $ulElement = $DOMDocument->createElement('ul');
    $ulElement->setAttribute('class', 'pagination-list list-reset');
    
    if ($this->itemCurrent > 0) {
      for ($itemIndex = 0; $itemIndex < 2; $itemIndex++) {
        $pageNumber = $itemIndex === 0 ? 0 : $this->itemCurrent - 1;

        $liElement = $DOMDocument->createElement('li');
        $liElement->setAttribute('class', 'pagination-list__item item');

        $aElement = $DOMDocument->createElement('a', $itemIndex === 0 ? '&#10094;&#10094;' : '&#10094;');
        $aElement->setAttribute('class', 'pagination-list__item-link item-link');
        $aElement->setAttribute('href', $this->pageNumberParamIsFirst ? '?pageNumber=' . $pageNumber : $this->startPart . '&pageNumber=' . $pageNumber);

        $liElement->appendChild($aElement);
        $ulElement->appendChild($liElement);
      }
    }

    for ($itemIndex = 0; $itemIndex < $this->getPagesCount(); $itemIndex++) {
      if ($itemIndex >= $this->itemCurrent - 2 && $itemIndex <= $this->itemCurrent + 3) {
        $itemClass = $this->itemCurrent === $itemIndex ? 'pagination-list__item pagination-list__item_active' : 'pagination-list__item';
        $pageNumber = $itemIndex + 1;

        $aElement = $DOMDocument->createElement('a', $pageNumber);
        $aElement->setAttribute('class', 'pagination-list__item-link item-link');
        $aElement->setAttribute('href', $this->pageNumberParamIsFirst ? '?pageNumber=' . $itemIndex : $this->startPart . '&pageNumber=' . $itemIndex);

        $liElement = $DOMDocument->createElement('li');
        $liElement->setAttribute('class', $itemClass);
        
        $liElement->appendChild($aElement);
        $ulElement->appendChild($liElement);
      }
    }

    if ($this->itemCurrent < ($this->getPagesCount() - 1)) {
      for ($itemIndex = 0; $itemIndex < 2; $itemIndex++) {
        $pageNumber = $itemIndex === 0 ? $this->itemCurrent + 1 : $this->getPagesCount() - 1;

        $liElement = $DOMDocument->createElement('li');
        $liElement->setAttribute('class', 'pagination-list__item item');

        $aElement = $DOMDocument->createElement('a', $itemIndex === 0 ? '&#10095;' : '&#10095;&#10095;');
        $aElement->setAttribute('class', 'pagination-list__item-link item-link');
        $aElement->setAttribute('href', $this->pageNumberParamIsFirst ? '?pageNumber=' . $pageNumber : $this->startPart . '&pageNumber=' . $pageNumber);

        $liElement->appendChild($aElement);
        $ulElement->appendChild($liElement);
      }
    }

    $DOMDocument->appendChild($ulElement);

    $this->assembled = $DOMDocument->saveHTML();
  }
}
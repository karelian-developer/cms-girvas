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

final class PageBreadcrumbs
{
  private array $array = [];
  public string $assembled = '';

  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {}

  /**
   * Добавить элемент
   * 
   * @param string $title
   * @param string $url
   * 
   * @return bool
   */
  public function add(string $title, string $url) : bool
  {
    $arrayItemsCountStart = count($this->array);
    $arrayItemsCount = array_push($this->array, [
      'title' => $title,
      'url' => $url
    ]);

    return $arrayItemsCount > $arrayItemsCountStart;
  }

  /**
   * Получить массив элементов
   * 
   * @return array
   */
  public function getArray() : array
  {
    return $this->array;
  }

  /**
   * Сборка шаблона "хлебных крошек"
   * 
   * @return void
   */
  public function assembly() : void
  {
    /** @var array Массив преобразованных элементов */
    $breadcrumbsItemsTransformed = [];
    /** @var string Преобразованный массив элементов в TPL-шаблон */
    $breadcrumbsListTransformed = '';

    if (count($this->getArray()) > 0) {
      foreach ($this->getArray() as $index => $data) {
        array_push($breadcrumbsItemsTransformed, TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/breadcrumps/listItem.tpl', [
          'BREADCRUMP_URL' => $data['url'],
          'BREADCRUMP_TITLE' => $data['title'],
          'BREADCRUMP_META_POSITION' => $index + 1
        ]));
      }
    }

    if (count($breadcrumbsItemsTransformed) > 0) {
      $breadcrumbsListTransformed = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/breadcrumps/list.tpl', [
        'BREADCRUMPS_ITEMS' => implode($breadcrumbsItemsTransformed)
      ]);
    }

    $this->assembled = $breadcrumbsListTransformed;
  }
}
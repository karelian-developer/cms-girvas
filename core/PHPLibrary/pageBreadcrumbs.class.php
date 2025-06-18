<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  final class PageBreadcrumbs {
    private SystemCore $CMSCore;
    private array $array = [];
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $CMSCore
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
    }

    /**
     * Добавить элемент
     * 
     * @param string $title
     * @param string $url
     * 
     * @return bool
     */
    public function add(string $title, string $url) : bool {
      $arrayItemsCountStart = count($this->array);
      $arrayItemsCount = array_push($this->array, [
        'title' => $title,
        'url' => $url
      ]);

      if ($arrayItemsCount > $arrayItemsCountStart) {
        return true;
      }

      return false;
    }

    /**
     * Получить массив элементов
     * 
     * @return array
     */
    public function get_array() : array {
      return $this->array;
    }

    /**
     * Сборка шаблона "хлебных крошек"
     * 
     * @return void
     */
    public function assembly() : void {
      /** @var array Массив преобразованных элементов */
      $breadcrumbsItemsTransformed = [];
      /** @var string Преобразованный массив элементов в TPL-шаблон */
      $breadcrumbsListTransformed = '';

      if (count($this->get_array()) > 0) {
        foreach ($this->get_array() as $index => $data) {
          array_push($breadcrumbsItemsTransformed, TemplateCollector::assembly_file_content($this->CMSCore->template, 'templates/page/breadcrumps/listItem.tpl', [
            'BREADCRUMP_URL' => $data['url'],
            'BREADCRUMP_TITLE' => $data['title'],
            'BREADCRUMP_META_POSITION' => $index + 1
          ]));
        }
      }

      if (count($breadcrumbsItemsTransformed) > 0) {
        $breadcrumbsListTransformed = TemplateCollector::assembly_file_content($this->CMSCore->template, 'templates/page/breadcrumps/list.tpl', [
          'BREADCRUMPS_ITEMS' => implode($breadcrumbsItemsTransformed)
        ]);
      }

      $this->assembled = $breadcrumbsListTransformed;
    }

  }

}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  if (!defined('IS_NOT_HACKED')) {
		die('Unauthorized access attempt detected!');
	}

  class Pagination {
    private readonly SystemCore $system_core;
    private int $items_total_count;
    private int $items_in_page_count;
    private int $item_current;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  int $items_total_count
     * @param  int $items_in_page_count
     * @param  int $item_current
     * @return void
     */
    public function __construct(SystemCore $system_core, int $items_total_count, int $items_in_page_count, int $item_current = 0) {
      $this->system_core = $system_core;
      $this->items_total_count = $items_total_count;
      $this->items_in_page_count = $items_in_page_count;
      $this->item_current = $item_current;
    }
    
    /**
     * Получить количество страниц
     *
     * @return int
     */
    public function get_pages_count() : int {
      return ceil($this->items_total_count / $this->items_in_page_count);
    }
    
    /**
     * Сборка шаблона пагинации
     *
     * @return void
     */
    public function assembly() {
      $pagination_items = [];
      for ($item_i = 0; $item_i < $this->get_pages_count(); $item_i++) {
        $item_class = ($this->item_current == $item_i) ? 'pagination-list__item pagination-list__item_active' : 'pagination-list__item';
        array_push($pagination_items, sprintf('<li class="%s"><a class="pagination-list__item-link" href="?pageNumber=%d">%d</a></li>', $item_class, $item_i, $item_i + 1));
      }

      if ($this->item_current > 0) array_unshift($pagination_items, sprintf('<li class="pagination-list__item"><a class="pagination-list__item-link" href="?pageNumber=%d">&#10094;</a></li>', $this->item_current - 1));
      if ($this->item_current > 0) array_unshift($pagination_items, sprintf('<li class="pagination-list__item"><a class="pagination-list__item-link" href="?pageNumber=%d">&#10094;&#10094;</a></li>', 0));
      if ($this->item_current < ($this->get_pages_count() - 1)) array_push($pagination_items, sprintf('<li class="pagination-list__item"><a class="pagination-list__item-link" href="?pageNumber=%d">&#10095;</a></li>', $this->item_current + 1));
      if ($this->item_current < ($this->get_pages_count() - 1)) array_push($pagination_items, sprintf('<li class="pagination-list__item"><a class="pagination-list__item-link" href="?pageNumber=%d">&#10095;&#10095;</a></li>', $this->get_pages_count() - 1));

      $this->assembled = TemplateCollector::assembly('<ul class="pagination-list list-reset">{PAGINATION_ITEMS}</ul>', [
        'PAGINATION_ITEMS' => implode($pagination_items)
      ]);
    }

  }

}
?>
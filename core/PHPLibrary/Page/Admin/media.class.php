<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageMedia implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/media.css', 'rel' => 'stylesheet']);
      
      $media_files_path = sprintf('%s/uploads/media', $this->system_core->get_cms_path());
      $media_files = array_diff(scandir($media_files_path), ['.', '..']);
      $media_files_count_total = count($media_files);

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $media_files = array_slice($media_files, $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);

      $media_files_data = [];
      foreach ($media_files as $file) {
        /** @var string */
        $file_path = sprintf('%s/%s', $media_files_path, $file);
        $file_url = $file;
        
        array_push($media_files_data, [
          'file_url' => $file_url,
          'created_unix_timestamp' => filemtime($file_path)
        ]);
      }

      usort($media_files_data, function($a, $b) {
        if ($a['created_unix_timestamp'] == $b['created_unix_timestamp']) {
          return 0;
        }
    
        return ($a['created_unix_timestamp'] > $b['created_unix_timestamp']) ? -1 : 1;
      });

      $media_files_sorted = [];
      foreach ($media_files_data as $file_data) {
        array_push($media_files_sorted, $file_data['file_url']);
      }

      $media_files_transformed = [];
      foreach ($media_files_sorted as $media_file) {
        $media_file_url = sprintf('/uploads/media/%s', $media_file);
        array_push($media_files_transformed, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/media/listItem.tpl', [
          'MEDIA_FILE_URL' => $media_file_url,
          'MEDIA_FILE_FULLNAME' => $media_file
        ]));
      }

      $pagination = new Pagination($this->system_core, $media_files_count_total, $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/media.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'media',
        'PAGE_MEDIA_PAGINATION' => $pagination->assembled,
        'MEDIA_LIST_ITEMS' => implode($media_files_transformed)
      ]);
    }
  }
}

?>
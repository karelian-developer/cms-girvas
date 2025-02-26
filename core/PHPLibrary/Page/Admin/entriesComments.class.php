<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \DOMDocument as DOMDocument;
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\EntryComments as EntryComments;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;
  use \core\PHPLibrary\Parsedown as Parsedown;

  class PageEntriesComments implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_COMMENTS_NAVIGATION_%s_LABEL';

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'index' => [
        'name' => 'index',
        'iconName' => 'index',
        'link' => '/',
        'permanent' => true,
        'isActive' => false
      ],
      'entries' => [
        'name' => 'entries',
        'iconName' => 'entries',
        'link' => '/entries',
        'permanent' => false,
        'isActive' => false
      ],
      'categories' => [
        'name' => 'categories',
        'iconName' => 'entriesCategories',
        'link' => '/entriesCategories',
        'permanent' => false,
        'isActive' => false
      ],
      'comments' => [
        'name' => 'comments',
        'iconName' => 'entriesComments',
        'link' => '/entriesComments',
        'permanent' => true,
        'isActive' => true
      ]
    ];

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/entriesComments.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      $pagination_item_current = (!is_null($this->system_core->urlp->get_param('pageNumber'))) ? (int)$this->system_core->urlp->get_param('pageNumber') : 0;
      $pagination_items_on_page = 12;

      $entries_instance = new Entries($this->system_core);
      $entries_array = $entries_instance->get_all();
      
      $entries_comments_array = [];
      if (!empty($entries_array)) {
        foreach ($entries_array as $entry) {
          $entry_comments = $entry->get_comments();
          if (!empty($entry_comments)) {
            foreach ($entry_comments as $comment) {
              $comment->init_data(['content', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata']);
              array_push($entries_comments_array, $comment);
            }
          }
        }
      }

      if (!empty($entries_comments_array)) {
        usort($entries_comments_array, function ($a, $b) {
          $a_created_unix_timestamp = $a->get_created_unix_timestamp();
          $b_created_unix_timestamp = $b->get_created_unix_timestamp();

          if ($a_created_unix_timestamp != $b_created_unix_timestamp) {
            return ($a_created_unix_timestamp > $b_created_unix_timestamp) ? -1 : 1;
          }

          return 0;
        });

        $entries_comments_array = array_slice($entries_comments_array, $pagination_item_current * $pagination_items_on_page, $pagination_items_on_page);
      }

      $pagination = new Pagination($this->system_core, count($entries_comments_array), $pagination_items_on_page, $pagination_item_current);
      $pagination->assembly();
      
      $comments_table_items_assembled = [];
      if (!empty($entries_comments_array)) {
        foreach ($entries_comments_array as $comment_index => $comment) {
          $created_date_timestamp = date('d.m.Y H:i:s', $comment->get_created_unix_timestamp());
          $updated_date_timestamp = date('d.m.Y H:i:s', $comment->get_updated_unix_timestamp());

          array_push($comments_table_items_assembled, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesComments/tableItem.tpl', [
            'COMMENT_ID' => $comment->get_id(),
            'COMMENT_IS_HIDDEN_STATUS' => ($comment->is_hidden()) ? 'true' : 'false',
            'COMMENT_HIDDEN_REASON' => strip_tags($comment->get_hidden_reason()),
            'COMMENT_INDEX' => $comment_index + 1,
            'COMMENT_CONTENT' => strip_tags($comment->get_content()),
            'COMMENT_CREATED_DATE_TIMESTAMP' => $created_date_timestamp,
            'COMMENT_UPDATED_DATE_TIMESTAMP' => $updated_date_timestamp
          ]));
        }
      }

      $template_comments_table = (!empty($entries_array)) ? TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesComments/table.tpl', [
        'ADMIN_PANEL_COMMENTS_TABLE_ITEMS' => implode($comments_table_items_assembled)
      ]) : $locale_data['PAGE_ENTRIES_COMMENTS_NOT_FOUND_LABEL'];

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/entriesComments.tpl', [
        'PAGE_ENTRIES_COMMENTS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'comments',
        'ADMIN_PANEL_COMMENTS_TABLE' => $template_comments_table
      ]);
    }

  }

}

?>
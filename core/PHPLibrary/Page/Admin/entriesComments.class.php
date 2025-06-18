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

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRIES_NAVIGATION_%s_LABEL';

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
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
      ],
      'samples' => [
        'name' => 'samples',
        'iconName' => 'entriesSamples',
        'link' => '/entriesSamples',
        'permanent' => false,
        'isActive' => false
      ]
    ];

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/entriesComments.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = (!is_null($this->CMSCore->urlp->get_param('pageNumber'))) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $$paginationItemsOnPage = 12;

      $entries = new Entries($this->CMSCore);
      $entriesObjects = $entries->get_all();
      
      $entriesCommentsObjectsSorted = [];
      if (!empty($entriesObjects)) {
        foreach ($entriesObjects as $entry) {
          $entryCommentsObjects = $entry->get_comments();
          if (!empty($entryCommentsObjects)) {
            foreach ($entryCommentsObjects as $object) {
              $object->init_data(['content', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
              array_push($entriesCommentsObjectsSorted, $object);
            }
          }
        }
      }

      if (!empty($entriesCommentsObjectsSorted)) {
        usort($entriesCommentsObjectsSorted, function ($a, $b) {
          $aCreatedUnixTimestamp = $a->get_created_unix_timestamp();
          $bCreatedUnixTimestamp = $b->get_created_unix_timestamp();

          if ($aCreatedUnixTimestamp !== $bCreatedUnixTimestamp) {
            return $aCreatedUnixTimestamp > $bCreatedUnixTimestamp ? -1 : 1;
          }

          return 0;
        });

        $entriesCommentsObjectsSorted = array_slice($entriesCommentsObjectsSorted, $paginationItemCurrent * $$paginationItemsOnPage, $$paginationItemsOnPage);
      }

      $pagination = new Pagination($this->CMSCore, count($entriesCommentsObjectsSorted), $$paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();
      
      $commentsTableItemsAssembled = [];
      if (!empty($entriesCommentsObjectsSorted)) {
        foreach ($entriesCommentsObjectsSorted as $index => $object) {
          $createdDateTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
          $updatedDateTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

          array_push($commentsTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesComments/tableItem.tpl', [
            'COMMENT_ID' => $object->get_id(),
            'COMMENT_IS_HIDDEN_STATUS' => ($object->is_hidden()) ? 'true' : 'false',
            'COMMENT_HIDDEN_REASON' => strip_tags($object->get_hidden_reason()),
            'COMMENT_INDEX' => $index + 1,
            'COMMENT_CONTENT' => strip_tags($object->get_content()),
            'COMMENT_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
            'COMMENT_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp
          ]));
        }
      }

      $templateCommentsTable = (!empty($entriesObjects)) ? TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesComments/table.tpl', [
        'ADMIN_PANEL_COMMENTS_TABLE_ITEMS' => implode($commentsTableItemsAssembled)
      ]) : $localeData['PAGE_ENTRIES_COMMENTS_NOT_FOUND_LABEL'];

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/entriesComments.tpl', [
        'PAGE_ENTRIES_COMMENTS_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'comments',
        'ADMIN_PANEL_COMMENTS_TABLE' => $templateCommentsTable
      ]);
    }

  }

}

?>
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
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Pages as Pages;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PagePages implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_STATIC_PAGES_NAVIGATION_%s_LABEL';

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
      'pages' => [
        'name' => 'pages',
        'iconName' => 'pages',
        'link' => '/pages',
        'permanent' => false,
        'isActive' => true
      ],
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/pages.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $pagesStaticTableItemsAssembled = [];
      $pagesStatic = new Pages($this->CMSCore);
      $pagesStaticLocale = $this->CMSCore->get_cms_locale('admin');
      $pagesStaticLocaleName = $this->CMSCore->locale->get_name();

      $pagesStaticObjects = $pagesStatic->get_all([
        'limit' => [$paginationItemsOnPage, $paginationItemCurrent * $paginationItemsOnPage]
      ]);

      $pagination = new Pagination($this->CMSCore, $pagesStatic->get_count_total(), $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      unset($entries);

      $pageStaticNumber = 1;
      foreach ($pagesStaticObjects as $object) {
        $object->init_data(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

        $createdUnixTimestamp = date('d.m.Y H:i:s', $object->get_created_unix_timestamp());
        $publishedUnixTimestamp = date('d.m.Y H:i:s', $object->get_published_unix_timestamp());
        $updatedUnixTimestamp = date('d.m.Y H:i:s', $object->get_updated_unix_timestamp());

        $pageStaticTitle = $object->get_title($pagesStaticLocaleName);
        $pageStaticDescription = $object->get_description($pagesStaticLocaleName);

        $pageStaticTitle = strip_tags($pageStaticTitle);
        $pageStaticDescription = strip_tags($pageStaticDescription);

        array_push($pagesStaticTableItemsAssembled, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/pages/tableItem.tpl', [
          'PAGE_STATIC_ID' => $object->get_id(),
          'PAGE_STATIC_NAME' => $object->get_name(),
          'PAGE_STATIC_INDEX' => $pageStaticNumber,
          'PAGE_STATIC_TITLE' => !empty($pageStaticTitle) ? $pageStaticTitle : sprintf('[ TITLE NOT FOUND IN LOCALE %s ]', $pagesStaticLocale->get_name()),
          'PAGE_STATIC_DESCRIPTION' => !empty($pageStaticDescription) ? $pageStaticDescription : sprintf('[ DESCRIPTION NOT FOUND IN LOCALE %s ]', $pagesStaticLocale->get_name()),
          'PAGE_STATIC_PUBLISHED_STATUS' => $object->is_published() ? 'published' : 'not-published',
          'PAGE_STATIC_URL' => $object->get_url(),
          'PAGE_STATIC_CREATED_DATE_TIMESTAMP' => $createdUnixTimestamp,
          'PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP' => $object->get_published_unix_timestamp() > 0 ? $publishedUnixTimestamp : '-',
          'PAGE_STATIC_UPDATED_DATE_TIMESTAMP' => $updatedUnixTimestamp
        ]));

        $pageStaticNumber++;
      }

      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/pages.tpl', [
        'PAGE_PAGES_STATIC_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'page_static',
        'ADMIN_PANEL_PAGES_STATIC_TABLE' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/pages/table.tpl', [
          'ADMIN_PANEL_PAGES_STATIC_TABLE_ITEMS' => implode($pagesStaticTableItemsAssembled)
        ])
      ]);
    }

  }

}

?>
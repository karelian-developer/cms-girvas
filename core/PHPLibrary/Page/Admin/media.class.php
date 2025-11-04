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

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Pagination as Pagination;

class PageMedia implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/media.css', 'rel' => 'stylesheet']);
    
    $filesPath =  CMS_ROOT_DIRECTORY . '/uploads/media';
    $files = array_diff(scandir($filesPath), ['.', '..']);
    $filesCount = count($files);

    $filesData = [];
    foreach ($files as $file) {
      /** @var string */
      $path = $filesPath . '/' . $file;
      $URL = $file;
      
      array_push($filesData, [
        'fileURL' => $URL,
        'createdUnixTimestamp' => filemtime($path)
      ]);
    }

    usort($filesData, function($a, $b)
    {
      if ($a['createdUnixTimestamp'] === $b['createdUnixTimestamp']) {
        return 0;
      }
  
      return $a['createdUnixTimestamp'] > $b['createdUnixTimestamp'] ? -1 : 1;
    });

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $filesData = array_slice($filesData, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

    $filesSorted = [];
    foreach ($filesData as $data) {
      $filesSorted[] = $data['fileURL'];
    }

    $filesTransformed = [];
    foreach ($filesSorted as $file) {
      $URL = '/uploads/media/' . $file;
      $filesTransformed[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/media/listItem.tpl',
        [
          'MEDIA_FILE_URL' => $URL,
          'MEDIA_FILE_FULLNAME' => $file
        ]
      );
    }

    $pagination = new Pagination($this->CMSCore, $filesCount, $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/media.tpl',
      [
        'ADMIN_PANEL_PAGE_NAME' => 'media',
        'PAGE_MEDIA_PAGINATION' => $pagination->assembled,
        'MEDIA_LIST_ITEMS' => implode($filesTransformed)
      ]
    );
  }
}
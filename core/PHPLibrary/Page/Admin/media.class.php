<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
    
    $mediaFilesPath = $this->CMSCore->getCMSPath() . '/uploads/media';
    $mediaFiles = array_diff(scandir($mediaFilesPath), ['.', '..']);
    $mediaFilesCount = count($mediaFiles);

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $mediaFiles = array_slice($mediaFiles, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

    $mediaFilesData = [];
    foreach ($mediaFiles as $file) {
      /** @var string */
      $path = $mediaFilesPath . '/' . $file;
      $URL = $file;
      
      array_push($mediaFilesData, [
        'fileURL' => $URL,
        'createdUnixTimestamp' => filemtime($path)
      ]);
    }

    usort($mediaFilesData, function($a, $b)
    {
      if ($a['createdUnixTimestamp'] === $b['createdUnixTimestamp']) {
        return 0;
      }
  
      return $a['createdUnixTimestamp'] > $b['createdUnixTimestamp'] ? -1 : 1;
    });

    $mediaFilesSorted = [];
    foreach ($mediaFilesData as $data) {
      array_push($mediaFilesSorted, $data['fileURL']);
    }

    $mediaFilesTransformed = [];
    foreach ($mediaFilesSorted as $file) {
      $URL = '/uploads/media/' . $file;
      array_push($mediaFilesTransformed, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/media/listItem.tpl', [
        'MEDIA_FILE_URL' => $URL,
        'MEDIA_FILE_FULLNAME' => $file
      ]));
    }

    $pagination = new Pagination($this->CMSCore, $mediaFilesCount, $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/media.tpl', [
      'ADMIN_PANEL_PAGE_NAME' => 'media',
      'PAGE_MEDIA_PAGINATION' => $pagination->assembled,
      'MEDIA_LIST_ITEMS' => implode($mediaFilesTransformed)
    ]);
  }
}
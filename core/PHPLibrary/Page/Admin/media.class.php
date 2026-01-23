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
    
    $filesDirectoryPathParam = $this->CMSCore->urlp->getParam('directory') !== null
      ? urldecode($this->CMSCore->urlp->getParam('directory'))
      : null;

    $filesDirectoryPath = $filesDirectoryPathParam === null
    ? '/uploads/media'
    : $filesDirectoryPathParam;

    $filesDirectoryPathWithRoot = CMS_ROOT_DIRECTORY . $filesDirectoryPath;

    $files = array_diff(scandir($filesDirectoryPathWithRoot), ['.', '..']);

    usort($files, function($a, $b) use ($filesDirectoryPathWithRoot) {
      $pathA = $filesDirectoryPathWithRoot . DIRECTORY_SEPARATOR . $a;
      $pathB = $filesDirectoryPathWithRoot . DIRECTORY_SEPARATOR . $b;
      
      $isDirA = is_dir($pathA);
      $isDirB = is_dir($pathB);
      
      if ($isDirA === $isDirB) {
        return strcasecmp($a, $b);
      }
      
      return $isDirA ? -1 : 1;
    });

    $filesCount = count($files);

    $filesData = [];
    foreach ($files as $file) {
      /** @var string */
      $filePath = $filesDirectoryPathWithRoot . '/' . $file;
      $URL = $filesDirectoryPath . '/' . $file;
      
      $filesData[] = [
        'fileURL' => $URL,
        'filePath' => $filesDirectoryPath,
        'fileExtension' => pathinfo($filePath, PATHINFO_EXTENSION),
        'fileName' => pathinfo($filePath, PATHINFO_FILENAME),
        'isDirectory' => is_dir($filePath),
        'createdUnixTimestamp' => filemtime($filePath)
      ];
    }

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 36;

    $filesData = array_slice($filesData, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

    $filesTransformed = [];
    foreach ($filesData as $file) {
      $URL = '/uploads/media/' . $file;
      $fileTemplatePath = $file['isDirectory']
        ? 'templates/page/media/directory.tpl'
        : 'templates/page/media/file.tpl';
      $filesTransformed[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        $fileTemplatePath,
        [
          'FILE_URL' => $file['fileURL'],
          'FILE_EXTENSION' => $file['fileExtension'],
          'FILE_NAME' => $file['fileName']
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
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
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageTemplates implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_TEMPLATES_NAVIGATION_%s_LABEL';

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
    'local' => [
      'name' => 'local',
      'iconName' => 'local',
      'link' => '/templates/local',
      'permanent' => true,
      'isActive' => false
    ],
    'repository' => [
      'name' => 'repository',
      'iconName' => 'repository',
      'link' => '/templates/repository',
      'permanent' => true,
      'isActive' => false
    ]
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/templates.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $parsedown = new Parsedown();

    $subpageName =  $this->CMSCore->urlp->getPath(2) ?? 'local';
    if (isset($this->navigationSubsections[$subpageName])) {
      $this->navigationSubsections[$subpageName]['isActive'] = true;
    }

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null ? (int) $this->CMSCore->urlp->getParam('pageNumber') : 0;
    $paginationItemsOnPage = 12;

    $themesCount = 0;

    $themesListItemsTransformed = [];

    if ($this->CMSCore->urlp->getPath(2) === 'repository') {
      $ch = curl_init('https://repository.cms-girvas.ru/templates');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $CURLExecuteResult = json_decode(curl_exec($ch), true);
      curl_close($ch);

      if (isset($CURLExecuteResult['outputData'])) {
        if (count($CURLExecuteResult['outputData']) > 0) {
          $themesCount = count($CURLExecuteResult['outputData']);
          $CURLExecuteResult['outputData'] = array_slice($CURLExecuteResult['outputData'], $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

          foreach ($CURLExecuteResult['outputData'] as $name => $data) {
            $theme = new Template($this->CMSCore, $name);
            $themeInstalledStatus = $theme->existsFileMetadataJSON() ? 'installed' : 'not-installed';

            $metadataTitle = isset($data['metadata']['title']) ? $data['metadata']['title'] : 'Anonymous Template';
            $metadataDescription = isset($data['metadata']['description']) ? $data['metadata']['description'] : 'Without description.';
            $metadataDatetimeCreatedUnix = isset($data['metadata']['createdUnixTimestamp']) ? $data['metadata']['createdUnixTimestamp'] : 0;
            $metadataAuthorName = isset($data['metadata']['authorName']) ? $data['metadata']['authorName'] : 'Anonymous';
            $metadataCategoryName = isset($data['metadata']['categoryName']) ? $data['metadata']['categoryName'] : 'default';

            array_push($themesListItemsTransformed, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/templates/listItem.tpl', [
              'TEMPLATE_NAME' => $name,
              'TEMPLATE_TITLE' => $metadataTitle,
              'TEMPLATE_DESCRIPTION' => $parsedown->text($metadataDescription),
              'TEMPLATE_CREATED_TIMESTAMP' => date('d.m.Y', $metadataDatetimeCreatedUnix),
              'TEMPLATE_AUTHOR_NAME' => $metadataAuthorName,
              'TEMPLATE_LINK' => '/admin/templates/repository/' . $theme->getName(),
              'TEMPLATE_PREVIEW_URL' => $data['preview'],
              'TEMPLATE_INSTALLED_STATUS' => $themeInstalledStatus,
              'TEMPLATE_CATEGORY_NAME' => $metadataCategoryName
            ]));
          }
        }
      }
    } elseif ($this->CMSCore->urlp->getPath(2) === 'local' || $this->CMSCore->urlp->getPath(2) === null) {
      $uploadedThemesNames = $this->CMSCore->getArrayUploadedTemplatesNames();
      $uploadedThemesNames = array_diff($uploadedThemesNames, ['admin', 'install']);

      if (count($uploadedThemesNames) > 0) {
        $themesCount = count($uploadedThemesNames);
        $uploadedThemesNames = array_slice($uploadedThemesNames, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

        foreach ($uploadedThemesNames as $name) {
          $theme = new Template($this->CMSCore, $name);
          $themeInstalledStatus = $theme->existsFileMetadataJSON() ? 'installed' : 'not-installed';
          
          if ($theme->existsFileMetadataJSON()) {
            array_push($themesListItemsTransformed, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/templates/listItem.tpl', [
              'TEMPLATE_NAME' => $theme->getName(),
              'TEMPLATE_TITLE' => $theme->getTitle(),
              'TEMPLATE_DESCRIPTION' => $parsedown->text($theme->getDescription()),
              'TEMPLATE_CREATED_TIMESTAMP' => date('d.m.Y', $theme->getCoreCreatedUnixTimestamp()),
              'TEMPLATE_AUTHOR' => $theme->getAuthorName(),
              'TEMPLATE_PREVIEW_URL' => $theme->getPreviewURL(),
              'TEMPLATE_LINK' => '/admin/template/' . $theme->getName(),
              'TEMPLATE_INSTALLED_STATUS' => $themeInstalledStatus,
              'TEMPLATE_CATEGORY_NAME' => $theme->getCategoryName(),
            ]));
          }

          unset($theme);
        }
      }
    }

    $pagination = new Pagination($this->CMSCore, $themesCount, $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    if ($this->CMSCore->urlp->getPath(2) === 'repository' && !is_null($this->CMSCore->urlp->getPath(3))) {
      $name = $this->CMSCore->urlp->getPath(3);
      $themePage = new PageTemplate($this->CMSCore, $this->page);
      
      $themePage->assembly();
      $this->assembled = $themePage->assembled;
    } else {
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/templates.tpl', [
        'PAGE_TEMPLATES_PAGINATION' => $pagination->assembled,
        'ADMIN_PANEL_PAGE_NAME' => 'templates',
        'TEMPLATES_LIST' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/templates/list.tpl', [
          'TEMPLATES_LIST_ITEMS' => implode('', $themesListItemsTransformed)
        ])
      ]);
    }
  }
}
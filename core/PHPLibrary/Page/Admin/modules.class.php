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
use \core\PHPLibrary\NadvoParse as NadvoParse;
use \core\PHPLibrary\Module as Module;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;

class PageModules implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_MODULES_NAVIGATION_%s_LABEL';

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
      'link' => '/modules/local',
      'permanent' => true,
      'isActive' => false
    ],
    'repository' => [
      'name' => 'repository',
      'iconName' => 'repository',
      'link' => '/modules/repository',
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/modules.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $nadvoParse = new NadvoParse();
    $subpageName =  $this->CMSCore->urlp->getPath(2) ?? 'local';

    if (isset($this->navigationSubsections[$subpageName])) {
      $this->navigationSubsections[$subpageName]['isActive'] = true;
    }

    $paginationItemCurrent = $this->CMSCore->urlp->getParam('pageNumber') !== null
      ? (int) $this->CMSCore->urlp->getParam('pageNumber')
      : 0;

    $paginationItemsOnPage = 12;
    $modulesCount = 0;

    $locationPlaceName = $this->CMSCore->urlp->getPath(2) === 'repository'
      ? 'repository'
      : 'local'; 

    if (
      $this->CMSCore->urlp->getPath(2) === 'repository'
      && $this->CMSCore->urlp->getPath(3) === null
    ) {
      $ch = curl_init('https://repository.cms-girvas.ru/modules');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $CURLExucuteResult = json_decode(curl_exec($ch), true);
      curl_close($ch);

      if (isset($CURLExucuteResult['outputData'])) {
        $modulesListItemsTransformed = [];

        if (count($CURLExucuteResult['outputData']) > 0) {
          $modulesCount = count($CURLExucuteResult['outputData']);
          $CURLExucuteResult['outputData'] = array_slice($CURLExucuteResult['outputData'], $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

          foreach ($CURLExucuteResult['outputData'] as $name => $data) {
            $module = new Module($this->CMSCore, $name);
            $moduleInstalledStatus = $module->existsFileMetadataJSON() ? 'installed' : 'not-installed';
            $moduleEnabledStatus = $module->isEnabled() ? 'enabled' : 'disabled';

            $metadataTitle = isset($data['metadata']['title']) ? $data['metadata']['title'] : 'Anonymous Module';
            $metadataDescription = isset($data['metadata']['description']) ? $data['metadata']['description'] : 'Without description.';
            $metadataDatetimeCreatedUnix = isset($data['metadata']['datetimeCreatedUnix']) ? $data['metadata']['datetimeCreatedUnix'] : 0;
            $metadataAuthorName = isset($data['metadata']['authorName']) ? $data['metadata']['authorName'] : 'Anonymous';
            $metadataCategoryName = isset($data['metadata']['categoryName']) ? $data['metadata']['categoryName'] : 'default';

            $modulesListItemsTransformed[] = ThemeCollector::assemblyFileContent(
              $this->CMSCore->theme,
              'templates/page/modules/listItem.tpl',
              [
                'MODULE_NAME' => $name,
                'MODULE_TITLE' => $metadataTitle,
                'MODULE_DESCRIPTION' => $nadvoParse->parse($metadataDescription),
                'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $metadataDatetimeCreatedUnix),
                'MODULE_AUTHOR_NAME' => $metadataAuthorName,
                'MODULE_LINK' => '/admin/modules/repository/' . $module->getName(),
                'MODULE_PREVIEW_URL' => $data['preview'],
                'MODULE_INSTALLED_STATUS' => $moduleInstalledStatus,
                'MODULE_ENABLED_STATUS' => $moduleEnabledStatus,
                'TEMPLATE_CATEGORY_NAME' => $metadataCategoryName
              ]
            );
          }
        }
      } else {
        $modulesListItemsTransformed = [];
      }
    } elseif (
      $this->CMSCore->urlp->getPath(2) === 'local'
      || $this->CMSCore->urlp->getPath(2) === null
    ) {
      $modulesListItemsTransformed = [];
      $uploadedModulesNames = $this->CMSCore->getArrayUploadedModulesNames();
      if (count($uploadedModulesNames) > 0) {
        $modulesCount = count($uploadedModulesNames);
        $uploadedModulesNames = array_slice($uploadedModulesNames, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

        foreach ($uploadedModulesNames as $name) {
          $module = new Module($this->CMSCore, $name);
          $moduleInstalledStatus = $module->existsFileMetadataJSON() ? 'installed' : 'not-installed';
          $moduleEnabledStatus = $module->isEnabled() ? 'enabled' : 'disabled';

          if ($module->existsFileMetadataJSON()) {
            $modulesListItemsTransformed[] =  ThemeCollector::assemblyFileContent(
              $this->CMSCore->theme,
              'templates/page/modules/listItem.tpl',
              [
                'MODULE_NAME' => $module->getName(),
                'MODULE_TITLE' => $module->getTitle(),
                'MODULE_DESCRIPTION' => $nadvoParse->parse($module->getDescription()),
                'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $module->getCoreCreatedUnixTimestamp()),
                'MODULE_AUTHOR' => $module->getAuthorName(),
                'MODULE_PREVIEW_URL' => $module->getPreviewURL(),
                'MODULE_LINK' => '/admin/module/' . $module->getName(),
                'MODULE_INSTALLED_STATUS' => $moduleInstalledStatus,
                'MODULE_ENABLED_STATUS' => $moduleEnabledStatus
              ]
            );
          }

          unset($module);
        }
      }
    }

    $pagination = new Pagination($this->CMSCore, $modulesCount, $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    if (
      $this->CMSCore->urlp->getPath(2) === 'repository'
      && $this->CMSCore->urlp->getPath(3) !== null
    ) {
      $modulePage = new PageModule($this->CMSCore, $this->page);
      $modulePage->assembly();

      $this->assembled = $modulePage->assembled;
    } else {
      /** @var string $assembled Содержимое шаблона страницы */
      $this->assembled = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/modules.tpl',
        [
          'PAGE_MODULES_PAGINATION' => $pagination->assembled,
          'MODULES_LIST' => !empty($modulesListItemsTransformed)
            ? ThemeCollector::assemblyFileContent(
              $this->CMSCore->theme,
              'templates/page/modules/list.tpl',
              [
                'MODULES_PLACE_NAME' => $locationPlaceName,
                'MODULES_LIST_ITEMS' => implode($modulesListItemsTransformed)
              ]
            )
            : sprintf(
                '<p class="page__content-phar">%s</p>',
                $localeData['PAGE_MODULES_MODULES_INSTALLED_NOT_FOUND_TITLE']
              )
        ]
      );
    }
  }
}
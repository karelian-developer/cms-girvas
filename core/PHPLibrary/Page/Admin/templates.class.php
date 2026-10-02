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
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;

class PageTemplates implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_THEMES_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'isActive' => false,
      'permission' => null,
    ],
    'local' => [
      'name' => 'local',
      'iconName' => 'local',
      'link' => '/templates/local',
      'permanent' => true,
      'isActive' => false,
      'permission' => 'hasPermissionAdminThemesManagement',
    ],
    'repository' => [
      'name' => 'repository',
      'iconName' => 'repository',
      'link' => '/templates/repository',
      'permanent' => true,
      'isActive' => false,
      'permission' => 'hasPermissionAdminThemesManagement',
    ]
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Получить группу текущего авторизованного пользователя админки
   * 
   * @return UserGroup|null
   */
  private function getCurrentUserGroup() : ?UserGroup
  {
    /** @var User|null $user */
    $user = $this->CMSCore->client->getUser(2);
    if ($user === null) {
      return null;
    }

    $user->initData(['metadata']);

    /** @var UserGroup|null $userGroup */
    $userGroup = $user->getGroup();
    if ($userGroup === null) {
      return null;
    }

    $userGroup->initData(['permissions']);

    return $userGroup;
  }

  /**
   * Проверить, есть ли у текущего пользователя право управления шаблонами
   * 
   * @return bool
   */
  private function currentUserCanManageTemplates() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionAdminThemesManagement')
      && $userGroup->hasPermissionAdminThemesManagement();
  }

  /**
   * Собрать страницу ошибки и подменить итоговую сборку
   * 
   * @param int $httpCode
   * 
   * @return void
   */
  private function assemblyError(int $httpCode) : void
  {
    http_response_code($httpCode);

    $pageError = new PageError($this->CMSCore, $this->page, $httpCode);
    $pageError->assembly();
    $this->assembled = $pageError->assembled;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void
  {
    // Если нет прав на управление шаблонами — подразделы не собираем
    if (!$this->currentUserCanManageTemplates()) {
      return;
    }

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageTemplates()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/themes.css', 'rel' => 'stylesheet']);
    
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
    $themesCount = 0;

    $locationPlaceName = $this->CMSCore->urlp->getPath(2) === 'repository'
      ? 'repository'
      : 'local'; 

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

            $themesListItemsTransformed[] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/themes/listItem.tpl', [
              'THEME_NAME' => $name,
              'THEME_TITLE' => $metadataTitle,
              'THEME_DESCRIPTION' => $nadvoParse->parse($metadataDescription),
              'THEME_CREATED_TIMESTAMP' => date('d.m.Y', $metadataDatetimeCreatedUnix),
              'THEME_AUTHOR_NAME' => $metadataAuthorName,
              'THEME_LINK' => '/admin/templates/repository/' . $theme->getName(),
              'THEME_PREVIEW_URL' => $data['preview'],
              'THEME_INSTALLED_STATUS' => $themeInstalledStatus,
              'THEME_CATEGORY_NAME' => $metadataCategoryName
            ]);
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
            $themesListItemsTransformed[] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/themes/listItem.tpl', [
              'THEME_NAME' => $theme->getName(),
              'THEME_TITLE' => $theme->getTitle(),
              'THEME_DESCRIPTION' => $nadvoParse->parse($theme->getDescription()),
              'THEME_CREATED_TIMESTAMP' => date('d.m.Y', $theme->getCoreCreatedUnixTimestamp()),
              'THEME_AUTHOR' => $theme->getAuthorName(),
              'THEME_PREVIEW_URL' => $theme->getPreviewURL(),
              'THEME_LINK' => '/admin/template/' . $theme->getName(),
              'THEME_INSTALLED_STATUS' => $themeInstalledStatus,
              'THEME_CATEGORY_NAME' => $theme->getCategoryName(),
            ]);
          }

          unset($theme);
        }
      }
    }

    $pagination = new Pagination($this->CMSCore, $themesCount, $paginationItemsOnPage, $paginationItemCurrent);
    $pagination->assembly();

    if (
      $this->CMSCore->urlp->getPath(2) === 'repository'
      && $this->CMSCore->urlp->getPath(3) !== null
    ) {
      $name = $this->CMSCore->urlp->getPath(3);
      $themePage = new PageTemplate($this->CMSCore, $this->page);
      
      $themePage->assembly();
      $this->assembled = $themePage->assembled;
    } else {
      /** @var string $site_page Содержимое шаблона страницы */
      $this->assembled = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme, 'templates/page/themes.tpl',
        [
          'PAGE_THEMES_PAGINATION' => $pagination->assembled,
          'THEMES_LIST' => ThemeCollector::assemblyFileContent(
            $this->CMSCore->theme,
            'templates/page/themes/list.tpl',
            [
              'THEMES_PLACE_NAME' => $locationPlaceName,
              'THEMES_LIST_ITEMS' => implode('', $themesListItemsTransformed)
            ]
          )
        ]
      );
    }
  }
}
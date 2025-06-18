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
  use \core\PHPLibrary\Module as Module;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;
  use \core\PHPLibrary\Pagination as Pagination;

  class PageModules implements InterfacePage {
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page/modules.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      $parsedown = new Parsedown();

      $subpageName = !is_null($this->CMSCore->urlp->get_path(2)) ? $this->CMSCore->urlp->get_path(2) : 'local';
      if (isset($this->navigationSubsections[$subpageName])) {
        $this->navigationSubsections[$subpageName]['isActive'] = true;
      }

      $paginationItemCurrent = !is_null($this->CMSCore->urlp->get_param('pageNumber')) ? (int)$this->CMSCore->urlp->get_param('pageNumber') : 0;
      $paginationItemsOnPage = 12;

      $modulesCount = 0;

      if ($this->CMSCore->urlp->get_path(2) === 'repository') {
        
        if (is_null($this->CMSCore->urlp->get_path(3))) {
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
                $moduleInstalledStatus = ($module->exists_file_metadata_json()) ? 'installed' : 'not-installed';
                $moduleEnabledStatus = ($module->is_enabled()) ? 'enabled' : 'disabled';

                $metadataTitle = isset($data['metadata']['title']) ? $data['metadata']['title'] : 'Anonymous Module';
                $metadataDescription = isset($data['metadata']['description']) ? $data['metadata']['description'] : 'Without description.';
                $metadataDatetimeCreatedUnix = isset($data['metadata']['datetimeCreatedUnix']) ? $data['metadata']['datetimeCreatedUnix'] : 0;
                $metadataAuthorName = isset($data['metadata']['authorName']) ? $data['metadata']['authorName'] : 'Anonymous';
                $metadataCategoryName = isset($data['metadata']['categoryName']) ? $data['metadata']['categoryName'] : 'default';

                array_push($modulesListItemsTransformed, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/modules/listItem.tpl', [
                  'MODULE_NAME' => $name,
                  'MODULE_TITLE' => $metadataTitle,
                  'MODULE_DESCRIPTION' => $parsedown->text($metadataDescription),
                  'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $metadataDatetimeCreatedUnix),
                  'MODULE_AUTHOR_NAME' => $metadataAuthorName,
                  'MODULE_LINK' => sprintf('/admin/modules/repository/%s', $module->get_name()),
                  'MODULE_PREVIEW_URL' => $data['preview'],
                  'MODULE_INSTALLED_STATUS' => $moduleInstalledStatus,
                  'MODULE_ENABLED_STATUS' => $moduleEnabledStatus,
                  'TEMPLATE_CATEGORY_NAME' => $metadataCategoryName
                ]));
              }
            }
          } else {
            $modulesListItemsTransformed = [];
          }
        }

      } elseif ($this->CMSCore->urlp->get_path(2) === 'local' || is_null($this->CMSCore->urlp->get_path(2))) {

        $modulesListItemsTransformed = [];
        $uploadedModulesNames = $this->CMSCore->get_array_uploaded_modules_names();
        if (count($uploadedModulesNames) > 0) {
          $modulesCount = count($uploadedModulesNames);
          $uploadedModulesNames = array_slice($uploadedModulesNames, $paginationItemCurrent * $paginationItemsOnPage, $paginationItemsOnPage);

          foreach ($uploadedModulesNames as $name) {
            $module = new Module($this->CMSCore, $name);
            $moduleInstalledStatus = ($module->exists_file_metadata_json()) ? 'installed' : 'not-installed';
            $moduleEnabledStatus = $module->is_enabled() ? 'enabled' : 'disabled';

            if ($module->exists_file_metadata_json()) {
              array_push($modulesListItemsTransformed, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/modules/listItem.tpl', [
                'MODULE_NAME' => $module->get_name(),
                'MODULE_TITLE' => $module->get_title(),
                'MODULE_DESCRIPTION' => $parsedown->text($module->get_description()),
                'MODULE_CREATED_TIMESTAMP' => date('d.m.Y', $module->get_core_created_unix_timestamp()),
                'MODULE_AUTHOR' => $module->get_author_name(),
                'MODULE_PREVIEW_URL' => $module->get_preview_url(),
                'MODULE_LINK' => '/admin/module/' . $module->get_name(),
                'MODULE_INSTALLED_STATUS' => $moduleInstalledStatus,
                'MODULE_ENABLED_STATUS' => $moduleEnabledStatus
              ]));
            }

            unset($module);
          }
        }

      }

      $pagination = new Pagination($this->CMSCore, $modulesCount, $paginationItemsOnPage, $paginationItemCurrent);
      $pagination->assembly();

      if ($this->CMSCore->urlp->get_path(2) === 'repository' && !is_null($this->CMSCore->urlp->get_path(3))) {
        $modulePage = new PageModule($this->CMSCore, $this->page);
        $modulePage->assembly();

        $this->assembled = $modulePage->assembled;
      } else {
        /** @var string $assembled Содержимое шаблона страницы */
        $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/modules.tpl', [
          'PAGE_MODULES_PAGINATION' => $pagination->assembled,
          'ADMIN_PANEL_PAGE_NAME' => 'modules',
          'MODULES_LIST' => !empty($modulesListItemsTransformed) ? TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/modules/list.tpl', [
            'MODULES_LIST_ITEMS' => implode($modulesListItemsTransformed)
          ]) : sprintf('<p class="page__content-phar">%s</p>', $localeData['PAGE_MODULES_MODULES_INSTALLED_NOT_FOUND_TITLE'])
        ]);
      }
    }
  }
}

?>
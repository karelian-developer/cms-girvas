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
use \core\PHPLibrary\Module as Module;
use \core\PHPLibrary\Module\EnumMetadata as ModuleEnumMetadata;
use \core\PHPLibrary\Module\EnumWeight as ModuleEnumWeight;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PageModule implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_MODULE_NAVIGATION_%s_LABEL';

  /** @property SystemCore Объект системного ядра*/
  public SystemCore $CMSCore;

  /** @property Page Объект страницы */
  public Page $page;

  /** @property array Массив разрешенных типов метаданных */
  public array $allowedMetadata = [
    ModuleEnumMetadata::AUTHOR_NAME,
    ModuleEnumMetadata::AUTHOR_CODE_NAME,
    ModuleEnumMetadata::AUTHOR_CODE_SERVER_NAME,
    ModuleEnumMetadata::AUTHOR_CODE_CLIENT_NAME,
    ModuleEnumMetadata::AUTHOR_DESIGNER_NAME,
    ModuleEnumMetadata::AUTHOR_LAYOUT_NAME,
    ModuleEnumMetadata::AUTHOR_SITE_LINK,
    ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK,
    ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK,
    ModuleEnumMetadata::CATEGORY_NAME,
    ModuleEnumMetadata::WEIGHT,
    ModuleEnumMetadata::DATETIME_CREATED_UNIX,
    ModuleEnumMetadata::DATETIME_UPDATED_UNIX,
    ModuleEnumMetadata::VERSION
  ];

  /** @property string Итоговая сборка шаблона в виде строки */
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/modules',
      'permanent' => true,
      'isActive' => false
    ],
  ];

  /**
   * __construct
   * 
   * @return void
   */
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

  /**
   * Сборка шаблона
   * 
   * @return void
   */
  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/module.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $moduleName = $this->CMSCore->urlp->getPath(2) === 'repository' ? $this->CMSCore->urlp->getPath(3) : $this->CMSCore->urlp->getPath(2);
    $module = new Module($this->CMSCore, $moduleName);
    $moduleScreenshotsListItems = [];
    $moduleMetadataItemsTransformed = [];

    $isExists = false;
    if ($this->CMSCore->urlp->getPath(2) === 'repository') {
      $repositoryURL = 'https://repository.cms-girvas.ru/modules/' . $moduleName;

      $ch = curl_init($repositoryURL);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $CURLExucuteResult = json_decode(curl_exec($ch), true);
      curl_close($ch);

      $moduleData = $CURLExucuteResult['outputData'];
      if (isset($moduleData['metadata'])) {
        $isExists = true;
      }

      if ($isExists) {
        $parsedown = new Parsedown();

        $moduleMetadata = $moduleData['metadata'];
        $moduleTitle = $moduleMetadata['title'];
        $moduleDescription = file_get_contents($moduleData['readme_url']);
        $moduleDescription = $parsedown->text($moduleDescription);

        if (count($moduleData['screenshots']) > 0) {
          foreach ($moduleData['screenshots'] as $screenshotURL) {
            array_push($moduleScreenshotsListItems, TemplateCollector::assembly('<li class="gallery__item item"><img class="gallery__item-image item-image" src="{MODULE_SCREENSHOT_URL}"></li>', [
              'MODULE_SCREENSHOT_URL' => $screenshotURL
            ]));
          }
        }
      }
    } else {
      if ($module->existsCoreFile()) {
        if ($module->existsFileMetadataJSON()) {
          $isExists = true;
        }
      }

      if ($isExists) {
        $parsedown = new Parsedown();

        $moduleMetadata = $module->getMetadata();
        $moduleTitle = $module->getTitle();
        $moduleDescription = $module->getContentFileReadmeMD();
        $moduleDescription = !empty($moduleDescription) ? $parsedown->text($moduleDescription) : $localeData['DEFAULT_TEXT_DESCRIPTION_NOT_FOUND'];

        $moduleScreenshotsFiles = $module->getScreenshotsArray();
        if (count($moduleScreenshotsFiles) > 0) {
          $moduleScreenshotsDirectoryURL = $module->getScreenshotsURL();
          foreach ($moduleScreenshotsFiles as $file) {
            array_push($moduleScreenshotsListItems, TemplateCollector::assembly('<li class="gallery__item item"><img class="gallery__item-image item-image" src="{MODULE_SCREENSHOT_URL}"></li>', [
              'MODULE_SCREENSHOT_URL' => $moduleScreenshotsDirectoryURL . '/' . $file
            ]));
          }
        }
      }
    }

    if ($isExists) {
      foreach ($this->allowedMetadata as $enumMetadata) {
        /** @var string Имя ячейки метаданных */
        $metadataName = Module::getMetadataName($enumMetadata);

        if (array_key_exists($metadataName, $moduleMetadata) || $enumMetadata === ModuleEnumMetadata::WEIGHT) {
          $getMetadataValue = function (Module $module, array $moduleMetadata, ModuleEnumMetadata $enumMetadata) : string {
            $metadataName = Module::getMetadataName($enumMetadata);
            
            if ($enumMetadata === ModuleEnumMetadata::WEIGHT) {
              $moduleWeight = $this->CMSCore->urlp->getPath(2) !== 'repository' ? Module::getWeight($module, ModuleEnumWeight::BYTES) : $moduleMetadata[$metadataName];

              if ($moduleWeight < 1024) {
                return sprintf('%s B', $moduleWeight);
              }
              
              if ($moduleWeight >= 1024 && $moduleWeight < 1024 ^ 2) {
                return sprintf('%s KB', round($moduleWeight / 1024, 2));
              }

              if ($moduleWeight >= 1024 ^ 2 && $moduleWeight < 1024 ^ 3) {
                return sprintf('%s MB', round($moduleWeight / (1024 ^ 2), 2));
              }

              if ($moduleWeight >= 1024 ^ 3) {
                return sprintf('%s GB', round($moduleWeight / (1024 ^ 3), 2));
              }
            }

            if ($enumMetadata === ModuleEnumMetadata::DATETIME_CREATED_UNIX || $enumMetadata === ModuleEnumMetadata::DATETIME_UPDATED_UNIX) {
              return date('d.m.Y', $moduleMetadata[$metadataName]);
            }

            return $moduleMetadata[$metadataName] ?? '[???]';
          };

          /** @var string Заголовок ячейки метаданных */
          $metadataTitle = match ($enumMetadata) {
            ModuleEnumMetadata::AUTHOR_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_CODE_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_CODE_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_CODE_SERVER_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_CODE_SERVER_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_CODE_CLIENT_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_CODE_CLIENT_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_DESIGNER_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_DESIGNER_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_LAYOUT_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_LAYOUT_NAME_LABEL'),
            ModuleEnumMetadata::AUTHOR_SITE_LINK => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_SITE_LINK_LABEL'),
            ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_SOCIAL_VK_LINK_LABEL'),
            ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_AUTHOR_SOCIAL_OK_LINK_LABEL'),
            ModuleEnumMetadata::CATEGORY_NAME => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_CATEGORY_NAME_LABEL'),
            ModuleEnumMetadata::WEIGHT => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_SIZE_LABEL'),
            ModuleEnumMetadata::DATETIME_CREATED_UNIX => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_DATETIME_CREATED_UNIX_LABEL'),
            ModuleEnumMetadata::DATETIME_UPDATED_UNIX => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_DATETIME_UPDATED_UNIX_LABEL'),
            ModuleEnumMetadata::VERSION => $module->CMSCore->locale::getDataValue($localeData, 'PAGE_MODULE_VERSION_LABEL')
          };

          $metadataValueTemplate = match ($enumMetadata) {
            ModuleEnumMetadata::AUTHOR_SITE_LINK => '<li class="module__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK => '<li class="module__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK => '<li class="module__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="module__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            default => '<li class="module__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> {METADATA_VALUE}</li>',
          };

          array_push($moduleMetadataItemsTransformed, TemplateCollector::assembly($metadataValueTemplate, [
            'METADATA_TITLE' => $metadataTitle,
            'METADATA_VALUE' => $getMetadataValue($module, $moduleMetadata, $enumMetadata)
          ]));
        }
      }

      if (count($moduleScreenshotsListItems) > 0) {
        $moduleGalleryList = TemplateCollector::assembly('<ul class="gallery__list list list-reset">{MODULE_GALLARY_LIST_ITEMS}</ul>', [
          'MODULE_GALLARY_LIST_ITEMS' => implode($moduleScreenshotsListItems)
        ]);
      } else {
        $moduleGalleryList = '';
      }

      if (count($moduleMetadataItemsTransformed) > 0) {
        $metadataListTransformed = TemplateCollector::assembly('<ul class="module__metadata-list metadata-list list-reset">{METADATA_LIST}</ul>', [
          'METADATA_LIST' => implode($moduleMetadataItemsTransformed)
        ]);
      } else {
        $metadataListTransformed = $localeData['PAGE_MODULE_METADATA_BLOCK_METADATA_NOT_FOUND_TITLE'];
      }

      $parsedown = new Parsedown();

      $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/module.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'module',
        'MODULE_NAME' => $moduleName,
        'MODULE_TITLE' => $moduleTitle,
        'MODULE_DESCRIPTION' => $parsedown->text($moduleDescription),
        'MODULE_GALLARY_LIST' => $moduleGalleryList,
        'MODULE_METADATA_LIST' => $metadataListTransformed,
        'MODULE_ENABLED_STATUS' => $module->isEnabled() ? 'enabled' : 'disabled',
        'MODULE_INSTALLED_STATUS' => $module->isInstalled() ? 'installed' : 'not-installed'
      ]);
    } else {
      http_response_code(404);

      $pageError = new PageError($this->CMSCore, $this->page, 404);
      $pageError->assembly();
      $this->assembled = $pageError->assembled;
    }
  }
}
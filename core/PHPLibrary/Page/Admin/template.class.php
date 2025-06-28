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
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\EnumMetadata as TemplateEnumMetadata;
use \core\PHPLibrary\Template\EnumWeight as TemplateEnumWeight;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageTemplate implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_TEMPLATE_NAVIGATION_%s_LABEL';

  /** @property SystemCore Объект системного ядра*/
  public SystemCore $CMSCore;

  /** @property Page Объект страницы */
  public Page $page;

  /** @property array Массив разрешенных типов метаданных */
  public array $allowedMetadata = [
    TemplateEnumMetadata::AUTHOR_NAME,
    TemplateEnumMetadata::AUTHOR_CODE_NAME,
    TemplateEnumMetadata::AUTHOR_CODE_SERVER_NAME,
    TemplateEnumMetadata::AUTHOR_CODE_CLIENT_NAME,
    TemplateEnumMetadata::AUTHOR_DESIGNER_NAME,
    TemplateEnumMetadata::AUTHOR_LAYOUT_NAME,
    TemplateEnumMetadata::AUTHOR_SITE_LINK,
    TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK,
    TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK,
    TemplateEnumMetadata::CATEGORY_NAME,
    TemplateEnumMetadata::WEIGHT,
    TemplateEnumMetadata::DATETIME_CREATED_UNIX,
    TemplateEnumMetadata::DATETIME_UPDATED_UNIX,
    TemplateEnumMetadata::VERSION
  ];
  
  /** @property string Итоговая сборка шаблона в виде строки */
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/templates',
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
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/template.css', 'rel' => 'stylesheet']);

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $themeName = $this->CMSCore->urlp->getPath(2) === 'repository' ? $this->CMSCore->urlp->getPath(3) : $this->CMSCore->urlp->getPath(2);
    $theme = new Template($this->CMSCore, $themeName);
    $themeScreenshotsListItems = [];
    $themeMetadataItemsTransformed = [];

    $isExists = false;

    if ($this->CMSCore->urlp->getPath(2) === 'repository') {
      $themeRepositoryURL = 'https://repository.cms-girvas.ru/templates/' . $themeName;
      $ch = curl_init($themeRepositoryURL);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $CURLExucuteResult = json_decode(curl_exec($ch), true);
      curl_close($ch);

      $themeData = $CURLExucuteResult['outputData'];
      if (isset($themeData['metadata'])) {
        $isExists = true;
      }

      if ($isExists) {
        $parsedown = new Parsedown();

        $themeMetadata = $themeData['metadata'];
        $themeTitle = $themeMetadata['title'];
        $themeDescription = file_get_contents($themeData['readme_url']);
        $themeDescription = !empty($themeDescription) ? $parsedown->text($themeDescription) : $localeData['DEFAULT_TEXT_DESCRIPTION_NOT_FOUND'];

        if (count($themeData['screenshots']) > 0) {
          foreach ($themeData['screenshots'] as $screenshotURL) {
            array_push($themeScreenshotsListItems, TemplateCollector::assembly('<li class="gallery__item"><img class="gallery__item-image item-image" src="{TEMPLATE_SCREENSHOT_URL}"></li>', [
              'TEMPLATE_SCREENSHOT_URL' => $screenshotURL
            ]));
          }
        }
      }
    } else {
      if ($theme->existsFileMetadataJSON()) {
        $isExists = true;
      }

      if ($isExists) {
        $parsedown = new Parsedown();

        $themeMetadata = $theme->getMetadata();
        $themeTitle = $theme->getTitle();
        $themeDescription = $theme->getContentFileReadmeMD();
        $themeDescription = $parsedown->text($themeDescription);

        $themeScreenshotsFiles = $theme->getScreenshotsArray();
        if (count($themeScreenshotsFiles) > 0) {
          $themeScreenshotsURL = $theme->getScreenshotsURL();
          foreach ($themeScreenshotsFiles as $file) {
            array_push($themeScreenshotsListItems, TemplateCollector::assembly('<li class="gallery__item item"><img class="gallery__item-image item-image" src="{TEMPLATE_SCREENSHOT_URL}"></li>', [
              'TEMPLATE_SCREENSHOT_URL' => $themeScreenshotsURL . '/' . $file
            ]));
          }
        }
      }
    }

    if ($isExists) {
      foreach ($this->allowedMetadata as $enumMetadata) {
        /** @var string Имя ячейки метаданных */
        $metadataName = Template::getMetadataName($enumMetadata);

        if (array_key_exists($metadataName, $themeMetadata) || $enumMetadata === TemplateEnumMetadata::WEIGHT) {
          $getMetadataValue = function (Template $theme, array $themeMetadata, TemplateEnumMetadata $enumMetadata) {
            $metadataName = Template::getMetadataName($enumMetadata);
            
            if ($enumMetadata === TemplateEnumMetadata::WEIGHT) {
              $themeWeight = $this->CMSCore->urlp->getPath(2) !== 'repository' ? Template::get_weight($theme, TemplateEnumWeight::BYTES) : $themeMetadata[$metadataName];
              
              if ($themeWeight < 1024) {
                return sprintf('%s B', $themeWeight);
              }
              
              if ($themeWeight >= 1024 && $themeWeight < 1024 ^ 2) {
                return sprintf('%s KB', round($themeWeight / 1024, 2));
              }

              if ($themeWeight >= 1024 ^ 2 && $themeWeight < 1024 ^ 3) {
                return sprintf('%s MB', round($themeWeight / (1024 ^ 2), 2));
              }

              if ($themeWeight >= 1024 ^ 3) {
                return sprintf('%s GB', round($themeWeight / (1024 ^ 3), 2));
              }
            }

            if ($enumMetadata === TemplateEnumMetadata::DATETIME_CREATED_UNIX || $enumMetadata === TemplateEnumMetadata::DATETIME_UPDATED_UNIX) {
              return date('d.m.Y', $themeMetadata[$metadataName]);
            }

            return $themeMetadata[$metadataName] ?? '[???]';
          };

          /** @var string Заголовок ячейки метаданных */
          $metadataTitle = match ($enumMetadata) {
            TemplateEnumMetadata::AUTHOR_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_CODE_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_CODE_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_CODE_SERVER_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_CODE_SERVER_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_CODE_CLIENT_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_CODE_CLIENT_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_DESIGNER_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_DESIGNER_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_LAYOUT_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_LAYOUT_NAME_LABEL'),
            TemplateEnumMetadata::AUTHOR_SITE_LINK => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_SITE_LINK_LABEL'),
            TemplateEnumMetadata::AUTHOR_SOCIAL_VK_LINK => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_SOCIAL_VK_LINK_LABEL'),
            TemplateEnumMetadata::AUTHOR_SOCIAL_OK_LINK => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_AUTHOR_SOCIAL_OK_LINK_LABEL'),
            TemplateEnumMetadata::CATEGORY_NAME => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_CATEGORY_NAME_LABEL'),
            TemplateEnumMetadata::WEIGHT => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_SIZE_LABEL'),
            TemplateEnumMetadata::DATETIME_CREATED_UNIX => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_DATETIME_CREATED_UNIX_LABEL'),
            TemplateEnumMetadata::DATETIME_UPDATED_UNIX => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_DATETIME_UPDATED_UNIX_LABEL'),
            TemplateEnumMetadata::VERSION => $theme->CMSCore->locale::getDataValue($localeData, 'PAGE_TEMPLATE_VERSION_LABEL')
          };

          $metadataValueTemplate = match ($enumMetadata) {
            ModuleEnumMetadata::AUTHOR_SITE_LINK => '<li class="template__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            ModuleEnumMetadata::AUTHOR_SOCIAL_VK_LINK => '<li class="template__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            ModuleEnumMetadata::AUTHOR_SOCIAL_OK_LINK => '<li class="template__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> <a class="template__metadata-link metadata-link" href="{METADATA_VALUE}" target="_blank">{METADATA_VALUE}</a></li>',
            default => '<li class="template__metadata-item metadata-item"><b>{METADATA_TITLE}:</b> {METADATA_VALUE}</li>',
          };

          array_push($themeMetadataItemsTransformed, TemplateCollector::assembly($metadataValueTemplate, [
            'METADATA_TITLE' => $metadataTitle,
            'METADATA_VALUE' => $getMetadataValue($theme, $themeMetadata, $enumMetadata)
          ]));
        }
      }

      if (count($themeScreenshotsListItems) > 0) {
        $themeGalleryList = TemplateCollector::assembly('<ul class="gallery__list list list-reset">{TEMPLATE_GALLARY_LIST_ITEMS}</ul>', [
          'TEMPLATE_GALLARY_LIST_ITEMS' => implode($themeScreenshotsListItems)
        ]);
      } else {
        $themeGalleryList = '';
      }

      if (count($themeMetadataItemsTransformed) > 0) {
        $themeMetadataListTransformed = TemplateCollector::assembly('<ul class="template__metadata-list metadata-list list-reset">{METADATA_LIST}</ul>', [
          'METADATA_LIST' => implode($themeMetadataItemsTransformed)
        ]);
      } else {
        $themeMetadataListTransformed = $localeData['PAGE_TEMPLATE_METADATA_BLOCK_METADATA_NOT_FOUND_TITLE'];
      }

      $parsedown = new Parsedown();

      $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/template.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'template',
        'TEMPLATE_NAME' => $themeName,
        'TEMPLATE_TITLE' => $themeTitle,
        'TEMPLATE_DESCRIPTION' => $themeDescription,
        'TEMPLATE_GALLARY_LIST' => $themeGalleryList,
        'TEMPLATE_METADATA_LIST' => $themeMetadataListTransformed,
        'TEMPLATE_DOWNLOADED_STATUS' => $theme->existsFileMetadataJSON() ? 'downloaded' : 'not-downloaded',
        'TEMPLATE_INSTALLED_STATUS' => $theme->getName() === $this->CMSCore->configurator->getDatabaseEntryValue('base_template') ? 'installed' : 'not-installed'
      ]);
    } else {
      http_response_code(404);

      $pageError = new PageError($this->CMSCore, $this->page, 404);
      $pageError->assembly();
      $this->assembled = $pageError->assembled;
    }
  }
}
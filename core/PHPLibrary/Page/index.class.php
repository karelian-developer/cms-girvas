<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \DOMDocument as DOMDocument;
use \DOMXPath as DOMXPath;

class PageIndex implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  private array $metaOpenGraphAllowed = [
    'title',
    'description',
    'type',
    'url',
    'site_name'
  ];

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;

    $this->initMetaOpenGraph();
  }

  initMetaOpenGraph() : void
  {
    $document = new DOMDocument();
    libxml_use_internal_errors(true);
    $document->loadHTML($this->CMSCore->theme->core->assembled);
    libxml_use_internal_errors(false);

    $documentElement = (new DOMXPath($document))->query('/')->item(0);
    $headElement = $documentElement->getElementsByTagName('head')->item(0);
    if ($headElement !== null) {
      foreach ($this->metaOpenGraphAllowed as $metadata) {
        $metaElement = $document->createElement('meta');
        $metaElementContent = match ($metadata) {
          'title' => '{SITE_META_TITLE}',
          'description' => '{SITE_DESCRIPTION}',
          'type' => 'website',
          'url' => '{CMS_DOMAIN_LINK}'
          'site_name' => '{SITE_TITLE}'
        };

        $metaElement->setAttribute('property', 'og:' . $metadata);
        $metaElement->setAttribute('content', 'og:' . $metaElementContent);

        $headElement->appendChild($document->importNode($metaElement, true));
      }
    }
  }
  
  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/index.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $CMSCore = $this->CMSCore;
    $CMSTheme = $CMSCore->theme;
    $CMSLocale = $CMSCore->locale;

    $this->addRequiredStyles();

    $localeData = $CMSLocale->getData();
    $localeName = $CMSLocale->getName();

    $this->assembled = ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page.tpl', [
      'PAGE_NAME' => 'index',
      'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($CMSTheme, 'templates/page/index.tpl', [])
    ]);
  }
}
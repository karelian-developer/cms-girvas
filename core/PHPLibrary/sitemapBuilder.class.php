<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \DOMDocument as DOMDocument;

  /**
   * Сборщик карты сайта в XML-формате
   */
  final class SitemapBuilder {
    private DOMDocument $document;
    private SystemCore $system_core;
    private array $urls = [];
    public string $assembled = '';

    /**
     * __construct
     * 
     * @param SystemCore $system_core
     */
    public function __construct(SystemCore $system_core) {
      $this->system_core = $system_core;
      $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Добавить URL
     * 
     * @param string $loc
     * @param int $lastmod_unix
     * @param string $changefreq
     * @param float $priority
     * 
     * @return void
     */
    public function add_url(string $loc, int $lastmod_unix, string $changefreq, float $priority) : void {
      array_push($this->urls, [
        'loc' => $loc,
        'lastmod' => date('Y-m-d', $lastmod_unix),
        'changefreq' => $changefreq,
        'priority' => $priority
      ]);
    }

    /**
     * Сборка карты сайта
     * 
     * @return void
     */
    public function assembly() : void {
      $element_urlset = $this->document->createElement('urlset');
      $element_urlset_attribute_xmlns = $this->document->createAttribute('xmlns');
      $element_urlset_attribute_xmlns->value = 'https://www.sitemaps.org/schemas/sitemap/0.9';
      $element_urlset->appendChild($element_urlset_attribute_xmlns);

      foreach ($this->urls as $url) {
        $element_url = $this->document->createElement('url');
        $element_loc = $this->document->createElement('loc', $url['loc']);
        $element_lastmod = $this->document->createElement('lastmod', $url['lastmod']);
        $element_changefreq = $this->document->createElement('changefreq', $url['changefreq']);
        $element_priority = $this->document->createElement('priority', $url['priority']);

        $element_url->appendChild($element_loc);
        $element_url->appendChild($element_lastmod);
        $element_url->appendChild($element_changefreq);
        $element_url->appendChild($element_priority);

        $element_urlset->appendChild($element_url);
      }

      $this->document->appendChild($element_urlset);
      $this->assembled = $this->document->saveXML();
    }
  }
}

?>
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

namespace core\PHPLibrary;

use \DOMDocument as DOMDocument;

/**
 * Сборщик карты сайта в XML-формате
 */
final class SitemapBuilder
{
  private DOMDocument $document;
  private array $urls = [];
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {
    $this->setDocument();
  }

  /**
   * Назначить пустой документа
   * 
   * @return void
   */
  private function setDocument() : void
  {
    $this->document = new DOMDocument('1.0', 'UTF-8');
  }

  /**
   * Добавить URL
   * 
   * @param string $loc
   * @param int $lastmodUnix
   * @param string $changefreq
   * @param float $priority
   * 
   * @return void
   */
  public function addURL(string $loc, int $lastmodUnix, string $changefreq, float $priority) : void
  {
    $this->urls[] = [
      'loc' => $loc,
      'lastmod' => date('Y-m-d', $lastmodUnix),
      'changefreq' => $changefreq,
      'priority' => $priority
    ];
  }

  /**
   * Сборка карты сайта
   * 
   * @return void
   */
  public function assembly() : void
  {
    error_log(print_r($this->urls, true));

    $elementURLSet = $this->document->createElement('urlset');
    $elementURLSetAttributeXMLns = $this->document->createAttribute('xmlns');
    $elementURLSetAttributeXMLns->value = 'https://www.sitemaps.org/schemas/sitemap/0.9';
    $elementURLSet->appendChild($elementURLSetAttributeXMLns);

    foreach ($this->urls as $url) {
      $elementURL = $this->document->createElement('url');
      $elementLoc = $this->document->createElement('loc', $url['loc']);
      $elementLastmod = $this->document->createElement('lastmod', $url['lastmod']);
      $elementChangefreq = $this->document->createElement('changefreq', $url['changefreq']);
      $elementPriority = $this->document->createElement('priority', $url['priority']);

      $elementURL->appendChild($elementLoc);
      $elementURL->appendChild($elementLastmod);
      $elementURL->appendChild($elementChangefreq);
      $elementURL->appendChild($elementPriority);

      $elementURLSet->appendChild($elementURL);
    }

    $this->document->appendChild($elementURLSet);
    $this->assembled = $this->document->saveXML();
  }
}
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

final class FeedBuilder
{
  private DOMDocument $document;
  private array $items = [];
  private string $language = 'en-us';
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
    $this->document = new DOMDocument('1.0');
  }
  
  /**
   * Назначит язык RSS-ленты
   *
   * @param  mixed $localeName
   * @return void
   */
  public function setLanguage(string $localeName) : void
  {
    $this->language = str_replace('_', '-', strtolower($localeName));
  }
  
  /**
   * Получить язык RSS-ленты
   *
   * @return string
   */
  public function getLanguage() : string
  {
    return $this->language;
  }
  
  /**
   * Добавить запись в ленту
   *
   * @param  string $title
   * @param  string $description
   * @param  string $link
   * @param  int $updatedUnixTimestamp
   * 
   * @return void
   */
  public function addItem(string $title, string $description, string $link, int $updatedUnixTimestamp) : void
  {
    array_push($this->items, [
      'title' => $title,
      'description' => $description,
      'link' => $link,
      'pubdate' => date('D, d M Y H:i:s T', $updatedUnixTimestamp)
    ]);
  }
  
  /**
   * Сборка XML-структуры RSS-канала
   *
   * @return void
   */
  public function assembly() : void
  {
    $siteTitle = $this->CMSCore->configurator->existsDatabaseEntryValue('base_site_title') ? $this->CMSCore->configurator->getDatabaseEntryValue('base_site_title') : $this->CMSCore::CMS_TITLE . ' ' .  $this->CMSCore::CMS_VERSION;
    $siteDescription = $this->CMSCore->configurator->existsDatabaseEntryValue('seo_site_description') ? $this->CMSCore->configurator->getDatabaseEntryValue('seo_site_description') : 'Description is not exists';
    $siteLink = 'https://' . $this->CMSCore->configurator->get('domain');

    $elementRSS = $this->document->createElement('rss');
    $elementRSSAttributeVersion = $this->document->createAttribute('version');
    $elementRSSAttributeVersion->value = '2.0';

    $elementRSS->appendChild($elementRSSAttributeVersion);

    $elementChannel = $this->document->createElement('channel');
    $elementChannelTitle = $this->document->createElement('title', $siteTitle);
    $elementChannelLink = $this->document->createElement('link', $siteLink);
    $elementChannelDescription = $this->document->createElement('description', $siteDescription);
    $elementChannelLanguage = $this->document->createElement('language', $this->getLanguage());
    $elementChannelLastBuildDate = $this->document->createElement('lastBuildDate', date('D, d M Y H:i:s T', time()));
    $elementChannelDocs = $this->document->createElement('docs', 'http://blogs.law.harvard.edu/tech/rss');
    $elementChannelGenerator = $this->document->createElement('generator', 'CMS GIRVAS: RSS Builder');

    $elementChannel->appendChild($elementChannelTitle);
    $elementChannel->appendChild($elementChannelLink);
    $elementChannel->appendChild($elementChannelDescription);
    $elementChannel->appendChild($elementChannelLastBuildDate);
    $elementChannel->appendChild($elementChannelDocs);
    $elementChannel->appendChild($elementChannelGenerator);

    $items = $this->items;
    usort($items, function ($a, $b) {
      $aPubdateUnix = strtotime($a['pubdate']);
      $bPubdateUnix = strtotime($b['pubdate']);

      if ($aPubdateUnix == $bPubdateUnix) {
        return 0;
      }

      return ($aPubdateUnix > $bPubdateUnix) ? -1 : 1;
    });

    $elementChannelPubdate = $this->document->createElement('pubDate', $items[0]['pubdate']);
    $elementChannel->appendChild($elementChannelPubdate);

    unset($items);

    foreach ($this->items as $item) {
      $elementItemDescriptionCData = $this->document->createCDATASection($item['description']);

      $elementItemTitle = $this->document->createElement('title', $item['title']);
      $elementItemDescription = $this->document->createElement('description');
      $elementItemLink = $this->document->createElement('link', $item['link']);
      $elementItemPubdate = $this->document->createElement('pubDate', $item['pubdate']);

      $elementItemDescription->appendChild($elementItemDescriptionCData);

      $elementItem = $this->document->createElement('item');
      $elementItem->appendChild($elementItemTitle);
      $elementItem->appendChild($elementItemDescription);
      $elementItem->appendChild($elementItemLink);
      $elementItem->appendChild($elementItemPubdate);
      $elementChannel->appendChild($elementItem);
    }

    $elementRSS->appendChild($elementChannel);
    $this->document->appendChild($elementRSS);

    $this->assembled = $this->document->saveXML();
  }
}
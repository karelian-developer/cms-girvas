<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \DOMDocument as DOMDocument;

  final class WebChannelBuilder {
    private DOMDocument $document;
    private SystemCore $CMSCore;
    private array $items = [];
    private string $language = 'en-us';
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
      $this->document = new DOMDocument('1.0');
    }
    
    /**
     * Назначит язык RSS-ленты
     *
     * @param  mixed $localeName
     * @return void
     */
    public function set_language(string $localeName) : void {
      $this->language = str_replace('_', '-', strtolower($localeName));
    }
    
    /**
     * Получить язык RSS-ленты
     *
     * @return string
     */
    public function get_language() : string {
      return $this->language;
    }
    
    /**
     * Добавить запись в ленту
     *
     * @param  int $id
     * @param  string $title
     * @param  string $description
     * @param  string $link
     * @param  int $updatedUnixTimestamp
     * 
     * @return void
     */
    public function add_item(string $title, string $description, string $link, int $updatedUnixTimestamp) : void {
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
    public function assembly() : void {
      $siteTitle = ($this->CMSCore->configurator->exists_database_entry_value('base_site_title')) ? $this->CMSCore->configurator->get_database_entry_value('base_site_title') : sprintf('%s %s', $this->CMSCore::CMS_TITLE, $this->CMSCore::CMS_VERSION);
      $siteDescription = ($this->CMSCore->configurator->exists_database_entry_value('seo_site_description')) ? $this->CMSCore->configurator->get_database_entry_value('seo_site_description') : 'Description is not exists';
      $siteLink = sprintf('https://%s', $this->CMSCore->configurator->get('domain'));

      $elementRSS = $this->document->createElement('rss');
      $elementRSSAttributeVersion = $this->document->createAttribute('version');
      $elementRSSAttributeVersion->value = '2.0';

      $elementRSS->appendChild($elementRSSAttributeVersion);

      $elementChannel = $this->document->createElement('channel');
      $elementChannelTitle = $this->document->createElement('title', $siteTitle);
      $elementChannelLink = $this->document->createElement('link', $siteLink);
      $elementChannelDescription = $this->document->createElement('description', $siteDescription);
      $elementChannelLanguage = $this->document->createElement('language', $this->get_language());
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
}
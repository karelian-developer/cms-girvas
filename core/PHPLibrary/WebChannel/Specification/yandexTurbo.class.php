<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\WebChannel\Specification {
  use \DOMElement as DOMElement;
  use \DOMImplementation as DOMImplementation;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\WebChannel\Builder as WebChannelBuilder;
  use \core\PHPLibrary\WebChannel\InterfaceSpecification as InterfaceSpecification;

  class YandexTurbo implements InterfaceSpecification {
    const TYPE_NAME = 'yandex-turbo';
    const TYPE_TITLE = 'Yandex Turbo';

    private SystemCore $system_core;
    private WebChannelBuilder $builder;
    public string $title;
    public string $description;
    public string $link;
    public string $language;
    public array $items = [];

    public function __construct(SystemCore $system_core, WebChannelBuilder $web_channel_builder) {
      $this->system_core = $system_core;
      $this->builder = $web_channel_builder;
    }

    public function set_title(string $value) : void {
      $this->title = $value;
    }

    public function set_description(string $value) : void {
      $this->description = $value;
    }

    public function set_link(string $value) : void {
      $this->link = $value;
    }

    public function set_language(string $value) : void {
      preg_match('/([a-z]+)\_[A-Z]+/', $value, $matches);
      $this->language = $matches[1];
    }

    public function add_item(array $data) : void {
      array_push($this->items, [
        'title' => $data['title'],
        'description' => $data['description'],
        'content' => $data['content'],
        'preview_url' => $data['preview_url'],
        'link' => $data['link'],
        'pubdate' => date('D, d M Y H:i:s O', $data['pubdate']),
        'author' => $data['author']
      ]);
    }

    public function get_title() : string {
      return $this->title;
    }

    public function get_description() : string {
      return $this->description;
    }

    public function get_link() : string {
      return $this->link;
    }

    public function get_language() : string {
      return $this->language;
    }

    public function get_items() : array {
      return $this->items;
    }

    public function assembly() : void {
      $rss_element = $this->builder->document->createElement('rss');
      $rss_element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:yandex', 'http://news.yandex.ru');
      $rss_element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:media', 'http://search.yahoo.com/mrss/');
      $rss_element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:turbo', 'http://turbo.yandex.ru');
      $rss_element->setAttribute('version', '2.0');

      $channel_element = $this->builder->document->createElement('channel');
      $channel_title_element = $this->builder->document->createElement('title', $this->get_title());
      $channel_link_element = $this->builder->document->createElement('link', $this->system_core->get_cms_link());
      $channel_description_element = $this->builder->document->createElement('description', $this->get_description());
      $channel_language_element = $this->builder->document->createElement('language', $this->get_language());
      $channel_turbo_analytics_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:analytics');
      $channel_turbo_adnetwork_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:adNetwork');

      $channel_element->appendChild($channel_title_element);
      $channel_element->appendChild($channel_link_element);
      $channel_element->appendChild($channel_description_element);
      $channel_element->appendChild($channel_language_element);
      //$channel_element->appendChild($channel_turbo_analytics_element);
      //$channel_element->appendChild($channel_turbo_adnetwork_element);

      if (count($this->items) > 0) {
        foreach ($this->items as $item) {
          $item_element = $this->builder->document->createElement('item');
          $item_element->setAttribute('turbo', 'true');
          
          $item_turbo_extended_html_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:extendedHtml', 'true');
          $item_link_element = $this->builder->document->createElement('link', $item['link']);
          //$item_turbo_source_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:source', 'true');
          //$item_turbo_topic_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:topic', 'true');
          $item_pudate_element = $this->builder->document->createElement('pubDate', $item['pubdate']);
          $item_author_element = $this->builder->document->createElement('author', $item['author']);

          $item_yandex_related_element = $this->builder->document->createElementNS('http://news.yandex.ru', 'yandex:related');
          $item_turbo_content_element = $this->builder->document->createElementNS('http://turbo.yandex.ru', 'turbo:content');
          
          $item_content_header = sprintf('<header><h1>%s</h1>', $item['title']);
          
          if ($item['preview_url'] != '') {
            $item_content_header .= sprintf('<figure><img src="%s"></figure>', $item['preview_url']);
          }

          $item_content_header .= sprintf('</header>', $item['preview_url']);

          $item_turbo_content_cdata_element = $this->builder->document->createCDATASection($item_content_header . $item['content']);

          $item_turbo_content_element->appendChild($item_turbo_content_cdata_element);

          $item_element->appendChild($item_turbo_extended_html_element);
          $item_element->appendChild($item_link_element);
          //$item_element->appendChild($item_turbo_source_element);
          //$item_element->appendChild($item_turbo_topic_element);
          $item_element->appendChild($item_pudate_element);
          $item_element->appendChild($item_author_element);
          $item_element->appendChild($item_yandex_related_element);
          $item_element->appendChild($item_turbo_content_element);

          $channel_element->appendChild($item_element);
        }
      }

      $rss_element->appendChild($channel_element);
      $this->builder->document->appendChild($rss_element);

      $this->builder->assembled = $this->builder->document->saveXML();
    }
  }
}

?>
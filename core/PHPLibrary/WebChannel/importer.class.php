<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\WebChannel {
  use \DOMDocument as DOMDocument;
  use \SimpleXMLElement as SimpleXMLElement;
  use \XMLReader as XMLReader;
  use \core\PHPLibrary\SystemCore as SystemCore;

  final class Importer {
    public string $web_channel_link = '';

    public function __construct(SystemCore $system_core, string $web_channel_link) {
      $this->set_web_channel_link($web_channel_link);
    }

    public function get(array $stream_context = []) : SimpleXMLElement|bool {
      $web_channel_link = $this->get_web_channel_link();

      $assertion = file_get_contents($web_channel_link, false, stream_context_create($stream_context));
      return simplexml_load_string($assertion);
    }

    private function set_web_channel_link(string $link) : void {
      $this->web_channel_link = $link;
    }

    public function get_web_channel_link() : string {
      return $this->web_channel_link;
    }

  }
}

?>
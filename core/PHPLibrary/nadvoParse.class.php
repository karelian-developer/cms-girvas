<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link  https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

class NadvoParse
{
  private const PATTERNS = [
    'header' => '/^(#{1,6})\s(.+)/m',
    'bold' => '/\*\*(.+?)\*\*|__(.+?)__/s',
    'italic' => '/\*(.+?)\*|_(.+?)_/s',
    'link' => '/\[([^\[\]]+)\]\(\s*(\S+)\s*\)/',
    'image' => '/!\[(.+?)\]\((.+?)\)/',
    'text' => '/[^\*_!\[\]]+/s'
  ];

  public function __construct()
  {}

  public function parse(string $markdown) : string
  {
    $markdown = $this->parseBlocks($markdown);
    return $this->parseInlineElements($markdown);
  }

  private function parseBlocks(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $html = '';
    $currentParagraph = '';

    foreach ($lines as $line) {
      if (preg_match('/^(#{1,6})\s+(.+)/', $line, $matches)) {
        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }
        $html .= '<h' . strlen($matches[1]) . '>' . $matches[2] . '</h' . strlen($matches[1]) . '>';
      } elseif (trim($line) === '') {
        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }
      } else {
        $currentParagraph .= $line . ' ';
      }
    }

    if (!empty($currentParagraph)) {
      $html .= '<p>' . $currentParagraph . '</p>';
    }

    return $html;
  }

  private function parseInlineElements(string $html) : string
  {
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
    $html = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $html);
    
    $html = preg_replace('/\*([^*]+)\*/s', '<em>$1</em>', $html);
    $html = preg_replace('/_([^_]+)_/s', '<em>$1</em>', $html);

    $html = preg_replace_callback(
      '/!\[(.+?)\]\((.+?)\)/',
      function($matches) {
        return '<img src="' . htmlspecialchars($matches[2]) . '" alt="' . htmlspecialchars($matches[1]) . '">';
      },
      $html
    );

    $html = preg_replace_callback(
      '/\[([^\[\]]+)\]\(\s*(\S+)\s*\)/',
      function($matches) {
        return '<a href="' . htmlspecialchars($matches[2]) . '">' . htmlspecialchars($matches[1]) . '</a>';
      },
      $html
    );
    
    return $html;
  }
}
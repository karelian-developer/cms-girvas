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
    // Предварительная очистка текста
    $markdown = $this->preprocessText($markdown);
    
    $lines = explode("\n", $markdown);
    $html = '';
    $currentParagraph = '';

    foreach ($lines as $line) {
      $line = trim($line);
      
      if (preg_match('/^(#{1,6})\s+(.+)/', $line, $matches)) {
        if (!empty($currentParagraph)) {
          $html .= $this->parseParagraph($currentParagraph);
          $currentParagraph = '';
        }
        $html .= $this->parseHeader($line);
      } elseif (empty($line)) {
        if (!empty($currentParagraph)) {
          $html .= $this->parseParagraph($currentParagraph);
          $currentParagraph = '';
        }
      } else {
        $currentParagraph .= $line . ' ';
      }
    }

    if (!empty($currentParagraph)) {
      $html .= $this->parseParagraph($currentParagraph);
    }

    return $html;
  }

  private function preprocessText(string $text) : string
  {
    $text = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $text);
    
    return str_replace(["\r\n", "\r"], "\n", $text);
  }

  private function parseHeader(string $line) : string
  {
    preg_match('/^(#{1,6})\s+(.+)/', $line, $matches);
    $level = strlen($matches[1]);
    $content = $matches[2];
    
    return "<h$level>" . $this->parseInline($content) . "</h$level>";
  }

  private function parseParagraph(string $text) : string
  {
    return '<p>' . $this->parseInline($text) . '</p>';
  }

  private function parseInline(string $text) : string
  {
    // Обработка ссылок
    $text = preg_replace_callback(
      '/\[([^]]+)]\(([^)]+)\)/',
      function ($matches) {
        $url = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
        return "<a href=\"$url\">$title</a>";
      },
      $text
    );

    // Обработка жирного текста
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    
    // Обработка курсива
    $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);

    return htmlspecialchars_decode($text);
  }
}
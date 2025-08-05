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
    'table' => '/^(\|.+)+\|$/m',
    'text' => '/[^\*_!\[\]]+/s'
  ];

  public function __construct()
  {}

  public function parse(string $markdown) : string
  {
    $markdown = $this->parseTables($markdown);
    $markdown = $this->parseBlocks($markdown);
    return $this->parseInlineElements($markdown);
  }

  private function parseTables(string $markdown) : string
  {
    return preg_replace_callback(
      '/^([^\n\|]+\|[^\n]+)\n([\-:\| ]+)+\n((?:[^\n]+\|.+\n?)+)/m',
      function($matches) {
        $headers = explode('|', trim($matches[1], "| \t\n\r\0\x0B"));
        $aligns = $this->parseTableAligns(trim($matches[2], "| \t\n\r\0\x0B"));
        $rows = explode("\n", trim($matches[3]));
        
        $html = "<table>\n<thead>\n<tr>\n";
        
        // Заголовки таблицы
        foreach ($headers as $i => $header) {
          $align = $aligns[$i] ?? '';
          $html .= "  <th style=\"text-align:$align\">" . trim($header) . "</th>\n";
        }
        
        $html .= "</tr>\n</thead>\n<tbody>\n";
        
        // Строки таблицы
        foreach ($rows as $row) {
          $cells = explode('|', trim($row, "| \t\n\r\0\x0B"));
          $html .= "<tr>\n";
          
          foreach ($cells as $i => $cell) {
            $align = $aligns[$i] ?? '';
            $html .= "  <td style=\"text-align:$align\">" . trim($cell) . "</td>\n";
          }
          
          $html .= "</tr>\n";
        }
        
        return $html . "</tbody>\n</table>";
      },
      $markdown
    );
  }

  private function parseTableAligns(string $alignRow) : array
  {
    $aligns = [];
    $parts = explode('|', trim($alignRow, "| "));
    
    foreach ($parts as $part) {
      $part = trim($part);
      if (str_starts_with($part, ':') && str_ends_with($part, ':')) {
        $aligns[] = 'center';
      } elseif (str_ends_with($part, ':')) {
        $aligns[] = 'right';
      } elseif (str_starts_with($part, ':')) {
        $aligns[] = 'left';
      } else {
        $aligns[] = '';
      }
    }
    
    return $aligns;
  }

  private function parseBlocks(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $html = '';
    $currentParagraph = '';
    $inTable = false;

    foreach ($lines as $line) {
      if (str_starts_with(trim($line), '|')) {
        if (!$inTable) {
          if (!empty($currentParagraph)) {
            $html .= '<p>' . $currentParagraph . '</p>';
            $currentParagraph = '';
          }
          $inTable = true;
        }
        continue;
      }

      $inTable = false;

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
    
    $html = preg_replace('/\~\~(.+?)\~\~/s', '<u>$1</u>', $html);

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
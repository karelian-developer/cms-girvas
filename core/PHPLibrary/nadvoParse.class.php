<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link    https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
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
    // Нормализация кодировки перед обработкой
    $markdown = $this->normalizeEncoding($markdown);
    
    $lines = preg_split('/\R/', $markdown);
    $HTML = '';
    $currentParagraph = '';

    foreach ($lines as $line) {
      if (preg_match('/^(#{1,6})\s(.+)/', $line, $matches)) {
        if ($currentParagraph !== '') {
          $HTML .= $this->parseParagraph($currentParagraph);
          $currentParagraph = '';
        }
        $HTML .= $this->parseHeader($line);
      } elseif (trim($line) === '') {
        if ($currentParagraph !== '') {
          $HTML .= $this->parseParagraph($currentParagraph);
          $currentParagraph = '';
        }
      } else {
        $currentParagraph .= $line . "\n";
      }
    }

    if ($currentParagraph !== '') {
      $HTML .= $this->parseParagraph($currentParagraph);
    }

    return $HTML;
  }

  private function normalizeEncoding(string $text) : string
  {
    if (mb_detect_encoding($text, 'UTF-8', true) === 'UTF-8') {
      return $text;
    }

    // Проверяем кодировку
    $encoding = mb_detect_encoding($text, ['Windows-1251', 'CP866'], true);
    
    if ($encoding) {
      $text = mb_convert_encoding($text, 'UTF-8', $encoding);
    }

    // Удаляем битые символы
    return preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $text);
  }

  private function tokenize(string $markdown) : array
  {
    $tokens = [];
    $offset = 0;
    $length = mb_strlen($markdown, 'UTF-8');

    while ($offset < $length) {
      $matched = false;
      $substr = mb_substr($markdown, $offset, $length - $offset, 'UTF-8');

      foreach (self::PATTERNS as $type => $pattern) {
        if (preg_match($pattern, $substr, $matches, PREG_OFFSET_CAPTURE)) {
          $matchText = $matches[0][0];
          $matchLength = mb_strlen($matchText, 'UTF-8');
          $matchPos = $offset + $matches[0][1];

          $token = [
            'type' => $type,
            'value' => $matchText,
            'position' => $matchPos,
          ];

          if ($type === 'header') {
            $token['level'] = mb_strlen($matches[1][0], 'UTF-8');
            $token['content'] = $matches[2][0];
          } elseif (in_array($type, ['bold', 'italic', 'link', 'image'])) {
            // Для link и image используем первую capture-группу
            $contentKey = ($type === 'link' || $type === 'image') ? 1 : 0;
            $token['content'] = $matches[$contentKey + 1][0] ?? $matchText;
          }

          $tokens[] = $token;
          $offset += $matchLength;
          $matched = true;
          break;
        }
      }

      if (!$matched) {
        $char = mb_substr($markdown, $offset, 1, 'UTF-8');
        $tokens[] = [
          'type' => 'text',
          'value' => $char,
          'position' => $offset
        ];
        $offset++;
      }
    }

    return $tokens;
  }

  private function parseTokens(array $tokens) : array
  {
    $AST = [];

    while (!empty($tokens)) {
      $token = array_shift($tokens);

      switch ($token['type']) {
        case 'header':
          $AST[] = [
            'type' => 'header',
            'level' => $token['level'],
            'children' => $this->parseTokens($this->tokenize($token['content'])),
          ];
          break;
        case 'bold':
        case 'italic':
          $AST[] = [
            'type' => $token['type'],
            'children' => $this->parseTokens($this->tokenize($token['content'])),
          ];
          break;
        case 'link':
          $AST[] = [
            'type' => 'link',
            'url' => $this->sanitizeUrl($matches[2][0] ?? ''),
            'children' => $this->parseTokens($this->tokenize($token['content'])),
          ];
          break;
        case 'image':
          $AST[] = [
            'type' => 'image',
            'url' => $this->sanitizeUrl($matches[2][0] ?? ''),
            'alt' => $token['content'],
          ];
          break;
        default:
          $AST[] = ['type' => 'text', 'value' => $token['value']];
      }
    }

    return $AST;
  }

  private function sanitizeUrl(string $url) : string
  {
    $url = trim($url);
    $url = preg_replace('/[\s<>"\']/', '', $url);
    
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
      $url = 'https://' . $url;
    }

    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
  }

  private function ASTToHTML(array $AST) : string
  {
    $HTML = '';

    foreach ($AST as $node) {
      switch ($node['type']) {
        case 'header':
          $level = min(max($node['level'], 1), 6);
          $HTML .= '<h' . $level . '>' . $this->ASTToHTML($node['children']) . '</h' . $level . '>';
          break;
        case 'bold':
          $HTML .= '<strong>' . $this->ASTToHTML($node['children']) . '</strong>';
          break;
        case 'italic':
          $HTML .= '<em>' . $this->ASTToHTML($node['children']) . '</em>';
          break;
        case 'link':
          $HTML .= '<a href="' . $node['url'] . '">' . $this->ASTToHTML($node['children']) . '</a>';
          break;
        case 'image':
          $HTML .= '<img src="' . $node['url'] . '" alt="' . htmlspecialchars($node['alt']) . '">';
          break;
        default:
          $HTML .= htmlspecialchars($node['value'], ENT_QUOTES, 'UTF-8');
      }
    }

    return $HTML;
  }

  private function parseHeader(string $line) : string
  {
    preg_match('/^(#{1,6})\s(.+)/', $line, $matches);
    $level = mb_strlen($matches[1], 'UTF-8');
    $content = $matches[2];
    $tokens = $this->tokenize($content);
    $AST = $this->parseTokens($tokens);

    return sprintf('<h%d>%s</h%d>', $level, $this->ASTToHTML($AST), $level);
  }

  private function parseParagraph(string $text) : string
  {
    $text = trim($text);
    $text = preg_replace('/([^\n])\n([^\n])/', '$1<br>$2', $text);

    $tokens = $this->tokenize($text);
    $AST = $this->parseTokens($tokens);

    return '<p>' . $this->ASTToHTML($AST) . '</p>';
  }
}
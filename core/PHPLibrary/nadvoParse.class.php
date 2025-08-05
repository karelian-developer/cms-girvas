<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

class NadvoParse
{
  private const PATTERNS = [
    'header' => '/^(#{1,6})\s(.+)/m',
    'bold' => '/\*\*(.+?)\*\*|__(.+?)__/s',
    'italic' => '/\*(.+?)\*|_(.+?)_/s',
    'link' => '/\[(.+?)\]\((.+?)\)/',
    'image' => '/!\[(.+?)\]\((.+?)\)/',
    'text' => '/[^\*_!\[\]]+/s'
  ];

  public function __construct()
  {}

  public function parse(string $markdown) : string
  {
    $lines = preg_split('/\R/', $markdown); // Разбиваем текст на строки
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

  private function tokenize(string $markdown) : string
  {
    $tokens = [];
    $offset = 0;

    while ($offset < strlen($markdown)) {
      foreach (self::PATTERNS as $type => $pattern) {
        if (preg_match($pattern, $markdown, $matches, 0, $offset)) {
          if ($type === 'HEADER') {
            $tokens[] = [
              'type' => 'header',
              'level' => strlen($matches[1]),
              'content' => $matches[2],
              'position' => $offset,
            ];
          } else {
            $tokens[] = [
              'type' => $type,
              'value' => $matches[0],
              'content' => $matches[1] ?? null,
              'position' => $offset,
            ];
          }

          $offset += strlen($matches[0]);
          continue 2;
        }
      }

      $offset++;
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
          $AST[] = [
            'type' => 'bold',
            'children' => $this->parseTokens($this->tokenize($token['content'])),
          ];
          break;
        case 'italic':
          $AST[] = [
            'type' => 'italic',
            'children' => $this->parseTokens($this->tokenize($token['content'])),
          ];
          break;
        case 'link':
          $AST[] = [
            'type' => 'link',
            'url' => $token['content'][2],
            'children' => $this->parseTokens($this->tokenize($token['content'][1])),
          ];
          break;
        default:
          $AST[] = ['type' => 'text', 'value' => $token['value']];
      }
    }

    return $AST;
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
          $HTML .= '<a href="' . htmlspecialchars($node['url']) . '">' 
            . $this->ASTToHTML($node['children']) . '</a>';
          break;
        default:
          $HTML .= htmlspecialchars($node['value']);
      }
    }

    return $HTML;
  }

  private function parseHeader(string $line): string {
    preg_match('/^(#{1,6})\s(.+)/', $line, $matches);
    $level = strlen($matches[1]);
    $content = $matches[2];
    $tokens = $this->tokenize($content);
    $AST = $this->parseTokens($tokens);

    return sprintf('<h%d>%s</h%d>', $level, $this->ASTToHTML($AST), $level);
  }

  private function parseParagraph(string $text): string {
    $text = preg_replace('/(?<!\n)\n(?!\n)/', '<br>', trim($text));
    $tokens = $this->tokenize($text);
    $AST = $this->parseTokens($tokens);

    return '<p>' . $this->ASTToHTML($AST) . '</p>';
  }
}
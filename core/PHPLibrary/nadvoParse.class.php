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
    'underline' => '/\~\~(.+?)\~\~/s',
    'link' => '/\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'image' => '/!\[(.+?)?\]\((.+?)\)/',
    'video' => '/!\[video\]\((.+?)\)/',
    'audio' => '/!\[audio\]\((.+?)\)/',
    'table' => '/(\|.+)+\|/m',
    'quote' => '/^(\>+)\s?(.*)$/m',
    'code_block' => '/\`\`\`([a-z]*)\R([\s\S]*?)\R\`\`\`/',
    'inline_code' => '/(?<!`)`([^`]+)`(?!`)/',
    'text' => '/[^\*_!\[\]]+/s',
    'unordered_list' => '/^([*+-])\s+(.+)$/m',
    'ordered_list' => '/^(\d+)\.\s+(.+)$/m',
    'list_item' => '/^([*+-]|\d+\.)\s+(.+)$/m',
    'dangerous_tags' => '/<\?(?:php)?.*?\?>|<(script|iframe)[^>]*>.*?<\/\1>/is'
  ];

  public function __construct()
  {}

  public function parse(string $markdown) : string
  {
    $markdown = $this->sanitizeInput($markdown);
    $markdown = $this->parseCodeBlocks($markdown);
    $markdown = $this->parseQuotes($markdown);
    $markdown = $this->parseLists($markdown);
    $markdown = $this->parseTables($markdown);
    $markdown = $this->parseBlocks($markdown);
    return $this->parseInlineElements($markdown);
  }

  private function sanitizeInput(string $markdown) : string
  {
    $markdown = preg_replace(self::PATTERNS['dangerous_tags'], '', $markdown);
    $markdown = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8', false);
    
    return $markdown;
  }

  private function parseLists(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $result = [];
    $listStack = [];
    $currentIndent = 0;
    
    foreach ($lines as $line) {
      if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.+)$/', $line, $matches)) {
        $indent = strlen($matches[1]);
        $isOrdered = is_numeric(substr($matches[2], 0, 1));
        $content = $matches[3];
        
        // Закрываем лишние уровни
        while ($currentIndent > $indent && !empty($listStack)) {
          $result[] = str_repeat('</li></' . array_pop($listStack) . '>', $currentIndent - $indent);
          $currentIndent = $indent;
        }
        
        // Открываем новые уровни
        if ($indent > $currentIndent) {
          $type = $isOrdered ? 'ol' : 'ul';
          $result[] = str_repeat("<{$type}><li>", $indent - $currentIndent);
          $listStack = array_merge($listStack, array_fill(0, $indent - $currentIndent, $type));
          $currentIndent = $indent;
        } elseif (!empty($listStack)) {
          $result[] = '</li><li>';
        } else {
          $type = $isOrdered ? 'ol' : 'ul';
          $result[] = "<{$type}><li>";
          $listStack[] = $type;
          $currentIndent = 1;
        }
        
        $result[] = $this->parseInlineElements($matches[3]);
      } else {
        // Закрываем все списки для обычных строк
        if (!empty($listStack)) {
          $result[] = str_repeat('</li></' . array_pop($listStack) . '>', $currentIndent);
          $currentIndent = 0;
        }

        $result[] = $line;
      }
    }
    
    // Закрываем все оставшиеся списки
    if (!empty($listStack)) {
      $result[] = str_repeat('</li></' . array_pop($listStack) . '>', $currentIndent);
    }
    
    return implode("\n", $result);
  }

  private function parseTables(string $markdown) : string
  {
    // Разбиваем текст на строки
    $lines = explode("\n", $markdown);
    $result = [];
    $tableLines = [];
    $inTable = false;

    foreach ($lines as $line) {
      if (preg_match(self::PATTERNS['table'], $line)) {
        if (!$inTable) {
          $inTable = true;
        }

        $tableLines[] = $line;
      } else {
        if ($inTable) {
          $result[] = $this->buildTable($tableLines);
          $tableLines = [];
          $inTable = false;
        }

        $result[] = $line;
      }
    }

    if ($inTable) {
      $result[] = $this->buildTable($tableLines);
    }

    return implode("\n", $result);
  }

  private function buildTable(array $lines) : string
  {
    if (count($lines) < 2) {
      return implode("\n", $lines); // Недостаточно строк для таблицы
    }

    $headers = $this->parseTableRow($lines[0]);
    $aligns = $this->parseTableAligns($lines[1]);
    $rows = [];

    for ($i = 2; $i < count($lines); $i++) {
      $rows[] = $this->parseTableRow($lines[$i]);
    }

    // Генерация HTML
    $html = "<table>\n<thead>\n<tr>\n";
    
    // Заголовки
    foreach ($headers as $i => $header) {
      $align = $aligns[$i] ?? '';
      $html .= "<th style=\"text-align:$align\">" . trim($this->parseInlineElements($header)) . "</th>\n";
    }
    
    $html .= "</tr>\n</thead>\n<tbody>\n";
    
    // Строки
    foreach ($rows as $row) {
      $html .= "<tr>\n";

      foreach ($row as $i => $cell) {
        $align = $aligns[$i] ?? '';
        $html .= "<td style=\"text-align:$align\">" . trim($this->parseInlineElements($cell)) . "</td>\n";
      }

      $html .= "</tr>\n";
    }
    
    return $html . "</tbody>\n</table>";
  }

  private function parseTableRow(string $line) : array
  {
    $cells = explode('|', $line);

    if (count($cells) > 0 && trim($cells[0]) === '') {
      array_shift($cells);
    }

    if (count($cells) > 0 && trim($cells[count($cells)-1]) === '') {
      array_pop($cells);
    }

    return $cells;
  }

  private function parseTableAligns(string $line) : array
  {
    $aligns = [];
    $cells = $this->parseTableRow($line);
    
    foreach ($cells as $cell) {
      $cell = trim($cell);

      if (preg_match('/^:[-]+:$/', $cell)) {
        $aligns[] = 'center';
      } elseif (preg_match('/^[-]+:$/', $cell)) {
        $aligns[] = 'right';
      } elseif (preg_match('/^:[-]+$/', $cell)) {
        $aligns[] = 'left';
      } else {
        $aligns[] = '';
      }
    }
    
    return $aligns;
  }

  private function parseQuotes(string $markdown) : string
  {
    // Сначала восстанавливаем > из &gt; в цитатах
    $markdown = preg_replace_callback(
      '/^(&gt;)+/m',
      function($matches) {
        // Количество &gt; в совпадении
        $count = substr_count($matches[0], '&gt;');
        // Возвращаем соответствующее количество >
        return str_repeat('>', $count);
      },
      $markdown
    );
    
    // Обрабатываем вложенные цитаты
    $lines = explode("\n", $markdown);
    $result = [];
    $quoteStack = [];
    
    foreach ($lines as $line) {
      if (preg_match(self::PATTERNS['quote'], $line, $matches)) {
        $level = strlen($matches[1]);
        $content = trim($matches[2]);
        
        // Закрываем лишние уровни
        while (!empty($quoteStack) && count($quoteStack) > $level) {
          $result[] = '</blockquote>';
          array_pop($quoteStack);
        }
        
        // Открываем новые уровни
        while (count($quoteStack) < $level) {
          $result[] = '<blockquote>';
          $quoteStack[] = true;
        }
        
        // Добавляем содержимое
        if ($content !== '') {
          $result[] = '<p>' . $content . '</p>';
        }
      } else {
        // Закрываем все цитаты для обычных строк
        while (!empty($quoteStack)) {
          $result[] = '</blockquote>';
          array_pop($quoteStack);
        }
        
        // Добавляем саму строку
        $result[] = $line;
      }
    }
    
    // Закрываем все оставшиеся цитаты
    while (!empty($quoteStack)) {
      $result[] = '</blockquote>';
      array_pop($quoteStack);
    }
    
    return implode("\n", $result);
  }

  private function parseCodeBlocks(string $markdown) : string
  {
    $markdown = preg_replace_callback(
      self::PATTERNS['code_block'],
      function($matches) {
        $language = trim($matches[1]);
        $code = htmlspecialchars(trim($matches[2]), ENT_NOQUOTES);
        
        if ($language) {
          return '<pre><code class="language-' . $language . '">' . $code . '</code></pre>';
        } else {
          return '<pre><code>' . $code . '</code></pre>';
        }
      },
      $markdown
    );

    $markdown = preg_replace_callback(
      self::PATTERNS['inline_code'],
      function($matches) {
        $code = htmlspecialchars(trim($matches[1]), ENT_NOQUOTES);
        return '<code>' . $code . '</code>';
      },
      $markdown
    );

    return $markdown;
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
    $html = preg_replace_callback(
      self::PATTERNS['video'],
      function($matches) {
        $url = htmlspecialchars(trim($matches[1]));
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        
        return '<div class="video-container"><video controls><source src="' . $url . '" type="video/' . $extension . '">' .
               'Ваш браузер не поддерживает работу с видео.</video></div>';
      },
      $html
    );

    $html = preg_replace_callback(
      self::PATTERNS['image'],
      function($matches) {
        $alt = htmlspecialchars(trim($matches[1]), ENT_QUOTES);
        $src = htmlspecialchars(trim($matches[2]), ENT_QUOTES);
        $src = str_replace('_', '&#95;', $src);
        $attrs = [];
        
        if (isset($matches[3])) {
          try {
            $json = json_decode('{' . $matches[3] . '}', true);
            if ($json) {
              foreach ($json as $key => $value) {
                if (in_array($key, ['class', 'id'])) {
                  $attrs[] = $key . '="' . htmlspecialchars($value) . '"';
                }
              }
            }
          } catch (Exception $e) {
            // ...
          }
        }
        
        return '<img src="' . $src . '" alt="' . $alt . '"' . (count($attrs) ? ' ' . implode(' ', $attrs) : '') . '>';
      },
      $html
    );

    $html = preg_replace_callback(
      self::PATTERNS['link'],
      function($matches) {
        $href = htmlspecialchars(trim($matches[2]), ENT_QUOTES);
        $href = str_replace('_', '&#95;', $href);
        
        $text = htmlspecialchars(trim($matches[1]));
        $text = empty($text) ? $href : $text;
        $attrs = [];
        
        if (isset($matches[3])) {
          try {
            $json = json_decode('{' . $matches[3] . '}', true);
            if ($json) {
              foreach ($json as $key => $value) {
                if (in_array($key, ['class', 'id', 'target', 'rel'])) {
                  $attrs[] = $key . '="' . htmlspecialchars($value) . '"';
                }
              }
            }
          } catch (Exception $e) {
            // ...
          }
        }
        
        return '<a href="' . $href . '"' . (count($attrs) ? ' ' . implode(' ', $attrs) : '') . '>' . $text . '</a>';
      },
      $html
    );

    $html = preg_replace(self::PATTERNS['bold'], '<strong>$1</strong>', $html);
    $html = preg_replace(self::PATTERNS['italic'], '<em>$1</em>', $html);
    $html = preg_replace(self::PATTERNS['underline'], '<u>$1</u>', $html);
    
    return $html;
  }
}
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

class NadvoParse
{
  private const PATTERNS = [
    'header' => '/^(#{1,6})\s(.+)/m',
    'bold' => '/\*\*(.+?)\*\*|__(.+?)__/s',
    'italic' => '/\*(.+?)\*/s',
    'underline' => '/\~\~(.+?)\~\~/s',
    'link' => '/\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'image' => '/!\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'figure' => '/!\#\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'video' => '/!\[video\]\((.+?)\)/',
    'audio' => '/!\[audio\]\((.+?)\)/',
    'table' => '/(\|.+)+\|/m',
    'quote' => '/^(\>+)\s?(.*)$/m',
    'code_block' => '/\`\`\`([a-z]*)\R([\s\S]*?)\R\`\`\`/',
    'inline_code' => '/(?<!`)`([^`]+)`(?!`)/',
    'text' => '/[^\*_!\[\]]+/s',
    'ul_item' => '/^([*+-])\s+(.+)/',
    'ol_item' => '/^(\d+)\.\s+(.+)/',
    'list_group' => '/^([*+-]|\d+\.)\s+.+(?:\n\1\s+.+)*/m',
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
    $markdown = $this->parseInlineElements($markdown);
    return $this->parseBlocks($markdown);
  }

  private function sanitizeInput(string $markdown) : string
  {
    //$markdown = preg_replace(self::PATTERNS['dangerous_tags'], '', $markdown);
    $markdown = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8', false);
    
    return $markdown;
  }

  private function parseLists(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $result = [];
    $stack = [];
    $currentLevel = 0;
    
    foreach ($lines as $line) {
      // Определяем тип элемента списка
      if (preg_match('/^(\s*)(\d+\.|\*|\+|\-)\s+(.+)/', $line, $matches)) {
        $indent = strlen($matches[1]);
        $isOrdered = is_numeric($matches[2][0]);
        $content = $matches[3];
        $level = floor($indent / 4) + 1; // 4 пробела = 1 уровень
        
        // Закрываем предыдущие уровни
        while ($currentLevel > $level) {
          $result[] = array_pop($stack)['close'];
          $currentLevel--;
        }
        
        // Открываем новые уровни
        if ($currentLevel < $level) {
          $tag = $isOrdered ? 'ol' : 'ul';
          $result[] = "<{$tag}>";
          $stack[] = [
            'tag' => $tag,
            'close' => "</{$tag}>",
            'isOrdered' => $isOrdered
          ];
          $currentLevel = $level;
        }
        // Если изменился тип списка на том же уровне
        elseif (end($stack)['isOrdered'] !== $isOrdered) {
          $result[] = array_pop($stack)['close'];
          $tag = $isOrdered ? 'ol' : 'ul';
          $result[] = "<{$tag}>";
          $stack[] = [
            'tag' => $tag,
            'close' => "</{$tag}>",
            'isOrdered' => $isOrdered
          ];
        }
        
        $result[] = "<li>{$content}</li>";
      } else {
        // Закрываем все списки для обычного текста
        while (!empty($stack)) {
          $result[] = array_pop($stack)['close'];
          $currentLevel--;
        }

        $result[] = $line;
      }
    }
    
    // Закрываем все оставшиеся списки
    while (!empty($stack)) {
      $result[] = array_pop($stack)['close'];
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
        if (!empty($content)) {
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
        $code = trim($matches[2]);
        
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
        $code = trim($matches[1]);
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
      if (str_starts_with(trim($line), '<pre>') || 
        str_starts_with(trim($line), '<blockquote>') ||
        str_starts_with(trim($line), '</blockquote>') ||
        str_starts_with(trim($line), '<table>') ||
        str_starts_with(trim($line), '<ul>') ||
        str_starts_with(trim($line), '<ol>') ||
        str_starts_with(trim($line), '</ul>') ||
        str_starts_with(trim($line), '</ol>') ||
        str_starts_with(trim($line), '<li>'))
      {

        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }

        $html .= $line . "\n";
        continue;
      }

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
        $html .= '<h' . strlen($matches[1]) . '>' . $matches[2] . '</h' . strlen($matches[1]) . '>' . "\n";
      } elseif (empty(trim($line))) {
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
        $caption = htmlspecialchars(trim($matches[1]), ENT_QUOTES);
        $src = htmlspecialchars(trim($matches[2]), ENT_QUOTES);
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

        $document = new DOMDocument();
        $imageElement = $document->createElement('img');

        $imageElement->setAttribute('src', $src);
        $imageElement->setAttribute('alt', $caption);

        foreach($attrs as $attrName => $attrValue) {
          $imageElement->setAttribute($attrName, $attrValue);
        }
        
        $document->appendChild($imageElement);

        return $document->saveHTML();
      },
      $html
    );

    $html = preg_replace_callback(
      self::PATTERNS['figure'],
      function($matches) {
        $caption = htmlspecialchars(trim($matches[1]), ENT_QUOTES);
        $src = htmlspecialchars(trim($matches[2]), ENT_QUOTES);
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

        $document = new DOMDocument();
        $figureElement = $document->createElement('figure');
        $imageElement = $document->createElement('img');
        $figcaptionElement = $document->createElement('figcaption', $caption);

        $imageElement->setAttribute('src', $src);
        $imageElement->setAttribute('alt', $caption);

        foreach($attrs as $attrName => $attrValue) {
          $figureElement->setAttribute($attrName, $attrValue);
        }

        $figureElement->appendChild($imageElement);
        $figureElement->appendChild($figcaptionElement);
        $document->appendChild($figureElement);

        return $document->saveHTML();
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
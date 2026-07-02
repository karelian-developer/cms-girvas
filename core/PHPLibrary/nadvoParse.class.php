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
    'link' => '/\[(.*?)\]\(\s*([^)\s]+)\s*\)(\{[^{}]+\})?/s',
    'image' => '/!\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'figure' => '/![f]\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'video' => '/!\[video\]\((.+?)\)/',
    'video_vk' => '/!\[video\-vk\]\((.+?)\)/',
    'video_ok' => '/!\[video\-ok\]\((.+?)\)/',
    'video_rt' => '/!\[video\-rt\]\((.+?)\)/',
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

  private array $usedHeaderIds = [];

  public function __construct()
  {}

  public function parse(string $markdown) : string
  {
    $this->usedHeaderIds = [];

    $markdown = $this->sanitizeInput($markdown);
    $markdown = $this->parseAutoLinks($markdown);
    $markdown = $this->parseCodeBlocks($markdown);
    $markdown = $this->parseQuotes($markdown);
    $markdown = $this->parseLists($markdown);
    $markdown = $this->parseTables($markdown);
    $markdown = $this->parseInlineElements($markdown);
    return $this->parseBlocks($markdown);
  }

  private function sanitizeInput(string $markdown) : string
  {
    // Сохраняем JSON-блоки с атрибутами
    $jsonBlocks = [];
    $markdown = preg_replace_callback('/\{[^{}]+\}/', function($matches) use (&$jsonBlocks) {
      $placeholder = '%%JSON_' . count($jsonBlocks) . '%%';
      $jsonBlocks[$placeholder] = $matches[0];
      
      return $placeholder;
    }, $markdown);
    
    // Экранируем остальной текст
    $markdown = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8', false);
    
    // Возвращаем JSON-блоки на место
    foreach ($jsonBlocks as $placeholder => $json) {
      $markdown = str_replace($placeholder, $json, $markdown);
    }
    
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
    $tableBuffer = [];

    // Список тегов, которые не должны оборачиваться в параграфы
    $blockTags = [
        'pre', '/pre', 'blockquote', '/blockquote',
        'ul', '/ul', 'ol', '/ol', 'li', '/li',
        'figure', '/figure', 'figcaption', '/figcaption',
        'table', '/table', 'thead', '/thead', 'tbody', '/tbody',
        'tr', '/tr', 'th', '/th', 'td', '/td',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        '/h1', '/h2', '/h3', '/h4', '/h5', '/h6'
    ];
    
    // Инлайн-теги, которые могут быть внутри строки
    $inlineTags = ['a', 'strong', 'em', 'u', 'code', 'img', 'video', 'iframe'];

    foreach ($lines as $line) {
      $trimmedLine = trim($line);
      
      // Проверяем, начинается ли строка с блочного тега
      $isBlockTag = false;
      foreach ($blockTags as $tag) {
        if (str_starts_with($trimmedLine, '<' . $tag . '>') || 
          str_starts_with($trimmedLine, '<' . $tag . ' ')) {
          $isBlockTag = true;
          break;
        }
      }
      
      // Если это блочный тег - закрываем параграф и выводим строку как есть
      if ($isBlockTag) {
        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }

        $html .= $line . "\n";
        continue;
      }
      
      // Обработка таблиц
      if (str_starts_with($trimmedLine, '|')) {
        if (!$inTable) {
          if (!empty($currentParagraph)) {
            $html .= '<p>' . $currentParagraph . '</p>';
            $currentParagraph = '';
          }

          $inTable = true;
          $tableBuffer = [$line];
        } else {
          $tableBuffer[] = $line;
        }
        continue;
      }
      
      // Завершение таблицы
      if ($inTable && !str_starts_with($trimmedLine, '|')) {
        $html .= implode("\n", $tableBuffer) . "\n";
        $tableBuffer = [];
        $inTable = false;
      }
      
      // Обработка заголовков
      if (preg_match('/^(#{1,6})\s+(.+)/', $line, $matches)) {
        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }

        $level = strlen($matches[1]);
        $text = $matches[2];
        $id = $this->generateHeaderId($text);

        $html .= '<h' . $level . ' id="' . $id . '">' . $text . '</h' . $level . '>' . "\n";
        continue;
      }
      
      // Обработка пустых строк
      if (empty($trimmedLine)) {
        if (!empty($currentParagraph)) {
          $html .= '<p>' . $currentParagraph . '</p>';
          $currentParagraph = '';
        }

        continue;
      }
      
      // Обычный текст - добавляем в текущий параграф
      $currentParagraph .= $line . ' ';
    }
    
    // Закрываем последний параграф
    if (!empty($currentParagraph)) {
      $html .= '<p>' . trim($currentParagraph) . '</p>';
    }
    
    // Закрываем таблицу, если осталась
    if ($inTable && !empty($tableBuffer)) {
      $html .= implode("\n", $tableBuffer) . "\n";
    }
    
    return $html;
  }

  private function parseInlineElements(string $html) : string
  {
    $html = preg_replace_callback(
      self::PATTERNS['video'],
      function($matches) {
        $url = trim($matches[1]);
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        $container = $dom->createElement('div');
        $container->setAttribute('class', 'video-container');

        $video = $dom->createElement('video');
        $video->setAttribute('controls', 'controls');

        $source = $dom->createElement('source');
        $source->setAttribute('src', $url);
        $source->setAttribute('type', 'video/' . $extension);

        $video->appendChild($source);

        $fallbackText = $dom->createTextNode('Ваш браузер не поддерживает работу с видео.');
        $video->appendChild($fallbackText);

        $container->appendChild($video);

        $dom->appendChild($container);

        return $dom->saveHTML();
      },
      $html
    );
    
    // Сборка iframe с видеороликом из ВКонтакте
    $html = preg_replace_callback(
      self::PATTERNS['video_vk'],
      function($matches) {
        $url = trim($matches[1]);

        // Преобразуем URL из vkvideo.ru/video-209953203_456239053 в vk.com/video_ext.php?oid=-209953203&id=456239053&autoplay=1
        $convertedUrl = preg_replace_callback(
            '#https?://vkvideo\.ru/video-(\d+)_(\d+)#',
            function($matches) {
              $oid = '-' . $matches[1];
              $id = $matches[2];
              return 'https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $id . '&autoplay=1';
            },
            $url
        );

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        // Создаем элемент iframe
        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('src', $convertedUrl);
        $iframe->setAttribute('width', '853');
        $iframe->setAttribute('height', '480');
        $iframe->setAttribute('style', 'background-color: #000');
        $iframe->setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;');
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        $dom->appendChild($iframe);

        return $dom->saveHTML();
      },
      $html
    );
    
    // Сборка iframe с видеороликом из Одноклассники
    $html = preg_replace_callback(
      self::PATTERNS['video_ok'],
      function($matches) {
        $url = trim($matches[1]);

        $convertedUrl = preg_replace(
            '#https?://ok\.ru/video/([^/?#]+)#',
            '//ok.ru/videoembed/$1?nochat=1',
            $url
        );

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('src', $convertedUrl);
        $iframe->setAttribute('width', '560');
        $iframe->setAttribute('height', '315');
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allow', 'autoplay');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
        $iframe->setAttribute('referrerpolicy', 'no-referrer'); // Отключаем referrer
        $iframe->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-popups allow-forms'); // Ограничиваем права
        $iframe->setAttribute('loading', 'lazy');

        $dom->appendChild($iframe);

        return $dom->saveHTML();
      },
      $html
    );

    // Сборка iframe с видеороликом из RUTUBE
    $html = preg_replace_callback(
      self::PATTERNS['video_rt'],
      function($matches) {
        $url = trim($matches[1]);

        $convertedUrl = preg_replace(
          '#https?://rutube\.ru/video/([^/?#]+)#',
          'https://rutube.ru/play/embed/$1',
          $url
        );

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        // Создаем элемент iframe
        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('width', '720');
        $iframe->setAttribute('height', '405');
        $iframe->setAttribute('src', $convertedUrl);
        $iframe->setAttribute('style', 'border: none;');
        $iframe->setAttribute('allow', 'clipboard-write; autoplay');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        $dom->appendChild($iframe);

        return $dom->saveHTML();
      },
      $html
    );

    $html = preg_replace_callback(
      self::PATTERNS['image'],
      function($matches) {
        $caption = trim($matches[1]);
        $src = trim($matches[2]);
        $attrs = [];
        
        if (isset($matches[3])) {
          try {
            $json = json_decode('{' . $matches[3] . '}', true);
            if ($json) {
              foreach ($json as $key => $value) {
                if (in_array($key, ['class', 'id'])) {
                  $attrs[$key] = $value;
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

        return $document->saveHTML($imageElement);
      },
      $html
    );

    $html = preg_replace_callback(
      self::PATTERNS['figure'],
      function($matches) {
        $caption = trim($matches[1]);
        $src = trim($matches[2]);
        $attrs = [];
        
        if (isset($matches[3])) {
          try {
            $json = json_decode('{' . $matches[3] . '}', true);
            if ($json) {
              foreach ($json as $key => $value) {
                if (in_array($key, ['class', 'id'])) {
                  $attrs[$key] = $value;
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
        $href = trim($matches[2]);
        $text = trim($matches[1]);
        $text = empty($text) ? $href : $text;
        $attrs = [];
        
        if (isset($matches[3]) && !empty($matches[3])) {
          // Восстанавливаем кавычки из &quot; в "
          $jsonString = html_entity_decode($matches[3], ENT_QUOTES, 'UTF-8');
          
          try {
            $json = json_decode($jsonString, true);

            if ($json && is_array($json)) {
              foreach ($json as $key => $value) {
                if (in_array($key, ['class', 'id', 'target', 'rel', 'title'])) {
                  $attrs[] = $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
                }
              }
            }
          } catch (Exception $e) {
            // Ошибка парсинга JSON
          }
        }
        
        $attrString = !empty($attrs) ? ' ' . implode(' ', $attrs) : '';
        return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>' . $text . '</a>';
      },
      $html
    );

    $html = preg_replace(self::PATTERNS['bold'], '<strong>$1</strong>', $html);
    $html = preg_replace(self::PATTERNS['italic'], '<em>$1</em>', $html);
    $html = preg_replace(self::PATTERNS['underline'], '<u>$1</u>', $html);
    
    return $html;
  }

  /**
   * Генерация ID заголовков
   * 
   * @param string $text
   * 
   * @return string
   */
  private function generateHeaderId(string $text) : string
  {
    $text = Utils::transliterate($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    
    if (empty($text)) {
        $text = 'heading-' . substr(md5($text), 0, 8);
    }
    
    $original = $text;
    $counter = 1;
    
    while (in_array($text, $this->usedHeaderIds, true)) {
        $text = $original . '-' . $counter++;
    }
    
    $this->usedHeaderIds[] = $text;
    
    return $text;
  }

  /**
   * Парсинг «голых» ссылок
   * 
   * @param string $markdown
   * 
   * @return string
   */
  private function parseAutoLinks(string $markdown) : string
  {
    // Не трогаем уже существующие ссылки и изображения
    $protected = [];
    $markdown = preg_replace_callback(
      '/!?\[.*?\]\(\s*\S+\s*\)/',
      function($matches) use (&$protected) {
        $placeholder = '%%PROTECTED_' . count($protected) . '%%';
        $protected[$placeholder] = $matches[0];
        return $placeholder;
      },
      $markdown
    );

    // Находим "голые" URL и оборачиваем в ссылку
    $markdown = preg_replace(
      '/(?<!["\(\/\>])(https?:\/\/[^\s<>\[\]]+)/',
      '[$1]($1)',
      $markdown
    );

    // Возвращаем защищённые фрагменты на место
    foreach ($protected as $placeholder => $value) {
      $markdown = str_replace($placeholder, $value, $markdown);
    }

    return $markdown;
  }
}
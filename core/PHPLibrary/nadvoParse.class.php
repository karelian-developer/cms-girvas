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
  private const EMOJI_MAP = [
    // Улыбки и эмоции
    ':smile:' => '😊',
    ':grin:' => '😁',
    ':joy:' => '😂',
    ':rofl:' => '🤣',
    ':wink:' => '😉',
    ':blush:' => '😊',
    ':heart_eyes:' => '😍',
    ':kissing_heart:' => '😘',
    ':thinking:' => '🤔',
    ':neutral_face:' => '😐',
    ':expressionless:' => '😑',
    ':smirk:' => '😏',
    ':unamused:' => '😒',
    ':roll_eyes:' => '🙄',
    ':relieved:' => '😌',
    ':pensive:' => '😔',
    ':sleepy:' => '😪',
    ':sleeping:' => '😴',
    ':mask:' => '😷',
    
    // Жесты
    ':thumbsup:' => '👍',
    ':thumbsdown:' => '👎',
    ':clap:' => '👏',
    ':wave:' => '👋',
    ':ok_hand:' => '👌',
    ':pray:' => '🙏',
    ':muscle:' => '💪',
    ':point_up:' => '☝️',
    ':point_down:' => '👇',
    ':point_left:' => '👈',
    ':point_right:' => '👉',
    
    // Сердца и чувства
    ':heart:' => '❤️',
    ':orange_heart:' => '🧡',
    ':yellow_heart:' => '💛',
    ':green_heart:' => '💚',
    ':blue_heart:' => '💙',
    ':purple_heart:' => '💜',
    ':broken_heart:' => '💔',
    ':sparkling_heart:' => '💖',
    ':two_hearts:' => '💕',
    ':heartbeat:' => '💓',
    ':heartpulse:' => '💗',
    
    // Животные
    ':cat:' => '🐱',
    ':dog:' => '🐶',
    ':mouse:' => '🐭',
    ':hamster:' => '🐹',
    ':rabbit:' => '🐰',
    ':fox:' => '🦊',
    ':bear:' => '🐻',
    ':panda:' => '🐼',
    ':koala:' => '🐨',
    ':tiger:' => '🐯',
    ':lion:' => '🦁',
    ':unicorn:' => '🦄',
    
    // Еда и напитки
    ':apple:' => '🍎',
    ':pizza:' => '🍕',
    ':hamburger:' => '🍔',
    ':fries:' => '🍟',
    ':coffee:' => '☕',
    ':tea:' => '🍵',
    ':beer:' => '🍺',
    ':wine:' => '🍷',
    ':cake:' => '🍰',
    ':icecream:' => '🍦',
    ':cookie:' => '🍪',
    ':chocolate:' => '🍫',
    
    // Активности и праздники
    ':tada:' => '🎉',
    ':confetti:' => '🎊',
    ':balloon:' => '🎈',
    ':gift:' => '🎁',
    ':star:' => '⭐',
    ':sparkles:' => '✨',
    ':fire:' => '🔥',
    ':zap:' => '⚡',
    ':rainbow:' => '🌈',
    ':sunny:' => '☀️',
    ':moon:' => '🌙',
    ':cloud:' => '☁️',
    
    // Символы
    ':check:' => '✅',
    ':x:' => '❌',
    ':warning:' => '⚠️',
    ':question:' => '❓',
    ':exclamation:' => '❗',
    ':100:' => '💯',
    ':copyright:' => '©️',
    ':registered:' => '®️',
    ':tm:' => '™️',

    // Флаги стран
    ':flag_ru:' => '🇷🇺',
    ':flag_by:' => '🇧🇾',
    ':flag_kz:' => '🇰🇿',
    ':flag_am:' => '🇦🇲',
    ':flag_az:' => '🇦🇿',
    ':flag_ge:' => '🇬🇪',
    ':flag_ua:' => '🇺🇦',
    ':flag_uz:' => '🇺🇿',
    ':flag_kg:' => '🇰🇬',
    ':flag_tj:' => '🇹🇯',
    ':flag_tm:' => '🇹🇲',
    ':flag_md:' => '🇲🇩',
    ':flag_lt:' => '🇱🇹',
    ':flag_lv:' => '🇱🇻',
    ':flag_ee:' => '🇪🇪',
    ':flag_gb:' => '🇬🇧',
    ':flag_de:' => '🇩🇪',
    ':flag_fr:' => '🇫🇷',
    ':flag_it:' => '🇮🇹',
    ':flag_es:' => '🇪🇸',
    ':flag_pt:' => '🇵🇹',
    ':flag_nl:' => '🇳🇱',
    ':flag_be:' => '🇧🇪',
    ':flag_ch:' => '🇨🇭',
    ':flag_at:' => '🇦🇹',
    ':flag_pl:' => '🇵🇱',
    ':flag_cz:' => '🇨🇿',
    ':flag_sk:' => '🇸🇰',
    ':flag_hu:' => '🇭🇺',
    ':flag_ro:' => '🇷🇴',
    ':flag_bg:' => '🇧🇬',
    ':flag_gr:' => '🇬🇷',
    ':flag_se:' => '🇸🇪',
    ':flag_no:' => '🇳🇴',
    ':flag_dk:' => '🇩🇰',
    ':flag_fi:' => '🇫🇮',
    ':flag_ie:' => '🇮🇪',
    ':flag_cn:' => '🇨🇳',
    ':flag_jp:' => '🇯🇵',
    ':flag_kr:' => '🇰🇷',
    ':flag_kp:' => '🇰🇵',
    ':flag_in:' => '🇮🇳',
    ':flag_vn:' => '🇻🇳',
    ':flag_th:' => '🇹🇭',
    ':flag_id:' => '🇮🇩',
    ':flag_my:' => '🇲🇾',
    ':flag_ph:' => '🇵🇭',
    ':flag_sg:' => '🇸🇬',
    ':flag_mn:' => '🇲🇳',
    ':flag_us:' => '🇺🇸',
    ':flag_ca:' => '🇨🇦',
    ':flag_mx:' => '🇲🇽',
    ':flag_br:' => '🇧🇷',
    ':flag_ar:' => '🇦🇷',
    ':flag_cl:' => '🇨🇱',
    ':flag_co:' => '🇨🇴',
    ':flag_pe:' => '🇵🇪',
    ':flag_cu:' => '🇨🇺',
    ':flag_eg:' => '🇪🇬',
    ':flag_za:' => '🇿🇦',
    ':flag_tr:' => '🇹🇷',
    ':flag_il:' => '🇮🇱',
    ':flag_sa:' => '🇸🇦',
    ':flag_ae:' => '🇦🇪',
    ':flag_ir:' => '🇮🇷',
    ':flag_iq:' => '🇮🇶',
    ':flag_au:' => '🇦🇺',
    ':flag_nz:' => '🇳🇿',
    ':flag_eu:' => '🇪🇺',
    ':flag_un:' => '🇺🇳',
    ':rainbow_flag:' => '🏳️‍🌈',
    ':white_flag:' => '🏳️',
    ':black_flag:' => '🏴',
    ':pirate_flag:' => '🏴‍☠️',
    ':checkered_flag:' => '🏁',
  ];

  private const PATTERNS = [
    'header' => '/^(#{1,6})\s+(.+?)(?:\s*\{([^{}]+)\})?\s*$/m',
    'bold' => '/\*\*([^*]+?)\*\*/s',
    'italic' => '/(?<!\*)\*(?!\*)([^*]+?)(?<!\*)\*(?!\*)/s',
    'underline' => '/\~\~(.+?)\~\~/s',
    'link' => '/\[([^\]]+)\]\(([^)]+)\)(\{[^{}]+\})?/s',
    'image' => '/!\[([^\[\]]+)?\]\(\s*(\S+)\s*\)(?:\s*\{\s*(.+?)\s*\})?/s',
    'gallery' => '/\[gallery\]([\s\S]*?)\[\/gallery\]/',
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
    'dangerous_tags' => '/<\?(?:php)?.*?\?>|<(script|iframe)[^>]*>.*?<\/\1>/is',
    'paragraph_with_attrs' => '/^(.*?)(?:\s*\{([^{}]+)\})?\s*$/s',
    'hr' => '/^(\s*)(-{3,}|\*{3,}|_{3,})(?:\s*\{([^{}]+)\})?\s*$/m',
    'footnote_ref' => '/\[\^(\d+)\]/',
    'footnote_def' => '/^\[\^(\d+)\]:\s+(.+)$/m',
    'emoji' => '/:([a-z0-9_]+):/',
  ];

  private array $usedHeaderIds = [];
  private array $codeBlockPlaceholders = [];
  private array $allowedSchemaOrgAttributes = [
    // Основные атрибуты Schema.org
    'itemprop', 'itemscope', 'itemtype', 'itemid', 'itemref',
    
    // Дополнительные атрибуты для микроразметки
    'content', 'datetime', 'href', 'src', 'title', 'alt',
    
    // Атрибуты для RDFa (связанные со Schema.org)
    'property', 'resource', 'typeof', 'about', 'datatype',
    'rel', 'rev', 'vocab', 'prefix', 'inlist'
  ];
  
  private array $allowedAttributes = [
    'class', 'id', 'style', 'title', 'lang', 'dir', 'hidden',
    'tabindex', 'accesskey', 'draggable', 'spellcheck', 'translate',
    'role', 'aria-label', 'aria-labelledby', 'aria-describedby',
    'aria-hidden', 'aria-expanded', 'aria-controls', 'aria-current',
    'data-*', // разрешаем data-атрибуты
    
    // HTML5 структурные атрибуты
    'slot', 'part', 'exportparts',

    // Атрибуты для ссылок
    'target', 'rel', 'download', 'hreflang', 'type', 'referrerpolicy',
    
    // Атрибуты для доступности
    'aria-atomic', 'aria-busy', 'aria-live', 'aria-relevant',
    'aria-autocomplete', 'aria-checked', 'aria-disabled', 'aria-errormessage',
    'aria-haspopup', 'aria-invalid', 'aria-label', 'aria-level',
    'aria-modal', 'aria-multiline', 'aria-multiselectable', 'aria-orientation',
    'aria-placeholder', 'aria-pressed', 'aria-readonly', 'aria-required',
    'aria-selected', 'aria-sort', 'aria-valuemax', 'aria-valuemin',
    'aria-valuenow', 'aria-valuetext',
    
    // Атрибуты для интернационализации
    'xml:lang', 'xmlns'
  ];

  public function __construct()
  {}

  /**
   * Обработка эмодзи
   * 
   * @param string $markdown
   * @return string
   */
  private function parseEmoji(string $markdown) : string
  {
    return preg_replace_callback(
      self::PATTERNS['emoji'],
      function($matches) {
        $emojiCode = ':' . $matches[1] . ':';
        
        // Проверяем, есть ли такой эмодзи в карте
        if (isset(self::EMOJI_MAP[$emojiCode])) {
          return self::EMOJI_MAP[$emojiCode];
        }
        
        // Если эмодзи не найден, оставляем как есть
        return $matches[0];
      },
      $markdown
    );
  }

  private function parseFootnotes(string $markdown) : string
  {
    $footnotes = [];
    
    // Собираем определения сносок
    $markdown = preg_replace_callback(
      self::PATTERNS['footnote_def'],
      function($matches) use (&$footnotes) {
        $id = $matches[1];
        $content = trim($matches[2]);
        $footnotes[$id] = $content;
        return '';
      },
      $markdown
    );
    
    // Заменяем ссылки на сноски
    $markdown = preg_replace_callback(
      self::PATTERNS['footnote_ref'],
      function($matches) use ($footnotes) {
        $id = $matches[1];
        
        if (isset($footnotes[$id])) {
          return '<sup id="fnref:' . $id . '"><a href="#fn:' . $id . '">' . $id . '</a></sup>';
        }
        
        return $matches[0];
      },
      $markdown
    );
    
    // Добавляем блок сносок в конец
    if (!empty($footnotes)) {
      $html = '<div class="footnotes"><ol>';
      
      foreach ($footnotes as $id => $content) {
        $html .= '<li id="fn:' . $id . '">' . $content . ' <a href="#fnref:' . $id . '" class="footnote-back">↩</a></li>';
      }
      
      $html .= '</ol></div>';
      $markdown .= "\n\n" . $html;
    }
    
    return $markdown;
  }
  
  /**
   * Обновленный метод parse
   */
  public function parse(string $markdown) : string
  {
    $this->usedHeaderIds = [];
    
    $markdown = $this->sanitizeInput($markdown);
    
    // Сначала защищаем блоки кода
    $markdown = $this->protectCodeBlocks($markdown);
    
    // Обрабатываем горизонтальные линии ДО списков и инлайн-элементов
    $markdown = $this->parseHr($markdown);
    
    // Затем обрабатываем остальной Markdown
    $markdown = $this->parseAutoLinks($markdown);
    $markdown = $this->parseQuotes($markdown);
    $markdown = $this->parseLists($markdown);
    $markdown = $this->parseTables($markdown);
    $markdown = $this->parseGallery($markdown);
    $markdown = $this->parseInlineElements($markdown);
    $markdown = $this->parseFootnotes($markdown);
    $markdown = $this->parseEmoji($markdown); // Добавляем обработку эмодзи
    $markdown = $this->parseBlocks($markdown);
    
    // Возвращаем блоки кода на место
    $markdown = $this->restoreCodeBlocks($markdown);
    
    return $markdown;
  }

  /**
   * Обработка горизонтальных линий
   * 
   * @param string $markdown
   * @return string
   */
  private function parseHr(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $result = [];
    
    foreach ($lines as $line) {
      $trimmedLine = trim($line);
      
      // Проверяем, является ли строка горизонтальной линией
      if (preg_match(self::PATTERNS['hr'], $trimmedLine, $hrMatches)) {
        $hrAttrs = [];
        
        // Проверяем наличие атрибутов
        if (isset($hrMatches[3]) && !$this->isTemplateVariable($hrMatches[3])) {
          $hrAttrs = $this->parseAttributes($hrMatches[3]);
        }
        
        $attrString = $this->buildAttributeString($hrAttrs);
        $result[] = '<hr' . $attrString . '>';
      } else {
        $result[] = $line;
      }
    }
    
    return implode("\n", $result);
  }

  /**
   * Защита блоков кода от обработки Markdown
   * 
   * @param string $markdown
   * @return string
   */
  private function protectCodeBlocks(string $markdown) : string
  {
    $this->codeBlockPlaceholders = [];
    
    $markdown = preg_replace_callback(
      self::PATTERNS['code_block'],
      function($matches) {
        $placeholder = '%%CODE_BLOCK_' . count($this->codeBlockPlaceholders) . '%%';
        $language = trim($matches[1]);
        $code = trim($matches[2]);
        
        $this->codeBlockPlaceholders[$placeholder] = [
          'language' => $language,
          'code' => $code
        ];
        
        return $placeholder;
      },
      $markdown
    );
    
    // Также защищаем инлайн-код
    $markdown = preg_replace_callback(
      self::PATTERNS['inline_code'],
      function($matches) {
        $placeholder = '%%INLINE_CODE_' . count($this->codeBlockPlaceholders) . '%%';
        $code = trim($matches[1]);
        
        $this->codeBlockPlaceholders[$placeholder] = [
          'language' => '',
          'code' => $code,
          'inline' => true
        ];
        
        return $placeholder;
      },
      $markdown
    );
    
    return $markdown;
  }

  /**
   * Восстановление блоков кода после обработки Markdown
   * 
   * @param string $html
   * @return string
   */
  private function restoreCodeBlocks(string $html) : string
  {
    foreach ($this->codeBlockPlaceholders as $placeholder => $data) {
      if (isset($data['inline']) && $data['inline']) {
        $replacement = '<code>' . $data['code'] . '</code>';
      } else {
        if ($data['language']) {
          $replacement = '<pre><code class="language-' . $data['language'] . '">' . $data['code'] . '</code></pre>';
        } else {
          $replacement = '<pre><code>' . $data['code'] . '</code></pre>';
        }
      }
      
      $html = str_replace($placeholder, $replacement, $html);
    }
    
    return $html;
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
        
        // Добавляем содержимое ТОЛЬКО если оно не пустое
        if (!empty($content)) {
          $result[] = '<p>' . $content . '</p>';
        }
        // Если контент пустой — ничего не добавляем (не создаём пустой <p>)
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

  private function parseGallery(string $markdown) : string
  {
    return preg_replace_callback(
      self::PATTERNS['gallery'],
      function($matches) {
        $content = trim($matches[1]);

        if (empty($content)) {
          return '';
        }

        // Разбираем строки вида ![alt](src)
        preg_match_all('/!\[([^\]]*)\]\(([^)]+)\)/', $content, $images, PREG_SET_ORDER);

        if (empty($images)) {
          return $matches[0];
        }

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        $gallery = $dom->createElement('div');
        $gallery->setAttribute('class', 'nadvo-gallery');

        foreach ($images as $image) {
          $alt = trim($image[1]);
          $src = trim($image[2]);

          $figure = $dom->createElement('figure');
          $figure->setAttribute('class', 'nadvo-gallery__item');

          $img = $dom->createElement('img');
          $img->setAttribute('src', $src);
          $img->setAttribute('alt', $alt);
          $img->setAttribute('loading', 'lazy');

          $figure->appendChild($img);

          if ($alt !== '') {
            $figcaption = $dom->createElement('figcaption');
            $figcaption->setAttribute('class', 'nadvo-gallery__caption');
            $figcaption->textContent = $alt;

            $figure->appendChild($figcaption);
          }

          $gallery->appendChild($figure);
        }

        $dom->appendChild($gallery);

        return $dom->saveHTML();
      },
      $markdown
    );
  }

  private function parseBlocks(string $markdown) : string
  {
    $lines = explode("\n", $markdown);
    $html = '';
    $currentParagraph = '';
    $inTable = false;
    $inBlockquote = false;
    $tableBuffer = [];

    // Список тегов, которые не должны оборачиваться в параграфы
    $blockTags = [
      'pre', '/pre', 'blockquote', '/blockquote',
      'ul', '/ul', 'ol', '/ol', 'li', '/li',
      'figure', '/figure', 'figcaption', '/figcaption',
      'table', '/table', 'thead', '/thead', 'tbody', '/tbody',
      'tr', '/tr', 'th', '/th', 'td', '/td',
      'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      '/h1', '/h2', '/h3', '/h4', '/h5', '/h6',
      'hr', '/hr'
    ];

    foreach ($lines as $line) {
      $trimmedLine = trim($line);
      
      // Проверяем, является ли строка плейсхолдером блока кода
      $isCodePlaceholder = false;
      if (preg_match('/^%%(CODE_BLOCK|INLINE_CODE)_\d+%%$/', $trimmedLine)) {
        $isCodePlaceholder = true;
      }
      
      // Если это плейсхолдер кода - выводим как есть, без оборачивания в параграф
      if ($isCodePlaceholder) {
        if (!empty($currentParagraph)) {
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }
        
        $html .= $trimmedLine . "\n";
        continue;
      }
      
      // Проверяем, открывается ли blockquote
      if (str_starts_with($trimmedLine, '<blockquote')) {
        $inBlockquote = true;
        
        if (!empty($currentParagraph)) {
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }
        
        $html .= $line . "\n";
        continue;
      }
      
      // Проверяем, закрывается ли blockquote
      if (str_starts_with($trimmedLine, '</blockquote>')) {
        $inBlockquote = false;
        
        if (!empty($currentParagraph)) {
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }
        
        $html .= $line . "\n";
        continue;
      }
      
      // Если мы внутри blockquote - выводим строку как есть
      if ($inBlockquote) {
        $html .= $line . "\n";
        continue;
      }
      
      // Обработка пустых строк
      if (empty($trimmedLine)) {
        // Закрываем текущий параграф, только если он не пустой
        if (!empty($currentParagraph)) {
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }
        continue; // Пустую строку не добавляем в HTML
      }
      
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
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }

        $html .= $line . "\n";
        continue;
      }
      
      // Обработка таблиц
      if (str_starts_with($trimmedLine, '|')) {
        if (!$inTable) {
          if (!empty($currentParagraph)) {
            $html .= $this->wrapParagraph($currentParagraph);
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
      
      // Обработка заголовков с атрибутами
      if (preg_match('/^(#{1,6})\s+(.+?)(?:\s*\{([^{}]+)\})?\s*$/', $line, $matches)) {
        if (!empty($currentParagraph)) {
          $html .= $this->wrapParagraph($currentParagraph);
          $currentParagraph = '';
        }

        $level = strlen($matches[1]);
        $text = $matches[2];
        $id = $this->generateHeaderId($text);
        $attrs = [];
        
        // Проверяем, есть ли атрибуты и не являются ли они шаблонной переменной
        if (isset($matches[3]) && !$this->isTemplateVariable($matches[3])) {
          $attrs = $this->parseAttributes($matches[3]);
        }
        
        // Добавляем id если его нет в атрибутах
        if (!isset($attrs['id'])) {
          $attrs['id'] = $id;
        }
        
        $attrString = $this->buildAttributeString($attrs);
        $html .= '<h' . $level . $attrString . '>' . $text . '</h' . $level . '>' . "\n";
        continue;
      }
      
      // Обычный текст - добавляем в текущий параграф
      $currentParagraph .= $line . ' ';
    }
    
    // Закрываем последний параграф, только если он не пустой
    if (!empty($currentParagraph) && trim($currentParagraph) !== '') {
      $html .= $this->wrapParagraph($currentParagraph);
    }
    
    // Закрываем таблицу, если осталась
    if ($inTable && !empty($tableBuffer)) {
      $html .= implode("\n", $tableBuffer) . "\n";
    }
    
    return $html;
  }

  /**
   * Оборачивает текст в параграф с атрибутами
   */
  private function wrapParagraph(string $content) : string
  {
    $content = trim($content);
    
    // Проверяем наличие атрибутов в конце строки
    if (preg_match('/^(.*?)(?:\s*\{([^{}]+)\})\s*$/s', $content, $matches)) {
      $text = trim($matches[1]);
      $attrString = trim($matches[2]);
      
      // Проверяем, является ли содержимое фигурных скобок атрибутами
      // Если это шаблонная переменная - оставляем как есть
      if (!$this->isTemplateVariable($attrString)) {
        $attrs = $this->parseAttributes($attrString);
        
        // Если атрибуты найдены - применяем их
        if (!empty($attrs)) {
          $attrStringHtml = $this->buildAttributeString($attrs);
          return '<p' . $attrStringHtml . '>' . $text . '</p>';
        }
      }
    }
    
    return '<p>' . $content . '</p>';
  }

  /**
   * Проверяет, является ли строка шаблонной переменной
   * 
   * Поддерживаемые форматы:
   * - {NAME} - только заглавные буквы
   * - {name} - только строчные буквы  
   * - {NAME_NAME} - заглавные с подчёркиванием
   * - {name_name} - строчные с подчёркиванием
   * - {Name_Name} - смешанный регистр с подчёркиванием
   * - {NAME123} - с цифрами
   * - {name_123} - строчные с цифрами и подчёркиванием
   */
  private function isTemplateVariable(string $str) : bool
  {
    $str = trim($str);
    
    // Проверяем, что строка не пустая
    if (empty($str)) {
      return false;
    }
    
    // Проверяем, что строка состоит только из букв, цифр и подчёркиваний
    if (preg_match('/^[A-Za-z0-9_]+$/', $str)) {
      // Дополнительная проверка: не должна содержать двоеточий, кавычек, равенств
      if (!preg_match('/[:="\']/', $str)) {
        return true;
      }
    }
    
    return false;
  }

  /**
   * Парсит строку атрибутов в массив
   */
  private function parseAttributes(string $attrString) : array
  {
    $attrs = [];
    
    if (empty($attrString)) {
      return $attrs;
    }
    
    // Проверяем, является ли строка шаблонной переменной
    if ($this->isTemplateVariable($attrString)) {
      return $attrs; // Это шаблонная переменная, не атрибуты
    }
    
    // Пробуем распарсить как JSON (формат: {"key": "value", "key2": "value2"})
    $jsonString = '{' . $attrString . '}';
    $json = json_decode($jsonString, true);
    
    if ($json && is_array($json)) {
      foreach ($json as $key => $value) {
        if ($this->isAllowedAttribute($key)) {
          $attrs[$key] = $value;
        }
      }
    } else {
      // Пробуем распарсить в формате key="value" key2="value2"
      preg_match_all('/([a-zA-Z_][a-zA-Z0-9_:.-]*)\s*=\s*["\']([^"\']*)["\']/', $attrString, $matches, PREG_SET_ORDER);
      
      foreach ($matches as $match) {
        $key = $match[1];
        $value = $match[2];
        
        if ($this->isAllowedAttribute($key)) {
          $attrs[$key] = $value;
        }
      }
      
      // Если не нашли атрибуты в формате key="value", 
      // пробуем формат без кавычек: key=value
      if (empty($attrs)) {
        preg_match_all('/([a-zA-Z_][a-zA-Z0-9_:.-]*)\s*=\s*([^"\'\s,]+)/', $attrString, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
          $key = $match[1];
          $value = $match[2];
          
          if ($this->isAllowedAttribute($key)) {
            $attrs[$key] = $value;
          }
        }
      }
      
      // Если всё ещё пусто, пробуем формат: key="value", key2="value2"
      if (empty($attrs)) {
          // Убираем кавычки вокруг ключей
          $cleanedString = preg_replace('/"([^"]+)":/', '$1:', $attrString);
          $cleanedString = preg_replace("/'([^']+)':/", '$1:', $cleanedString);
          
          preg_match_all('/([a-zA-Z_][a-zA-Z0-9_:.-]*)\s*:\s*["\']([^"\']*)["\']/', $cleanedString, $matches, PREG_SET_ORDER);
          
          foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2];
            
            if ($this->isAllowedAttribute($key)) {
              $attrs[$key] = $value;
            }
          }
        }
        
        // Пробуем формат: key="value", key2="value2" с запятыми
        if (empty($attrs)) {
          preg_match_all('/([a-zA-Z_][a-zA-Z0-9_:.-]*)\s*:\s*["\']([^"\']*)["\']/', $attrString, $matches, PREG_SET_ORDER);
          
          foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2];
            
            if ($this->isAllowedAttribute($key)) {
              $attrs[$key] = $value;
            }
          }
        }
    }
    
    return $attrs;
  }

  /**
   * Проверяет, разрешен ли атрибут
   */
  private function isAllowedAttribute(string $attrName) : bool
  {
    // Разрешаем Schema.org атрибуты
    if (in_array($attrName, $this->allowedSchemaOrgAttributes)) {
      return true;
    }
    
    // Разрешаем стандартные атрибуты
    if (in_array($attrName, $this->allowedAttributes)) {
      return true;
    }
    
    // Разрешаем data-атрибуты
    if (str_starts_with($attrName, 'data-')) {
      return true;
    }
    
    // Разрешаем aria-атрибуты
    if (str_starts_with($attrName, 'aria-')) {
      return true;
    }
    
    return false;
  }

  /**
   * Строит строку атрибутов для HTML-элемента
   */
  private function buildAttributeString(array $attrs) : string
  {
    if (empty($attrs)) {
      return '';
    }
    
    $attrString = '';
    
    foreach ($attrs as $key => $value) {
      if (is_bool($value)) {
        // Для булевых атрибутов
        if ($value) {
          $attrString .= ' ' . $key;
        }
      } else {
        $attrString .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
      }
    }
    
    return $attrString;
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

        $convertedUrl = preg_replace_callback(
          '#https?://vkvideo\.ru/video(-?)(\d+)_(\d+)#',
          function($matches) {
            $oid = $matches[1] . $matches[2];
            $id = $matches[3];
            
            return 'https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $id;
          },
          $url
        );

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        // Создаем контейнер
        $container = $dom->createElement('div');
        $container->setAttribute('class', 'video-container');

        // Создаем элемент iframe
        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('src', $convertedUrl);
        $iframe->setAttribute('style', 'background-color: #000');
        $iframe->setAttribute('allow', 'encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;');
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        $container->appendChild($iframe);
        $dom->appendChild($container);

        return $dom->saveHTML();
      },
      $html
    );

    // Сборка iframe с видеороликом из RUTUBE
    $html = preg_replace_callback(
      self::PATTERNS['video_rt'],
      function($matches) {
        $url = trim($matches[1]);

        $convertedUrl = preg_replace_callback(
          '#https://rutube\.ru/video/([A-Za-z0-9]+)#',
          function($matches) {
            $id = $matches[1];
            return 'https://rutube.ru/play/embed/' . $id . '/';
          },
          $url
        );

        $dom = new DOMDocument();
        $dom->formatOutput = true;

        // Создаем контейнер
        $container = $dom->createElement('div');
        $container->setAttribute('class', 'video-container');

        // Создаем элемент iframe
        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('src', $convertedUrl);
        $iframe->setAttribute('style', 'border: none;');
        $iframe->setAttribute('allow', 'clipboard-write;');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        $container->appendChild($iframe);
        $dom->appendChild($container);

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
          $attrs = $this->parseAttributes($matches[3]);
        }

        $document = new DOMDocument();
        $imageElement = $document->createElement('img');

        $imageElement->setAttribute('src', $src);
        $imageElement->setAttribute('alt', $caption);

        foreach($attrs as $attrName => $attrValue) {
          if (is_bool($attrValue)) {
            if ($attrValue) {
              $imageElement->setAttribute($attrName, '');
            }
          } else {
            $imageElement->setAttribute($attrName, $attrValue);
          }
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
          $attrs = $this->parseAttributes($matches[3]);
        }

        $document = new DOMDocument();
        $figureElement = $document->createElement('figure');
        $imageElement = $document->createElement('img');
        $figcaptionElement = $document->createElement('figcaption', $caption);

        $imageElement->setAttribute('src', $src);
        $imageElement->setAttribute('alt', $caption);

        foreach($attrs as $attrName => $attrValue) {
          if (is_bool($attrValue)) {
            if ($attrValue) {
              $figureElement->setAttribute($attrName, '');
            }
          } else {
            $figureElement->setAttribute($attrName, $attrValue);
          }
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
          // Получаем строку атрибутов из фигурных скобок
          $attrString = trim($matches[3], '{}');
          $attrString = html_entity_decode($attrString, ENT_QUOTES, 'UTF-8');
          
          // Проверяем, не является ли это шаблонной переменной
          if (!$this->isTemplateVariable($attrString)) {
            $attrs = $this->parseAttributes($attrString);
          }
        }
        
        $attrString = $this->buildAttributeString($attrs);
        return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>' . $text . '</a>';
      },
      $html
    );

    // Жирный **текст**
    $html = preg_replace(self::PATTERNS['bold'], '<strong>$1</strong>', $html);
    
    // Курсив *текст*
    $html = preg_replace(self::PATTERNS['italic'], '<em>$1</em>', $html);
    
    // Подчёркнутый ~~текст~~
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
    // Защищаем шаблонные переменные
    $templateVars = [];
    $markdown = preg_replace_callback(
      '/\{[A-Za-z_][A-Za-z0-9_]*\}/',
      function($matches) use (&$templateVars) {
        $placeholder = '%%TEMPLATE_VAR_' . count($templateVars) . '%%';
        $templateVars[$placeholder] = $matches[0];
        return $placeholder;
      },
      $markdown
    );
    
    // Защищаем сноски [1], [2], [3] и т.д.
    $footnoteRefs = [];
    $markdown = preg_replace_callback(
      '/\[(\d+)\]/',
      function($matches) use (&$footnoteRefs) {
        $placeholder = '%%FOOTNOTE_REF_' . count($footnoteRefs) . '%%';
        $footnoteRefs[$placeholder] = $matches[0];
        return $placeholder;
      },
      $markdown
    );
    
    $protected = [];

    $markdown = preg_replace_callback(
      '/\[gallery\]([\s\S]*?)\[\/gallery\]/',
      function($matches) use (&$protected) {
        $placeholder = '%%PROTECTED_' . count($protected) . '%%';
        $protected[$placeholder] = $matches[0];
        return $placeholder;
      },
      $markdown
    );
    
    $markdown = preg_replace_callback(
      '/!?\[.*?\]\(\s*\S+\s*\)(?:\s*\{[^{}]+\})?/',
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
    
    // Возвращаем сноски на место
    foreach ($footnoteRefs as $placeholder => $value) {
      $markdown = str_replace($placeholder, $value, $markdown);
    }
    
    // Возвращаем шаблонные переменные на место
    foreach ($templateVars as $placeholder => $value) {
      $markdown = str_replace($placeholder, $value, $markdown);
    }

    return $markdown;
  }
}
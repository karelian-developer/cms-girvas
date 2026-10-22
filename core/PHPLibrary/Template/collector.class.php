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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Template;

use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\NadvoParse as NadvoParse;
use \core\PHPLibrary\LocaleInterface as LocaleInterface;
use \core\PHPLibrary\Module\Locale as ModuleLocale;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\ThemeInterface as ThemeInterface;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \DOMDocument as DOMDocument;

final class Collector
{
  public const DECODED_ENTITIES = ['&laquo;', '&raquo;', '&ldquo;', '&rdquo;'];
  public const SAFE_SYMBOLS = ['«', '»', '“', '”'];

  private const TEMPLATE_TAG_PATTERN = '/\{([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_TAG_LANG_PATTERN = '/\{LANG\:([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_TAG_LANG_MARKDOWN_PATTERN = '/\{LANG\:MD\:([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_TAG_PROPERTY_PATTERN = '/\{PROP\:([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_LOGIC_IF_PATTERN = '/\{\?IF:([!]?)([A-Z0-9_]+)(?::([=<>!]+)([A-Za-z0-9_\-\x{0400}-\x{04FF}]+))?\?\}(.*?)\{\?ENDIF\?\}/isu';
  private const TEMPLATE_LOGIC_IF_ELSE_PATTERN = '/\{\?IF:([!]?)([A-Z0-9_]+)(?::([=<>!]+)([A-Za-z0-9_\-\x{0400}-\x{04FF}]+))?\?\}(.*?)\{\?ELSE\?\}(.*?)\{\?ENDIF\?\}/isu';
  private const TEMPLATE_LOGIC_IFNOT_PATTERN = '/\{\?IFNOT:([A-Z0-9_]+)\?\}(.*?)\{\?ENDIF\?\}/isu';
  private const TEMPLATE_LOGIC_IFNOT_ELSE_PATTERN = '/\{\?IFNOT:([A-Z0-9_]+)\?\}(.*?)\{\?ELSE\?\}(.*?)\{\?ENDIF\?\}/isu';
  private Theme $theme;
  
  /**
   * __construct
   *
   * @param  mixed $theme
   * 
   * @return void
   */
  public function __construct(Theme $theme)
  {
    $this->theme = $theme;
  }

  /**
   * Сборка контент-блока
   * 
   * @param Theme $theme
   * @param EntityTypeContent $contentBlock
   * @param array $themeVars
   * @return string
   */
  public static function assemblyContentBlock(
    Theme $theme,
    EntityTypeContent $contentBlock,
    array $themeVars = []
  ) : string {
    $CMSLocale = $theme->CMSCore->locale;
    $CMSLocaleName = $CMSLocale->getName();

    $blockName = $contentBlock->getName();
    $blockTypeName = $contentBlock->getTypeName();
    $blockSectionIntegrationName = $contentBlock->getSectionIntegrationName();

    return ThemeCollector::assemblyFileContent($theme, 'templates/sidebar/' . $blockName . '.tpl', $themeVars);
  }

  /**
   * Сборка элементов link-стилей для последующего встраивания в секцию HEAD
   * 
   * @param Theme $theme
   * @param array $stylesArray
   * 
   * @return string
   */
  public static function assemblyStyles(Theme $theme, array $stylesArray) : string
  {
    $document = new DOMDocument();

    foreach ($stylesArray as $style) {
      if (array_key_exists('href', $style) && array_key_exists('rel', $style)) {
        $styleIsCore = false;

        if (!array_key_exists('isExternal', $style)) {
          if (array_key_exists('isCore', $style)) {
            if ($style['isCore'] == true) {
              $styleIsCore = true;
              $styleHref = '/core/CSSCore/' . $style['href'];
            }
          }

          if (!$styleIsCore) {
            $styleHref = ($theme->getCategory() !== 'default')
              ? '/templates/' . $theme->getCategory() . '/' . $theme->getName() . '/' .$style['href']
              : '/templates/' . $theme->getName() . '/' . $style['href'];
          }
        } else {
          $styleHref = $style['href'];
        }

        $linkElement = $document->createElement('link');
        $linkElement->setAttribute('href', $styleHref);
        $linkElement->setAttribute('rel', $style['rel']);

        if (isset($style['as'])) {
          $linkElement->setAttribute('as', $style['as']);
        }

        if (isset($style['onload'])) {
          $linkElement->setAttribute('onload', $style['onload']);
        }

        $document->appendChild($linkElement);
      }
    }

    return $document->saveHTML();
  }


  /**
   * Сборка элементов-скриптов для последующего встраивания в секцию HEAD
   * 
   * @param Theme $theme
   * @param array $scriptsArray
   * 
   * @return string
   */
  public static function assemblyScripts(Theme $theme, array $scriptsArray) : string
  {
    $document = new DOMDocument();

    foreach ($scriptsArray as $scriptData) {
      $isCMSCore = $scriptData['isCMSCore'] ?? false;
      $src = $scriptData['src'] ?? '';

      if ($theme->getCategory() !== 'default') {
        $scriptURL = !$isCMSCore ? '/templates/' . $theme->getCategory() . '/' . $theme->getName() . '/' . $src : '/core/JSLibrary/' . $src;
      } else {
        $scriptURL = !$isCMSCore ? '/templates/' . $theme->getName() . '/' . $src : '/core/JSLibrary/' . $src;
      }

      if (array_key_exists('src', $scriptData)) {
        $scriptElement = $document->createElement('script');

        foreach ($scriptData as $attributeName => $attributeValue) {
          if ($attributeName !== 'isCMSCore' && $attributeName !== 'src') {
            $scriptElement->setAttribute($attributeName, $attributeValue);
          }

          if ($attributeName === 'src') {
            $scriptElement->setAttribute($attributeName, $scriptURL);
          }
        }

        $document->appendChild($scriptElement);
      }
    }

    return $document->saveHTML();
  }

  /**
   * Сборка шаблона на основе общих данных локализации
   * 
   * @param string $themeString
   * @param LocaleInterface $locale
   * 
   * @return string
   */
  public static function assemblyLocale(string $themeString, LocaleInterface $locale) : string
  {
    $themeTransformed = $themeString;

    $localeData = $locale->getData();
    if (!empty($localeData)) {
      foreach ($localeData as $name => $value) {
        if (preg_match(self::TEMPLATE_TAG_LANG_PATTERN, $themeTransformed)) {
          $themeTransformed = strtr($themeTransformed, ["{LANG:{$name}}" => $value]);
        }
      }
    }

    return $themeTransformed;
  }

  /**
   * Сборка шаблона на основе файлов с разметкой MarkDown на основе реестра локализации
   * 
   * @param string $themeString
   * @param LocaleInterface $locale
   * 
   * @return string
   */
  public static function assemblyLocaleMarkdown(string $themeString, LocaleInterface $locale) : string
  {
    $themeTransformed = $themeString;
    $localeRegistryArray = $locale->getRegistryArray();
    $localeCorePath = $locale->getDataPath();

    if (!empty($localeRegistryArray)) {
      foreach ($localeRegistryArray as $name => $value) {
        if (preg_match(self::TEMPLATE_TAG_LANG_MARKDOWN_PATTERN, $themeTransformed)) {
          $fileMarkdownPath = $localeCorePath . '/' . $value;
          
          if (file_exists($fileMarkdownPath)) {
            $nadvoParse = new NadvoParse();

            $fileMarkdownContent = file_get_contents($fileMarkdownPath);
            $themeTransformed = strtr($themeTransformed, ["{LANG:MD:{$name}}" => $nadvoParse->parse($fileMarkdownContent)]);
          }
        }
      }
    }

    return $themeTransformed;
  }

  /**
   * Сборка шаблона на основе значений свойств темы
   * 
   * @param string $themeString
   * @param array $propertiesData
   * 
   * @return string
   */
  public static function assemblyPropertiesValues(string $themeString, array $propertiesData) : string
  {
    $themeTransformed = $themeString;

    if (!empty($propertiesData)) {
      foreach ($propertiesData as $name => $data) {
        if (isset($propertiesData[$name]['value'])) {
          if (preg_match(self::TEMPLATE_TAG_PROPERTY_PATTERN, $themeTransformed)) {
            $themeTransformed = strtr($themeTransformed, ["{PROP:{$name}}" => $data['value']]);
          }
        }
      }
    }

    return $themeTransformed;
  }

  /**
   * Сборка шаблона на основе строки
   *
   * @param  mixed $template Содержимое шаблона
   * @param  mixed $variables Массив с тегами шаблона и их значениями
   * @return string
   */
  public static function assembly(string $template, array $variables = []) : string
  {
    if ($template === '') {
      return '';
    }

    if (empty($variables)) {
      return $template;
    }

    foreach($variables as $name => $value) {
      if (preg_match(self::TEMPLATE_TAG_PATTERN, $template)) {
        $template = strtr($template, ["{{$name}}" => $value]);
      }
    }

    return $template;
  }

    /**
   * Сборка условных блоков шаблона
   *
   * Поддерживаемый синтаксис:
   *   {?IF:VAR?}...{?ENDIF?}
   *   {?IF:VAR?}...{?ELSE?}...{?ENDIF?}
   *   {?IF:!VAR?}...{?ENDIF?}
   *   {?IF:VAR==VAL?}...{?ENDIF?}
   *   {?IFNOT:VAR?}...{?ENDIF?}
   *   {?IFNOT:VAR?}...{?ELSE?}...{?ENDIF?}
   *
   * @param SystemCore $CMSCore
   * @param string $themeString
   *
   * @return string
   */
  public static function assemblyLogic(CMSCore $CMSCore, string $themeString) : string
  {
    if ($themeString === '') {
      return '';
    }

    $resolveVariable = function (string $name) use ($CMSCore) : mixed {
      return match ($name) {
        'CLIENT_IS_LOGGED'  => $CMSCore->client?->isLogged(1) ?? false,
        'ADMIN_IS_LOGGED'   => $CMSCore->client?->isLogged(2) ?? false,
        'INSTALLER_MODE'    => $CMSCore->isInstallerModeActive(),
        'IS_ADMIN_LOCATION' => $CMSCore->isLocationAdministrativePanelActive(),
        'IS_CLI'            => $CMSCore->isCLI,
        default             => null,
      };
    };

    $compare = function (mixed $left, string $operator, string $rightRaw) : bool {
      if (is_bool($left)) {
        $right = filter_var($rightRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $right = $right ?? (bool) $rightRaw;
      } elseif (is_int($left) || is_float($left)) {
        $right = is_numeric($rightRaw) ? $rightRaw + 0 : 0;
      } else {
        $right = $rightRaw;
      }

      return match ($operator) {
        '==', '===' => $left == $right,
        '!=', '!==' => $left != $right,
        '>'         => $left >  $right,
        '<'         => $left <  $right,
        '>='        => $left >= $right,
        '<='        => $left <= $right,
        default     => false,
      };
    };

    $maxIterations = 50;

    while ($maxIterations-- > 0) {
      $previous = $themeString;

      // 1. IF ... ELSE ... ENDIF
      $themeString = preg_replace_callback(
        self::TEMPLATE_LOGIC_IF_ELSE_PATTERN,
        function ($m) use ($resolveVariable, $compare) {
          $negate = $m[1] === '!';
          $value  = $resolveVariable($m[2]);

          if (($m[3] ?? '') !== '') {
            $result = $compare($value, $m[3], $m[4]);
          } else {
            $result = (bool) $value;
          }

          if ($negate) {
            $result = !$result;
          }

          return $result ? $m[5] : $m[6];
        },
        $themeString
      );

      // 2. IF ... ENDIF (без ELSE)
      $themeString = preg_replace_callback(
        self::TEMPLATE_LOGIC_IF_PATTERN,
        function ($m) use ($resolveVariable, $compare) {
          $negate = $m[1] === '!';
          $value  = $resolveVariable($m[2]);

          if (($m[3] ?? '') !== '') {
            $result = $compare($value, $m[3], $m[4]);
          } else {
            $result = (bool) $value;
          }

          if ($negate) {
            $result = !$result;
          }

          return $result ? $m[5] : '';
        },
        $themeString
      );

      // 3. IFNOT ... ELSE ... ENDIF
      $themeString = preg_replace_callback(
        self::TEMPLATE_LOGIC_IFNOT_ELSE_PATTERN,
        function ($m) use ($resolveVariable) {
          $value = (bool) $resolveVariable($m[1]);
          return $value ? $m[3] : $m[2];
        },
        $themeString
      );

      // 4. IFNOT ... ENDIF
      $themeString = preg_replace_callback(
        self::TEMPLATE_LOGIC_IFNOT_PATTERN,
        function ($m) use ($resolveVariable) {
          $value = (bool) $resolveVariable($m[1]);
          return $value ? '' : $m[2];
        },
        $themeString
      );

      if ($themeString === $previous) {
        break;
      }
    }

    return $themeString;
  }
  
  /**
   * Сборка шаблона на основе содержимого файла
   *
   * @param Theme $theme
   * @param string $filePath Полный путь до файла
   * @param array $themeVariables Массив с тегами шаблона и их значениями
   * 
   * @return string
   */
    public static function assemblyFileContent(Theme $theme, string $filePath, array $themeVariables) : string
    {
      /** @var string $filePath Полный путь до шаблона */
      $filePath = $theme->getPath() . '/' . $filePath;

      if (file_exists($filePath)) {
        $fileContent = file_get_contents($filePath);
        $fileContent = self::assemblyLogic($theme->CMSCore, $fileContent);

        return self::assembly($fileContent, $themeVariables);
      }

      $document = new DOMDocument();
      $containerElement = $document->createElement('div');
      $containerElement->setAttribute('style', 'background-color: pink; border: 1px solid red; color: red; padding: 10px 20px; font-size: 14px;');

      $spanElement = $document->createElement('span', 'Template is not exists: ' . $filePath);
      $containerElement->appendChild($spanElement);
      $document->appendChild($containerElement);

      return $document->saveHTML();
    }

  /**
   * Получить содержимое файла шаблона
   * 
   * @param ThemeInterface $theme
   * @param string $filePath
   * 
   * @return string
   */
  public static function getTemplateFileContent(ThemeInterface $theme, string $filePath) : string
  {
    $filePath = $theme->getPath() . '/' . $filePath;

    return file_exists($filePath) ? file_get_contents($filePath) : '';
  }

  /**
   * Проверить наличие шаблонной переменной
   * 
   * @param string $template
   * @param string $name
   * 
   * @return bool
   */
  public static function existsTemplateVariable(string $template, string $name) : bool
  {
    return preg_match("/\{" . $name . "\}/", $template);
  }

  /**
   * Проверить наличие языковой шаблонной переменной
   * 
   * @param string $template
   * @param string $name
   * @param bool $isMD
   * 
   * @return bool
   */
  public static function existsTemplateLocaleVariable(string $template, string $name, bool $isMD = false) : bool
  {
    return !$isMD
      ? preg_match("/\{LANG\:" . $name . "\}/", $template)
      : preg_match("/\{LANG\:MD\:" . $name . "\}/", $template);
  }

  /**
   * Добавить значение для шаблонной переменной
   * 
   * @param array $templateVariables
   * @param string $name
   * @param string $value
   * 
   * @return void
   */
  public static function addTemplateVariable(array &$templateVariables, string $name, mixed $value) : void
  {
    $templateVariables[$name] = $value;
  }
}
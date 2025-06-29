<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template;

use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Locale as TemplateLocale;
use \core\PHPLibrary\Module\Locale as ModuleLocale;
use \DOMDocument as DOMDocument;

final class Collector
{
  private const TEMPLATE_TAG_PATTERN = '/\{([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_TAG_LANG_PATTERN = '/\{LANG\:([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_TAG_LANG_MARKDOWN_PATTERN = '/\{LANG\:MD\:([a-zA-Z0-9_]+)\}/';
  private const TEMPLATE_LOGIC_IF_PATTERN = '/\{\?IF\:([a-zA-Z0-9_]+)([=<>!]+)([a-zA-Z0-9_]+)\?\}(.*)\{\?ENDIF\?\}/is';
  private const TEMPLATE_LOGIC_IF_ELSE_PATTERN = '/\{\?IF\:([a-zA-Z0-9_]+)([=<>!]+)([a-zA-Z0-9_]+)\?\}(.*){\?ELSE\?\}(.*)\{\?ENDIF\?\}/is';
  private Template $theme;
  
  /**
   * __construct
   *
   * @param  mixed $theme
   * 
   * @return void
   */
  public function __construct(Template $theme)
  {
    $this->theme = $theme;
  }

  /**
   * Сборка элементов link-стилей для последующего встраивания в секцию HEAD
   * 
   * @param Template $theme
   * @param array $stylesArray
   * 
   * @return string
   */
  public static function assemblyStyles(Template $theme, array $stylesArray) : string
  {
    $document = new DOMDocument();

    foreach ($stylesArray as $style) {
      if (array_key_exists('href', $style) && array_key_exists('rel', $style)) {
        $styleIsCore = false;
        if (array_key_exists('isCore', $style)) {
          if ($style['isCore'] == true) {
            $styleIsCore = true;
            $styleHref = '/core/CSSCore/' . $style['href'];
          }
        }

        if (!$styleIsCore) {
          $styleHref = ($theme->getCategory() !== 'default') ? '/templates/' . $theme->getCategory() . '/' . $theme->getName() . '/' .$style['href'] : '/templates/' . $theme->getName() . '/' . $style['href'];
        }

        $linkElement = $document->createElement('link');
        $linkElement->setAttribute('href', $styleHref);
        $linkElement->setAttribute('rel', $style['rel']);

        $document->appendChild($linkElement);
      }
    }

    return $document->saveHTML();
  }


  /**
   * Сборка элементов-скриптов для последующего встраивания в секцию HEAD
   * 
   * @param Template $theme
   * @param array $scriptsArray
   * 
   * @return string
   */
  public static function assemblyScripts(Template $theme, array $scriptsArray) : string
  {
    $document = new DOMDocument();

    foreach ($scriptsArray as $scriptData) {
      $isCMSCore = $scriptData['isCMSCore'] ?? false;
      $src = $scriptData['src'] ?? '';

      if ($theme->getCategory() !== 'default') {
        $scriptURL = $isCMSCore ? '/templates/' . $theme->getCategory() . '/' . $theme->getName() . '/' . $src : '/core/JSLibrary/' . $src;
      } else {
        $scriptURL = $isCMSCore ? '/templates/' . $theme->getName() . '/' . $src : '/core/JSLibrary/' . $src;
      }

      if (array_key_exists('src', $scriptData)) {
        $scriptElement = $document->createElement('script');

        foreach ($scriptData as $attributeName => $attributeValue) {
          if ($attributeName !== 'isCMSCore' && $attributeName !== 'src') {
            $scriptElement->setAttribute($attributeName, $attributeValue);
          }

          if ($attributeName === 'src') {
            $scriptElement->setAttribute($attributeName, $attributeValue);
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
   * @param SystemCoreLocale|TemplateLocale|ModuleLocale $locale
   * 
   * @return string
   */
  public static function assemblyLocale(string $themeString, SystemCoreLocale|TemplateLocale|ModuleLocale $locale) : string
  {
    $themeTransformed = $themeString;

    $localeData = $locale->getData();
    if (!empty($localeData)) {
      foreach ($localeData as $name => $value) {
        if (preg_match(self::TEMPLATE_TAG_LANG_PATTERN, $themeTransformed)) {
          $themeTransformed = str_replace("{LANG:{$name}}", $value, $themeTransformed);
        }
      }
    }

    return $themeTransformed;
  }

  /**
   * Сборка шаблона на основе файлов с разметкой MarkDown на основе реестра локализации
   * 
   * @param string $themeString
   * @param SystemCoreLocale|TemplateLocale|ModuleLocale $locale
   * 
   * @return string
   */
  public static function assemblyLocaleMarkdown(string $themeString, SystemCoreLocale|TemplateLocale|ModuleLocale $locale) : string
  {
    $themeTransformed = $themeString;
    $localeRegistryArray = $locale->getRegistryArray();
    $localeCorePath = $locale->getDataPath();

    if (!empty($localeRegistryArray)) {
      foreach ($localeRegistryArray as $name => $value) {
        if (preg_match(self::TEMPLATE_TAG_LANG_MARKDOWN_PATTERN, $themeTransformed)) {
          $fileMarkdownPath = sprintf('%s/%s', $localeCorePath, $value);
          
          if (file_exists($fileMarkdownPath)) {
            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            $parsedown->setMarkupEscaped(true);

            $fileMarkdownContent = file_get_contents($fileMarkdownPath);
            $themeTransformed = str_replace("{LANG:MD:{$name}}", $parsedown->text($fileMarkdownContent), $themeTransformed);
          }
        }
      }
    }

    return $themeTransformed;
  }

  /**
   * Сборка шаблона на основе строки
   *
   * @param  mixed $themeString Содержимое шаблона
   * @param  mixed $themeVariables Массив с тегами шаблона и их значениями
   * @return string
   */
  public static function assembly(string $themeString, array $themeVariables) : string
  {
    $themeTransformed = $themeString;

    foreach($themeVariables as $name => $value) {
      if (preg_match(self::TEMPLATE_TAG_PATTERN, $themeTransformed)) {
        $themeTransformed = str_replace("{{$name}}", $value, $themeTransformed);
      }
    }

    return $themeTransformed;
  }

  public static function assemblyLogic(SystemCore $CMSCore, string $themeString) : string
  {
    $themeTransformed = $themeString;

    $defineFunction = function(string $functionName) : mixed {
      switch ($functionName) {
        case 'CLIENT_IS_LOGGED': return $CMSCore->client->is_logged(1);
      }

      return null;
    };

    //       1  2  3     4            5
    // {?IF:CONDITION?} ... {?ELSE?} ... {?ENDIF?}
    if (preg_match(self::TEMPLATE_LOGIC_IF_ELSE_PATTERN, $themeTransformed, $matches)) {
      //
    }

    //       1  2  3     4
    // {?IF:CONDITION?} ... {?ENDIF?}
    if (preg_match(self::TEMPLATE_LOGIC_IF_PATTERN, $themeTransformed, $matches)) {
      $defineFunctionReturned = false;
      if ($matches[2] == '==') $defineFunctionReturned = $defineFunction($matches[1]) == $matches[3];
      if ($matches[2] == '!=') $defineFunctionReturned = $defineFunction($matches[1]) != $matches[3];
      if ($matches[2] == '>=') $defineFunctionReturned = $defineFunction($matches[1]) >= $matches[3];
      if ($matches[2] == '<=') $defineFunctionReturned = $defineFunction($matches[1]) <= $matches[3];
      if ($matches[2] == '>') $defineFunctionReturned = $defineFunction($matches[1]) > $matches[3];
      if ($matches[2] == '<') $defineFunctionReturned = $defineFunction($matches[1]) < $matches[3];

      if ($defineFunctionReturned) {
        $themeTransformed = str_replace($matches[0], self::assemblyLogic($matches[4]), $themeTransformed);
      } else {
        $themeTransformed = '';
      }
    }

    return $themeTransformed;
  }
  
  /**
   * Сборка шаблона на основе содержимого файла
   *
   * @param Template $theme
   * @param string $filePath Полный путь до файла
   * @param array $themeVariables Массив с тегами шаблона и их значениями
   * 
   * @return string
   */
  public static function assemblyFileContent(Template $theme, string $filePath, array $themeVariables) : string
  {
    /** @var string $filePath Полный путь до шаблона */
    $filePath = $theme->getPath() . '/' . $filePath;

    if (file_exists($filePath)) {
      $fileContent = file_get_contents($filePath);
      return self::assembly($fileContent, $themeVariables);
    }

    return sprintf('{ERROR:FILE_IS_NOT_EXISTS=%s}', $filePath);
  }

}
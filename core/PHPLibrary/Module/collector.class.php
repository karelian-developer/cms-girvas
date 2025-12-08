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

namespace core\PHPLibrary\Module;

use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\NadvoParse as NadvoParse;
use \core\PHPLibrary\LocaleInterface as LocaleInterface;
use \core\PHPLibrary\Module\Locale as ModuleLocale;
use \core\PHPLibrary\InterfaceModule as ModuleInterface;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\ThemeInterface as ThemeInterface;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \DOMDocument as DOMDocument;

final class Collector
{
  /**
   * Сборка шаблона на основе содержимого файла
   *
   * @param ModuleInterface $module
   * @param string $filePath Полный путь до файла
   * @param array $TPLVariables Массив с тегами шаблона и их значениями
   * 
   * @return string
   */
  public static function assemblyFileContent(ModuleInterface $module, string $filePath, array $TPLVariables) : string
  {
    /** @var string $filePath Полный путь до шаблона */
    $filePath = $module->getPath() . '/templates/' . $filePath;

    if (file_exists($filePath)) {
      $fileContent = file_get_contents($filePath);
      return self::assembly($fileContent, $TPLVariables);
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
   * @param ModuleInterface $module
   * @param string $filePath
   * 
   * @return string
   */
  public static function getTemplateFileContent(ModuleInterface $module, string $filePath) : string
  {
    $filePath = $module->getPath() . '/templates/' . $filePath;

    return file_exists($filePath) ? file_get_contents($filePath) : '';
  }

  /**
   * Сборка шаблона на основе строки
   *
   * @param string $template Содержимое шаблона
   * @param array $TPLVariables Массив с тегами шаблона и их значениями
   * @return string
   */
  public static function assembly(string $template, array $TPLVariables = []) : string
  {
    return ThemeCollector::assembly($template, $TPLVariables);
  }
}
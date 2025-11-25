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

/**
 * Modules
 * 
 * Класс для работы с несколькими модулями CMS
 * 
 * @author Andrey Shestakov <drelagas.new@gmail.com>
 * @version 0.0.1
 */
class Modules
{
  const RELATIVE_MODULES_PATH = 'modules';

  /**
   * Получить абсолютный путь до модулей CMS
   * 
   * @return string
   */
  public static function getAbsoluteModulesPath() : string
  {
    return CMS_ROOT_DIRECTORY . '/' . self::RELATIVE_MODULES_PATH;
  }

  /**
   * Получить список установленных модулей
   * 
   * @return array
   */
  public static function getInstalledModulesArray() : array
  {
    $pathAbsolute = self::getAbsoluteModulesPath();
    $modules = array_diff(scandir($pathAbsolute), ['.', '..']);
    
    if (!empty($modules)) {
      foreach ($modules as $name) {
        $path = $pathAbsolute . '/' . $name;
        
        if (!file_exists($path . '/installed')) {
          $modules = array_diff($modules, [$name]);
        }
      }
    }

    return $modules;
  }
}
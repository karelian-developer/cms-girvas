<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
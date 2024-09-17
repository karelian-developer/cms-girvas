<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  /**
   * Modules
   * 
   * Класс для работы с несколькими модулями CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  class Modules {
    const RELATIVE_MODULES_PATH = 'modules';

    /**
     * Получить абсолютный путь до модулей CMS
     * 
     * @return string
     */
    public static function get_absolute_modules_path() : string {
      return sprintf('%s/%s', CMS_ROOT_DIRECTORY, self::RELATIVE_MODULES_PATH);
    }

    /**
     * Получить список установленных модулей
     * 
     * @return array
     */
    public static function get_installed_modules_array() : array {
      $modules_array = array_diff(scandir(self::get_absolute_modules_path()), ['.', '..']);
      if (!empty($modules_array)) {
        foreach ($modules_array as $module_name) {
          $module_path = sprintf('%s/%s', self::get_absolute_modules_path(), $module_name);
          if (!file_exists(sprintf('%s/installed', $module_path))) {
            $modules_array = array_diff($modules_array, [$module_name]);
          }
        }
      }

      return $modules_array;
    }
  }
}

?>
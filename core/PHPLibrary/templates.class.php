<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {  
  /**
   * Templates
   * 
   * Класс для работы с несколькими шаблонами CMS
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  class Templates {
    const RELATIVE_TEMPLATES_PATH = 'templates';

    public static function get_absolute_templates_path() : string {
      return sprintf('%s/%s', CMS_ROOT_DIRECTORY, self::RELATIVE_TEMPLATES_PATH);
    }

    public static function get_installed_templates_array() : array {
      $templates_array = array_diff(scandir(self::get_absolute_templates_path()), ['.', '..']);
      if (!empty($templates_array)) {
        foreach ($templates_array as $template_name) {
          $template_path = sprintf('%s/%s', self::get_absolute_templates_path(), $template_name);
          if (!file_exists(sprintf('%s/installed', $template_path))) {
            $templates_array = array_diff($templates_array, [$template_name]);
          }
        }
      }

      return $templates_array;
    }
  }
}

?>
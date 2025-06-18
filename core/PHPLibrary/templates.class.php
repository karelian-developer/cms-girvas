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
   * @author Andrey Shestakov <drelagas.new@yandex.ru>
   * @version 0.0.1-1
   */
  class Templates {
    const RELATIVE_TEMPLATES_PATH = 'templates';

    public static function get_absolute_templates_path() : string {
      return sprintf('%s/%s', CMS_ROOT_DIRECTORY, self::RELATIVE_TEMPLATES_PATH);
    }

    public static function get_installed_templates_array() : array {
      $themesDirectories = array_diff(scandir(self::get_absolute_templates_path()), ['.', '..']);
      if (!empty($themesDirectories)) {
        foreach ($themesDirectories as $directoryName) {
          $directoryPath = sprintf('%s/%s', self::get_absolute_templates_path(), $directoryName);
          if (!file_exists(sprintf('%s/installed', $directoryPath))) {
            $themesDirectories = array_diff($themesDirectories, [$directoryName]);
          }
        }
      }

      return $themesDirectories;
    }
  }
}

?>
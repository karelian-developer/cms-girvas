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
 * Templates
 * 
 * Класс для работы с несколькими шаблонами CMS
 * 
 * @author Andrey Shestakov <drelagas.new@yandex.ru>
 * @version 0.0.1-1
 */
class Templates
{
  const RELATIVE_TEMPLATES_PATH = 'templates';

  public static function getAbsoluteTemplatesPath() : string
  {
    return CMS_ROOT_DIRECTORY . '/' . self::RELATIVE_TEMPLATES_PATH;
  }

  public static function getInstalledTemplatesArray() : array
  {
    $themesDirectories = array_diff(scandir(self::getAbsoluteTemplatesPath()), ['.', '..']);

    if (!empty($themesDirectories)) {
      foreach ($themesDirectories as $directoryName) {
        $directoryPath = self::getAbsoluteTemplatesPath() . '/' . $directoryName;
        if (!file_exists($directoryPath . '/installed')) {
          $themesDirectories = array_diff($themesDirectories, [$directoryName]);
        }
      }
    }

    return $themesDirectories;
  }
}
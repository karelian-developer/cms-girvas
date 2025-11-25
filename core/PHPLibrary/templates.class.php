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
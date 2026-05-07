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

use \core\PHPLibrary\SystemCore as CMSCore;
use \PDOException as PDOException;

final class Utils
{
  /**
   * Транслитерация кириллицы
   * 
   * @param string $text
   * 
   * @return string
   */
  public static function transliterate(string $text) : string
  {
    $map = [
      'а' => 'a',  'б' => 'b',  'в' => 'v',   'г' => 'g',
      'д' => 'd',  'е' => 'e',  'ё' => 'yo',  'ж' => 'zh',
      'з' => 'z',  'и' => 'i',  'й' => 'j',   'к' => 'k',
      'л' => 'l',  'м' => 'm',  'н' => 'n',   'о' => 'o',
      'п' => 'p',  'р' => 'r',  'с' => 's',   'т' => 't',
      'у' => 'u',  'ф' => 'f',  'х' => 'kh',  'ц' => 'cz',
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '',
      'ы' => 'y',  'ь' => '',   'э' => 'e',   'ю' => 'yu',
      'я' => 'ya',
      
      'А' => 'A',  'Б' => 'B',  'В' => 'V',   'Г' => 'G',
      'Д' => 'D',  'Е' => 'E',  'Ё' => 'Yo',  'Ж' => 'Zh',
      'З' => 'Z',  'И' => 'I',  'Й' => 'J',   'К' => 'K',
      'Л' => 'L',  'М' => 'M',  'Н' => 'N',   'О' => 'O',
      'П' => 'P',  'Р' => 'R',  'С' => 'S',   'Т' => 'T',
      'У' => 'U',  'Ф' => 'F',  'Х' => 'Kh',  'Ц' => 'Cz',
      'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shh', 'Ъ' => '',
      'Ы' => 'Y',  'Ь' => '',   'Э' => 'E',   'Ю' => 'Yu',
      'Я' => 'Ya',
    ];
    
    return strtr($text, $map);
  }
}
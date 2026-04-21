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

use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

enum EnumContentBlock
{
  case Custom;
  case Cabinet;
  case Categories;
  case Empty;

  /**
   * Получить ID
   * 
   * @return int
   */
  public function getID() : int
  {
    return match ($this) {
      self::Custom => 1,
      self::Cabinet => 2,
      self::Categories => 3,
      self::Empty => 4
    };
  }

  /**
   * Получить техническое наименование
   * 
   * @return string
   */
  public function getTechnicalName() : string
  {
    return match ($this) {
      self::Custom => 'custom',
      self::Cabinet => 'cabinet',
      self::Categories => 'categories',
      self::Empty => 'empty',
    };
  }

  /**
   * Получить заголовок
   * 
   * @param CMSLocale $locale
   * 
   * @return string
   */
  public function getTitle(CMSLocale $locale) : string
  {
    $localeData = $locale->getData();
    $localeName = $locale->getName();

    $name = strtoupper($this->getTechnicalName());

    return $localeData['DEFAULT_CONTENT_BLOCK_TYPE_' . $name . '_LABEL'] ?? '[ ??? ]';
  }

  /**
   * Получить тип по ID
   * 
   * @return string
   */
  public static function getTypeFromID(int $id) : ?self
  {
    return match ($id) {
      1 => self::Custom,
      2 => self::Cabinet,
      3 => self::Categories,
      4 => self::Empty,
      default => null
    };
  }

  /**
   * Получить тип по техническому наименованию
   * 
   * @return string
   */
  public static function getTypeFromTechnicalName(string $name) : ?self
  {
    return match ($name) {
      'custom' => self::Custom,
      'cabinet' => self::Cabinet,
      'categories' => self::Categories,
      'empty' => self::Empty,
      default => null
    };
  }

  // Получить все технические названия
  public static function getAllTechnicalNames() : array
  {
    return array_map(
      fn(self $case) => $case->getTechnicalName(),
      self::cases()
    );
  }

  // Получить все ID
  public static function getAllIDs() : array
  {
    return array_map(
      fn(self $case) => $case->getID(),
      self::cases()
    );
  }
}
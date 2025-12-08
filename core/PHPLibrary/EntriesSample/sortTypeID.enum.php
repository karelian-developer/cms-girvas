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

namespace core\PHPLibrary\EntriesSample;

enum EnumSortTypeID
{
  case BY_DATE_OF_PUBLICATION;
  case BY_DATE_OF_CREATION;
  case BY_NUMBER_OF_VIEW;
  case BY_NUMBER_OF_COMMENTS;
  case BY_RELEVANCE;

  public function getID() : int
  {
    return match($this) {
      EnumSortTypeID::BY_DATE_OF_PUBLICATION => 1,
      EnumSortTypeID::BY_DATE_OF_CREATION => 2,
      EnumSortTypeID::BY_NUMBER_OF_VIEW => 3,
      EnumSortTypeID::BY_NUMBER_OF_COMMENTS => 4,
      EnumSortTypeID::BY_RELEVANCE => 5,
      default => 1
    };
  }
}
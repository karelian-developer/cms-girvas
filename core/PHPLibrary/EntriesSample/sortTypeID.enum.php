<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
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

namespace core\PHPLibrary\Database;

enum IndexType : string
{
  case BTREE = 'BTREE';
  case HASH = 'HASH';
  case GIST = 'GIST';
  case GIN = 'GIN';
  case SPGIST = 'SPGIST';
  case BRIN = 'BRIN';
  case FULLTEXT = 'FULLTEXT';
  
  public function isSupportedBy(DatabaseManagementSystem $dms) : bool
  {
    return match ($this) {
      self::BTREE, self::HASH => true,
      self::GIST, self::GIN, self::SPGIST, self::BRIN => $dms === DatabaseManagementSystem::PostgreSQL,
      self::FULLTEXT => true,
    };
  }
}
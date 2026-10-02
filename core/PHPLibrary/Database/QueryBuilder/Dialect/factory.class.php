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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Database\QueryBuilder\Dialect;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\QueryBuilder\Dialect as BaseDialect;

/**
 * Фабрика SQL-диалектов.
 *
 * Централизованное место, где тип СУБД превращается
 * в конкретный экземпляр диалекта. При добавлении новой СУБД
 * достаточно расширить match в create().
 */
final class Factory
{
  /**
   * @param  DMS $dms
   * @return BaseDialect
   */
  public static function create(DMS $dms) : BaseDialect
  {
    return match ($dms) {
      DMS::PostgreSQL => new PostgreSql(),
      DMS::MySQL      => new MySql(),
    };
  }
}
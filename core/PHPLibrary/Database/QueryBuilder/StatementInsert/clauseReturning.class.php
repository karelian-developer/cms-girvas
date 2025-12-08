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

namespace core\PHPLibrary\Database\QueryBuilder\StatementInsert;

use \core\PHPLibrary\Database\QueryBuilder\StatementInsert\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementInsert as StatementInsert;

final class ClauseReturning implements InterfaceClause
{
  private StatementInsert $statement;
  public array $columns = [];
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  StatementInsert $statement
   * @return void
   */
  public function __construct(StatementInsert $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * Добавить колонку значения
   *
   * @param  mixed $name
   * @return void
   */
  public function addColumn(string $name) : void
  {
    array_push($this->columns, '"' . $name . '"');
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->assembled = sprintf('RETURNING %s', implode(', ', $this->columns));
  }
}
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

namespace core\PHPLibrary\Database\QueryBuilder\StatementSelect;

use \core\PHPLibrary\Database\QueryBuilder\StatementSelect\InterfaceClause as InterfaceClause;
use \core\PHPLibrary\Database\QueryBuilder\StatementSelect as StatementSelect;

final class ClauseOrderBy implements InterfaceClause
{
  const SORT_TYPE_DESC = 'DESC';
  const SORT_TYPE_ASC = 'ASC';

  private StatementSelect $statement;
  public string $column = '';
  public string $sortType = '';
  public string $assembled = '';
  
  /**
   * __construct
   *
   * @param  StatementSelect $statement
   * @return void
   */
  public function __construct(StatementSelect $statement)
  {
    $this->statement = $statement;
  }
  
  /**
   * setSortType
   *
   * @param  string $value
   * @return void
   */
  public function setSortType(string $value) : void
  {
    $this->sortType = $value;
  }
  
  /**
   * setColumn
   *
   * @param  mixed $value
   * @return void
   */
  public function setColumn(string $value) : void
  {
    $this->column = $value;
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->assembled = sprintf('ORDER BY "%s" %s', $this->column, $this->sortType);
  }

}
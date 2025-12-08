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

final class ClauseLimit implements InterfaceClause
{
  private StatementSelect $statement;
  public int $limit = 100;
  public int $offset = 0;
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
   * setLimit
   *
   * @param  int $value
   * @return void
   */
  public function setLimit(int $value) : void
  {
    $this->limit = $value;
  }
  
  /**
   * setOffset
   *
   * @param  int $value
   * @return void
   */
  public function setOffset(int $value) : void
  {
    $this->offset = $value;
  }
  
  /**
   * assembly
   *
   * @return void
   */
  public function assembly() : void
  {
    if ($this->limit >= 1) {
      if ($this->offset <= 0) {
        $this->assembled = sprintf('LIMIT %d', $this->limit);
      } else {
        $this->assembled = sprintf('LIMIT %d OFFSET %d', $this->limit, $this->offset);
      }
    } else {
      $this->assembled = '';
    }
  }

}
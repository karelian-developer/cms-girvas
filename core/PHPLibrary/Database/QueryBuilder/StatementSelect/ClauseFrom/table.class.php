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

namespace core\PHPLibrary\Database\QueryBuilder\StatementSelect\ClauseFrom;

final class Table
{
  private string $name = '';
  private string $prefix = '';
  
  /**
   * __construct
   *
   * @param  string $name
   * @param  prefix $prefix
   * @return void
   */
  public function __construct(string $name, string $prefix)
  {
    $this->setName($name);
    $this->setPrefix($prefix);
  }
  
  /**
   * setName
   *
   * @param  mixed $value
   * @return void
   */
  private function setName(string $value) : void
  {
    $this->name = $value;
  }
  
  /**
   * getName
   *
   * @return string
   */
  public function getName() : string
  {
    return $this->name;
  }
  
  /**
   * setPrefix
   *
   * @param  string $value
   * @return void
   */
  private function setPrefix(string $value) : void
  {
    $this->prefix = $value;
  }
  
  /**
   * getPrefix
   *
   * @return string
   */
  public function getPrefix() : string
  {
    return $this->prefix;
  }
}
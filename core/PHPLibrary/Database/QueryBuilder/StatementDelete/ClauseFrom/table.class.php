<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom;

final class Table
{
  private string $name = '';
  private string $prefix = '';
  
  /**
   * __construct
   *
   * @param  mixed $name
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
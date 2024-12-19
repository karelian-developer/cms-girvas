<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder\StatementDelete\ClauseFrom {

  class Table {
    private string $name = '';
    private string $prefix = '';
    
    /**
     * __construct
     *
     * @param  mixed $name
     * @return void
     */
    public function __construct(string $name, string $prefix) {
      $this->set_name($name);
      $this->set_prefix($prefix);
    }
    
    /**
     * set_name
     *
     * @param  mixed $value
     * @return void
     */
    private function set_name(string $value) : void {
      $this->name = $value;
    }
    
    /**
     * get_name
     *
     * @return string
     */
    public function get_name() : string {
      return $this->name;
    }
    
    /**
     * set_prefix
     *
     * @param  string $value
     * @return void
     */
    private function set_prefix(string $value) : void {
      $this->prefix = $value;
    }
    
    /**
     * get_prefix
     *
     * @return string
     */
    public function get_prefix() : string {
      return $this->prefix;
    }

  }

}
?>
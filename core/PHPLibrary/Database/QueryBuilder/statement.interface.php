<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Database\QueryBuilder {
  use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
  
  interface InterfaceStatement {
    public function __construct(QueryBuilder $queryBuilder);
    public function assembly() : void;
  }

}

?>
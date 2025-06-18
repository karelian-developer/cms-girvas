<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Entities\Types {
  use \core\PHPLibrary\SystemCore as SystemCore;

  interface Content {
    public function __construct(SystemCore $CMSCore, int $id);

    public function init_data(array $columns = ['*']): void;
  }
}

?>
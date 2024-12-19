<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {

  interface InterfaceFileConnector {
    public function __construct(\core\PHPLibrary\SystemCore $system_core);
    public function set_current_directory(string $directory) : void;
    public function get_current_directory() : string;
    public function connect_file(string $file_name) : bool;
  }

}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template {

  interface InterfaceCore {
    public function __construct(\core\PHPLibrary\Template $template_base);
    public function assembly() : void;
    public function assembly_document() : string;
    public function assembly_header() : string;
    public function assembly_main() : string;
    public function assembly_footer() : string;
  }

}

?>
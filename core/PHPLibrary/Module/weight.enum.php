<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Module {
  enum EnumWeight {
    case BYTES;
    case KILOBYTES;
    case MEGABYTES;
    case GIGABYTES;
    case TERABYTES;
    case PETABYTES;
    case EXABYTES;
    case ZETTABYTES;
    case YOTTABYTES;
  }
}

?>
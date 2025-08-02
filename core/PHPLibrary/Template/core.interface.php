<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template;

use \core\PHPLibrary\Template as Theme;

interface InterfaceCore
{
  public function __construct(Theme $themeBase);
  public function getPrimaryColor() : string;
  public function assembly() : void;
  public function assemblyDocument() : string;
  public function assemblyHeader() : string;
  public function assemblyMain() : string;
  public function assemblyFooter() : string;
}
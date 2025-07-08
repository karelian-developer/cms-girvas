<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\SystemCore as CMSCore;

interface InterfaceFileConnector
{
  public function __construct(CMSCore $CMSCore);
  public function setCurrentDirectory(string $directory) : void;
  public function getCurrentDirectory() : string;
  public function connectFile(string $fileName) : bool;
}
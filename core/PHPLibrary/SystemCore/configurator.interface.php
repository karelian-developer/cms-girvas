<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\CoreInterface as CMSCoreInterface;

interface ConfiguratorInterface
{
  public function __construct(CMSCoreInterface $CMSCore);
  public function set(string $name, mixed $value) : void;
  public function get(string $name) : mixed;
  public function exists(string $name) : bool;
}
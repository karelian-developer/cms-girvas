<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

/**
 * Форум
 * 
 * @author Andrey Shestakov <drelagas.new@gmail.com>
 * @version 0.0.1
 */
#[\AllowDynamicProperties]
final class Forum
{
  /** @var SystemCore|null Объект системного ядра */
  public SystemCore|null $CMSCore = null;

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;
  }

  public function getCategories()
  {

  }
}
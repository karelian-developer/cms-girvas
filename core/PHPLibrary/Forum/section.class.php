<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Forum;

use \core\PHPLibrary\Forum as Forum;
use \core\PHPLibrary\Forum\Category as ForumCategory;

/**
 * Форум
 * 
 * @author Andrey Shestakov <drelagas.new@gmail.com>
 * @version 0.0.1
 */
#[\AllowDynamicProperties]
final class Section
{
  /** @var SystemCore|null Объект системного ядра */
  public SystemCore|null $CMSCore = null;
  /** @var Forum|null Объект форума */
  public Forum|null $forum = null;

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  string $name
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Forum $forum)
  {
    $this->CMSCore = $CMSCore;
    $this->forum = $forum;
  }

  public function getTopics()
  {
    
  }
}
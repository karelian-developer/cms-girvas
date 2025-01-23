<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Forum {
  use \core\PHPLibrary\Forum as Forum;
  use \core\PHPLibrary\Forum\Section as ForumSection;

  /**
   * Форум
   * 
   * @author Andrey Shestakov <drelagas.new@gmail.com>
   * @version 0.0.1
   */
  #[\AllowDynamicProperties]
  final class Category {
    /** @var SystemCore|null Объект системного ядра */
    public SystemCore|null $system_core = null;
    /** @var Forum|null Объект форума */
    public Forum|null $forum = null;

    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  string $name
     * @return void
     */
    public function __construct(SystemCore $system_core, Forum $forum) {
      $this->system_core = $system_core;
      $this->forum = $forum;
    }

    public function get_sections() {

    }
  }
}

?>
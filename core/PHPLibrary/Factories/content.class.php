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

namespace core\PHPLibrary\Factories;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \core\PHPLibrary\Entry as EntityEntry;
use \core\PHPLibrary\EntryCategory as EntityEntryCategory;
use \core\PHPLibrary\EntryComment as EntityEntryComment;
use \core\PHPLibrary\EntrySample as EntityEntrySample;
use \core\PHPLibrary\PageStatic as EntityPageStatic;

class Content
{
  private SystemCore $CMSCore;

  /**
   * Создать контентную сущность
   * 
   * @param SystemCore $CMSCore
   * @param string $type
   * @param array $data
   * 
   * @return EntityTypeContent
   */
  public static function create(SystemCore $CMSCore, string $type, array $data = []) : EntityTypeContent
  {
    return match($type) {
      'entry' => new EntityEntry($CMSCore, $data['id']),
      'entryCategory' => new EntityEntryCategory($CMSCore, $data['id']),
      'entryComment' => new EntityEntryComment($CMSCore, $data['id']),
      'entrySample' => new EntityEntrySample($CMSCore, $data['id']),
      'pageStatic' => new EntityPageStatic($CMSCore, $data['id']),
      default => throw new Exception('Entity is not exists')
    };
  }
}

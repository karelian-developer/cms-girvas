<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Factories {
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
  use \core\PHPLibrary\Entry as EntityEntry;
  use \core\PHPLibrary\EntryCategory as EntityEntryCategory;
  use \core\PHPLibrary\EntryComment as EntityEntryComment;
  use \core\PHPLibrary\EntrySample as EntityEntrySample;
  use \core\PHPLibrary\PageStatic as EntityPageStatic;

  class Content {
    private SystemCore $systemCore;

    /**
     * Создать контентную сущность
     * 
     * @param SystemCore $systemCore
     * @param string $type
     * @param array $data
     * 
     * @return EntityTypeContent
     */
    public static function create(SystemCore $systemCore, string $type, array $data = []) : EntityTypeContent {
      return match($type) {
        'entry' => new EntityEntry($systemCore, $data['id']),
        'entryCategory' => new EntityEntryCategory($systemCore, $data['id']),
        'entryComment' => new EntityEntryComment($systemCore, $data['id']),
        'entrySample' => new EntityEntrySample($systemCore, $data['id']),
        'pageStatic' => new EntityPageStatic($systemCore, $data['id']),
        default => throw new Exception('Entity is not exists')
      };
    }
  }
}

?>
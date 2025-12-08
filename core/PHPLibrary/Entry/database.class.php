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

namespace core\PHPLibrary\Entry;

use \core\PHPLibrary\Database as CMSDatabase;
use \core\PHPLibrary\Entries as Entries;

final class Database
{
  private CMSDatabase $database;
  private Entries $entry;
  
  /**
   * __construct
   *
   * @param  CMSDatabase $database
   * @param  Entries $entry
   * 
   * @return void
   */
  public function __construct(CMSDatabase $database, Entries $entry)
  {
    $this->database = $database;
    $this->entry = $entry;
  }

  private function getEntryID() : int
  {
    return $this->entry->getID();
  }

  public function getData(string|array $columns) : string|array|null
  {
    /** @var string $databaseQuery SQL-запрос */
    $databaseQuery = '';
    /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
    $databaseManagementSystem = $this->database->getDatabaseNanagementSystem();
    $databaseQuery = match ($databaseManagementSystem->value) {
      'mysql' => $this->database->getFileSQL('Entry/get.mysql.sql'),
      'pgsql' => $this->database->getFileSQL('Entry/get.pgsql.sql'),
    };

    /** @var string $databaseQuery SQL-запрос (переопределение) */
    $databaseQuery = is_string($columns) ? sprintf($databaseQuery, $columns) : sprintf($databaseQuery, implode(', ', $columns));
    $entryID = $this->getEntryID();

    $this->database->prepare($databaseQuery);
    $this->database->bindParam(':id', $entryID, \PDO::PARAM_INT);
    $this->database->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    if ($result) {
      return is_string($columns) ? $result[$columns] : $result;
    }

    return null;
  }

  public function exists() : bool
  {
    /** @var string $databaseQuery SQL-запрос */
    $databaseQuery = '';
    /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
    $databaseManagementSystem = $this->database->getDatabaseNanagementSystem();

    $databaseQuery = match ($databaseManagementSystem->value) {
      'mysql' => $this->database->getFileSQL('Entry/exists.mysql.sql'),
      'pgsql' => $this->database->getFileSQL('Entry/exists.pgsql.sql'),
    };
    
    $entryID = $this->getEntryID();

    $this->database->prepare($databaseQuery);
    $this->database->bindParam(':id', $entryID, \PDO::PARAM_INT);
    $this->database->execute();

    $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
    return $result ? $result['exists'] : false;
  }
}
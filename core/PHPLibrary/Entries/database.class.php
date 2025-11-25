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

namespace core\PHPLibrary\Entries;

use \core\PHPLibrary\Database as CMSDatabase;
use \core\PHPLibrary\Entries as Entries;

final class Database
{
  private CMSDatabase $database;
  private Entries $entries;
  private mixed $data;
  private array $conditions = [];
  private int $limit = 100;
  private array $selectColumns = [];
  
  /**
   * __construct
   *
   * @param  CMSDatabase $database
   * 
   * @return void
   */
  public function __construct(CMSDatabase $database)
  {
    $this->database = $database;
  }

  public function get() : array
  {
    /** @var string $databaseQuery SQL-запрос */
    $databaseQuery = '';
    /** @var EnumDatabaseManagementSystem $databaseManagementSystem */
    $databaseManagementSystem = $this->database->getDatabaseNanagementSystem();
    $databaseQuery = match ($databaseManagementSystem->value) {
      'mysql' => $this->database->getFileSQL('Entry/get.mysql.sql'),
      'pgsql' => $this->database->getFileSQL('Entry/get.pgsql.sql')
    };

    /** @var string $databaseQuery SQL-запрос (переопределение) */
    $databaseQuery = sprintf($databaseQuery, implode(', ', $this->selectColumns), implode(' AND ', $this->conditions), $this->limit);

    $this->database->prepare($databaseQuery);
    $this->database->bindParam(':categoryID', $categoryID, \PDO::PARAM_INT);
    $this->database->execute();

    $result = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
    return count($result) > 0 ? $result : [];
  }
}
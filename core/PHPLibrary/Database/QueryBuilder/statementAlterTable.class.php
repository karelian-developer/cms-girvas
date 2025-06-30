<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

/**
 * Класс находится в тестовом виде
 */

namespace core\PHPLibrary\Database\QueryBuilder;

use \core\PHPLibrary\Database\QueryBuilder as QueryBuilder;
use \core\PHPLibrary\Database\QueryBuilder\InterfaceStatement as InterfaceStatement;

final class StatementAlterTable implements InterfaceStatement
{
  public QueryBuilder $queryBuilder;
  private string $tableName;
  private string $event;
  private array $data;
  private bool $ifExists = false;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param  mixed $queryBuilder
   * @return void
   */
  public function __construct(QueryBuilder $queryBuilder)
  {
    $this->queryBuilder = $queryBuilder;
  }

  public function setTableName(string $name) : void
  {
    $this->tableName = $name;
  }

  public function getTableName() : string
  {
    return $this->tableName;
  }

  public function renameColumn(string $oldName, string $newName) : void
  {
    $this->setEvent('renameColumn');
    $this->data = [
      'oldName' => $oldName,
      'newName' => $newName
    ];
  }

  public function modifyColumn(string $name, string $typeName) : void
  {
    $this->setEvent('modifyColumn');
    $this->data = [
      'name' => $name,
      'typeName' => $typeName
    ];
  }

  public function addColumn(string $name, string $typeName) : void
  {
    $this->setEvent('addColumn');
    $this->data = [
      'name' => $name,
      'typeName' => $typeName
    ];
  }

  public function dropColumn(string $name) : void
  {
    $this->setEvent('dropColumn');
    $this->data = [
      'name' => $name
    ];
  }

  private function setEvent(string $name) : void
  {
    $this->event = $name;
  }

  public function setIfExists(bool $value) : void
  {
    $this->ifExists = $value;
  }

  public function assembly() : void
  {
    $this->assembled .= 'ALTER TABLE ';
    $this->assembled .= trim($this->tableName) ?? 'column_undefined';

    $ifExists = $this->ifExists ? 'IF EXISTS ' : '';

    if ($this->event === 'renameColumn') {
      $this->assembled .= ' RENAME COLUMN ' . $ifExists . $this->data['oldName'] . ' TO ' . $this->data['newName'];
    }

    if ($this->event === 'modifyColumn') {
      $this->assembled .= ' RENAME COLUMN ' . $this->data['name'] . ' ' . strtoupper($this->data['newName']);
    }

    $this->assembled .= ';';
  }
}
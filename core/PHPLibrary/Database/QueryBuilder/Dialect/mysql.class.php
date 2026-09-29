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

namespace core\PHPLibrary\Database\QueryBuilder\Dialect;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\IndexType as IndexType;
use \core\PHPLibrary\Database\QueryBuilder\Dialect as BaseDialect;

/**
 * SQL-диалект для СУБД MySQL.
 */
final class MySql extends BaseDialect
{
  /**
   * @return DMS
   */
  public function getDMS() : DMS
  {
    return DMS::MySQL;
  }

  /**
   * @param  string $logicalType
   * @return string
   */
  protected function resolveSimple(string $logicalType) : string
  {
    return match ($logicalType) {
      'id'        => 'int NOT NULL AUTO_INCREMENT',
      'bigint'    => 'bigint',
      'integer'   => 'int',
      'boolean'   => 'tinyint(1)',
      'string'    => 'varchar(255)',
      'text'      => 'longtext',
      'json'      => 'json',
      'timestamp' => 'bigint',
      default     => throw new \InvalidArgumentException(
        sprintf('Unknown logical type "%s" for MySQL', $logicalType)
      ),
    };
  }

  public function resolveAdaptiveCondition(array $conditions) : string
  {
    return $conditions['mysql'] ?? $conditions['default'] ?? '';
  }

  /**
   * @param  int $length
   * @return string
   */
  protected function resolveSizedString(int $length) : string
  {
    return sprintf('varchar(%d)', $length);
  }

  /**
   * @param  string $identifier
   * @return string
   */
  public function quoteIdentifier(string $identifier) : string
  {
    return '`' . $identifier . '`';
  }

  /**
   * @param  IndexType $type
   * @return bool
   */
  public function supportsIndexType(IndexType $type) : bool
  {
    return match ($type) {
      IndexType::BTREE, IndexType::HASH, IndexType::FULLTEXT => true,
      default => false,
    };
  }

  /**
   * @param  IndexType $type
   * @return bool
   */
  public function requiresUsingClause(IndexType $type) : bool
  {
    return false;
  }

  /**
   * @return bool
   */
  public function supportsDropIndexConcurrently() : bool
  {
    return true;
  }

  /**
   * @return bool
   */
  public function supportsDropIndexIfExists() : bool
  {
    return true;
  }
}
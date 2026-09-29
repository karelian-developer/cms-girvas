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
 * SQL-диалект для СУБД PostgreSQL.
 */
final class PostgreSql extends BaseDialect
{
  /**
   * @return DMS
   */
  public function getDMS() : DMS
  {
    return DMS::PostgreSQL;
  }

  /**
   * @param  string $logicalType
   * @return string
   */
  protected function resolveSimple(string $logicalType) : string
  {
    return match ($logicalType) {
      'id'        => 'serial',
      'bigint'    => 'bigint',
      'integer'   => 'integer',
      'boolean'   => 'boolean',
      'string'    => 'varchar(255)',
      'text'      => 'text',
      'json'      => 'jsonb',
      'timestamp' => 'integer',
      default     => throw new \InvalidArgumentException(
        sprintf('Unknown logical type "%s" for PostgreSQL', $logicalType)
      ),
    };
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
   * @return bool
   */
  public function supportsConcurrently() : bool
  {
    return true;
  }

  /**
   * @return bool
   */
  public function supportsIfNotExistsForIndex() : bool
  {
    return true;
  }

  /**
   * @return bool
   */
  public function supportsPartialIndex() : bool
  {
    return true;
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

  public function supportsInsertReturning() : bool
  {
    return true;
  }

  /**
   * @param  string $identifier
   * @return string
   */
  public function quoteIdentifier(string $identifier) : string
  {
    return '"' . $identifier . '"';
  }
  
  public function resolveAdaptiveCondition(array $conditions) : string
  {
    return $conditions['postgresql'] ?? $conditions['default'] ?? '';
  }

  /**
   * @param  IndexType $type
   * @return bool
   */
  public function supportsIndexType(IndexType $type) : bool
  {
    return match ($type) {
      IndexType::BTREE,
      IndexType::HASH,
      IndexType::GIST,
      IndexType::GIN,
      IndexType::SPGIST,
      IndexType::BRIN => true,
      IndexType::FULLTEXT => false,
    };
  }

  /**
   * @param  IndexType $type
   * @return bool
   */
  public function requiresUsingClause(IndexType $type) : bool
  {
    return match ($type) {
      IndexType::BTREE => false,
      default => true,
    };
  }
}
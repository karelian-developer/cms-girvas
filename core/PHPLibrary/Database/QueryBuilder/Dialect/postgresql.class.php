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

  /**
   * @param  string $column
   * @param  string $key
   * @return string
   */
  public function jsonExtractBoolean(string $column, string $key) : string
  {
    return sprintf(
      '(%s::jsonb->>\'%s\')::boolean',
      $this->quoteIdentifier($column),
      $key
    );
  }

  /**
   * @param  string $column
   * @param  string $jsonObject
   * @return string
   */
  public function jsonMergePatch(string $column, string $jsonObject) : string
  {
    return sprintf(
      '%s::jsonb || %s::jsonb',
      $this->quoteIdentifier($column),
      $jsonObject
    );
  }

  public function getLastInsertedIDCondition(string $column) : string
  {
    throw new \LogicException(
      'PostgreSQL uses RETURNING clause instead of LAST_INSERT_ID(). ' .
      'This method should not be called for PostgreSQL.'
    );
  }

  public function stringLike(string $column, string $paramName, bool $caseInsensitive = true) : string
  {
    return sprintf(
      '%s %s :%s',
      $this->quoteIdentifier($column),
      $caseInsensitive ? 'ILIKE' : 'LIKE',
      $paramName
    );
  }

  public function jsonExtractInt(string $column, string $key) : string
  {
    return sprintf(
      '(%s::jsonb->>\'%s\')::int',
      $this->quoteIdentifier($column),
      $key
    );
  }

  public function jsonBuildObject(array $pairs) : string
  {
    $parts = [];
    foreach ($pairs as $key => $value) {
      $parts[] = sprintf("'%s', %s", $key, $value);
    }
    return sprintf('jsonb_build_object(%s)', implode(', ', $parts));
  }

  public function jsonMergePatches(string $column, array $patches) : string
  {
    if (empty($patches)) {
      return $this->quoteIdentifier($column);
    }

    return sprintf(
      '%s::jsonb || %s::jsonb',
      $this->quoteIdentifier($column),
      implode(' || ', array_map(fn($p) => "({$p})", $patches))
    );
  }

  public function jsonObjectMergeKey(string $column, string $key, string $jsonFragment) : string
  {
    return sprintf(
      'COALESCE(%s::jsonb->\'%s\', \'{}\'::jsonb) || %s::jsonb',
      $this->quoteIdentifier($column),
      $key,
      $jsonFragment
    );
  }

  /**
   * @param  string $column
   * @return string
   */
  public function extractYearFromUnixTimestamp(string $column) : string
  {
    return sprintf('EXTRACT(YEAR FROM to_timestamp(%s))', $this->quoteIdentifier($column));
  }

  /**
   * @param  string $column
   * @return string
   */
  public function extractMonthFromUnixTimestamp(string $column) : string
  {
    return sprintf('EXTRACT(MONTH FROM to_timestamp(%s))', $this->quoteIdentifier($column));
  }

  /**
   * @param  string $column
   * @param  string $locale
   * @param  string $field
   * @param  string $paramName
   * @return string
   */
  public function jsonLike(string $column, string $locale, string $field, string $paramName) : string
  {
    return sprintf(
      "%s->'%s'->>'%s' ILIKE '%%' || :%s || '%%'",
      $this->quoteIdentifier($column),
      $locale,
      $field,
      $paramName
    );
  }

  /**
   * @param  string $column
   * @param  string $locale
   * @param  string $field
   * @param  string $paramName
   * @return string
   */
  public function jsonArrayContainsLike(string $column, string $locale, string $field, string $paramName) : string
  {
    return sprintf(
      "EXISTS (SELECT 1 FROM jsonb_array_elements_text(%s->'%s'->'%s') AS kw WHERE kw ILIKE '%%' || :%s || '%%')",
      $this->quoteIdentifier($column),
      $locale,
      $field,
      $paramName
    );
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
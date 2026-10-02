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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
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

  /**
   * @param  string $column
   * @param  string $key
   * @return string
   */
  public function jsonExtractBoolean(string $column, string $key) : string
  {
    // MySQL: JSON_EXTRACT возвращает JSON-значение.
    // Оператор ->> возвращает текст. Сравниваем с 'true'.
    return sprintf(
      '%s->>\'$.%s\' = \'true\'',
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
      'JSON_MERGE_PATCH(COALESCE(%s, JSON_OBJECT()), CAST(%s AS JSON))',
      $this->quoteIdentifier($column),
      $jsonObject
    );
  }

  public function getLastInsertedIDCondition(string $column) : string
  {
    return sprintf('%s = LAST_INSERT_ID()', $this->quoteIdentifier($column));
  }

  public function stringLike(string $column, string $paramName, bool $caseInsensitive = true) : string
  {
    if ($caseInsensitive) {
      return sprintf(
        'LOWER(%s) LIKE LOWER(:%s)',
        $this->quoteIdentifier($column),
        $paramName
      );
    }

    return sprintf(
      '%s LIKE :%s',
      $this->quoteIdentifier($column),
      $paramName
    );
  }

  public function jsonExtractInt(string $column, string $key) : string
  {
    return sprintf(
      'CAST(JSON_UNQUOTE(JSON_EXTRACT(%s, \'$.%s\')) AS SIGNED)',
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
    return sprintf('JSON_OBJECT(%s)', implode(', ', $parts));
  }

  public function jsonMergePatches(string $column, array $patches) : string
  {
    if (empty($patches)) {
      return $this->quoteIdentifier($column);
    }

    return sprintf(
      'JSON_MERGE_PRESERVE(COALESCE(%s, JSON_OBJECT()), %s)',
      $this->quoteIdentifier($column),
      implode(', ', $patches)
    );
  }

  public function jsonObjectMergeKey(string $column, string $key, string $jsonFragment) : string
  {
    return sprintf(
      'JSON_MERGE_PRESERVE(COALESCE(JSON_EXTRACT(%s, \'$.%s\'), JSON_OBJECT()), CAST(%s AS JSON))',
      $this->quoteIdentifier($column),
      $key,
      $jsonFragment
    );
  }

  public function jsonReplace(string $column, string $jsonValue) : string
  {
    return sprintf('CAST(%s AS JSON)', $jsonValue);
  }

  public function quoteLiteral(string $value) : string
  {
    return "'" . str_replace(
      ["\\",  "'"],
      ["\\\\", "''"],
      $value
    ) . "'";
  }

  /**
   * @param  string $column
   * @return string
   */
  public function extractYearFromUnixTimestamp(string $column) : string
  {
    return sprintf('YEAR(FROM_UNIXTIME(%s))', $this->quoteIdentifier($column));
  }

  /**
   * @param  string $column
   * @return string
   */
  public function extractMonthFromUnixTimestamp(string $column) : string
  {
    return sprintf('MONTH(FROM_UNIXTIME(%s))', $this->quoteIdentifier($column));
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
      "JSON_UNQUOTE(JSON_EXTRACT(%s, '$.%s.%s')) LIKE CONCAT('%%', :%s, '%%')",
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
      "JSON_SEARCH(%s, 'one', :%s, NULL, '$.%s.%s[*]') IS NOT NULL",
      $this->quoteIdentifier($column),
      $paramName,
      $locale,
      $field
    );
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
  public function supportsDropIndexIfExists() : bool
  {
    return true;
  }
}
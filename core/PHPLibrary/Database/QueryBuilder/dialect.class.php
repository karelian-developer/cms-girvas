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

namespace core\PHPLibrary\Database\QueryBuilder;

use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;
use \core\PHPLibrary\Database\IndexType as IndexType;

/**
 * Абстрактный SQL-диалект.
 *
 * Отвечает за перевод логических типов данных в физические,
 * а также за проверку допустимости конструкций, специфичных
 * для конкретной СУБД (DEFAULT, типы индексов, экранирование).
 *
 * Наследники реализуют только специфику своей СУБД.
 */
abstract class Dialect
{
  /**
   * Возвращает тип СУБД, которому соответствует диалект.
   *
   * @return DMS
   */
  abstract public function getDMS() : DMS;

  /**
   * Резолвинг простого логического типа (без размеров).
   *
   * @param  string $logicalType
   * @return string
   */
  abstract protected function resolveSimple(string $logicalType) : string;

  /**
   * Резолвинг строкового типа с указанной длиной.
   *
   * @param  int $length
   * @return string
   */
  abstract protected function resolveSizedString(int $length) : string;

  /**
   * Экранирование идентификатора (имя таблицы, колонки, индекса).
   *
   * @param  string $identifier
   * @return string
   */
  abstract public function quoteIdentifier(string $identifier) : string;

  /**
   * Главный метод: логический тип -> физический.
   *
   * Поддерживает синтаксис string:N для строк заданной длины.
   *
   * @param  string $logicalType
   * @return string
   */
  final public function resolve(string $logicalType) : string
  {
    if (preg_match('/^string:(\d+)$/', $logicalType, $matches)) {
      return $this->resolveSizedString((int) $matches[1]);
    }

    return $this->resolveSimple($logicalType);
  }

  /**
   * Проверяет, является ли переданный тип логическим.
   *
   * @param  string $type
   * @return bool
   */
  final public function isLogicalType(string $type) : bool
  {
    return (bool) preg_match(
      '/^(id|bigint|integer|boolean|string(:(\d+))?|text|json|timestamp)$/',
      $type
    );
  }

  /**
   * Разрешено ли использование DEFAULT для указанного логического типа.
   *
   * @param  string $logicalType
   * @return bool
   */
  public function supportsDefault(string $logicalType) : bool
  {
    return !in_array($logicalType, ['text', 'json'], true);
  }

  /**
   * Проверяет, содержит ли constraint ключевое слово DEFAULT.
   *
   * @param  string $constraint
   * @return bool
   */
  final public function constraintHasDefault(string $constraint) : bool
  {
    return (bool) preg_match('/\bDEFAULT\b/i', $constraint);
  }

  /**
   * Поддерживает ли СУБД указанный тип индекса.
   *
   * @param  IndexType $type
   * @return bool
   */
  public function supportsIndexType(IndexType $type) : bool
  {
    return match ($type) {
      IndexType::BTREE, IndexType::HASH => true,
      default => false,
    };
  }

  /**
   * Поддерживает ли СУБД конкурентное создание индекса.
   *
   * @return bool
   */
  public function supportsConcurrently() : bool
  {
    return false;
  }

  /**
   * Поддерживает ли СУБД конструкцию IF NOT EXISTS для CREATE INDEX.
   *
   * @return bool
   */
  public function supportsIfNotExistsForIndex() : bool
  {
    return false;
  }

  /**
   * Поддерживает ли СУБД частичные индексы (WHERE).
   *
   * @return bool
   */
  public function supportsPartialIndex() : bool
  {
    return false;
  }

  /**
   * Поддерживает ли СУБД конкурентное удаление индекса.
   *
   * @return bool
   */
  public function supportsDropIndexConcurrently() : bool
  {
    return false;
  }

  /**
   * Поддерживает ли СУБД конструкцию IF EXISTS для DROP INDEX.
   *
   * @return bool
   */
  public function supportsDropIndexIfExists() : bool
  {
    return false;
  }

  /**
   * Поддерживает ли СУБД конструкцию RETURNING в INSERT.
   *
   * @return bool
   */
  public function supportsInsertReturning() : bool
  {
    return false;
  }

  /**
   * Требуется ли для типа индекса явное указание USING <type>.
   *
   * @param  IndexType $type
   * @return bool
   */
  public function requiresUsingClause(IndexType $type) : bool
  {
    return $type !== IndexType::BTREE;
  }

  /**
   * Разрешить адаптивное условие (строковый вариант resolveAdaptiveValue).
   *
   * @param  array $conditions
   * @return string
   */
  public function resolveAdaptiveCondition(array $conditions) : string
  {
    return (string) $this->resolveAdaptiveValue($conditions, '');
  }

  /**
   * Построить конструкцию LIMIT/OFFSET.
   *
   * Базовый вариант — синтаксис LIMIT x OFFSET y,
   * поддерживается PostgreSQL, MySQL, SQLite.
   *
   * @param  int $limit
   * @param  int $offset
   * @return string
   */
  public function buildLimitOffsetClause(int $limit, int $offset) : string
  {
    if ($limit <= 0) {
      return '';
    }

    if ($offset <= 0) {
      return sprintf('LIMIT %d', $limit);
    }

    return sprintf('LIMIT %d OFFSET %d', $limit, $offset);
  }

  /**
   * Разрешить адаптивное значение по ключу текущей СУБД.
   *
   * @param  array  $values   ['mysql' => ..., 'pgsql' => ..., 'default' => ...]
   * @param  mixed  $fallback Значение по умолчанию, если ключ не найден
   * @return mixed
   */
  public function resolveAdaptiveValue(array $values, mixed $fallback = null) : mixed
  {
    $dms = $this->getDMS();

    return $values[$dms->value] 
      ?? $values[strtolower($dms->name)]
      ?? $values['default']
      ?? $fallback;
  }
}
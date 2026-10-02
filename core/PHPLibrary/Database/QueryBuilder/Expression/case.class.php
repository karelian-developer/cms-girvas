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

namespace core\PHPLibrary\Database\QueryBuilder\Expression;

use \core\PHPLibrary\Database\QueryBuilder\Dialect as Dialect;

final class CaseExpression
{
  private array $whenConditions = [];
  private array $whenResults = [];
  private mixed $elseResult = null;
  private ?string $alias = null;
  private Dialect $dialect;
  
  /**
   * __construct
   *
   * @param CMSDMS $DMS Тип СУБД
   */
  public function __construct(Dialect $dialect)
  {
    $this->dialect = $dialect;
  }
  
  /**
   * Добавить условие WHEN
   *
   * @param string $condition Условие
   * @param mixed $result Результат (число, строка или выражение)
   * 
   * @return self
   */
  public function when(string $condition, mixed $result) : self
  {
    $this->whenConditions[] = $condition;
    $this->whenResults[] = $result;

    return $this;
  }
  
  /**
   * Добавить условие WHEN с JSON-полем и ILIKE/LIKE
   *
   * @param string $column    Имя JSON-колонки (например, 'texts')
   * @param string $locale    Локаль (например, 'ru_RU')
   * @param string $field     Поле (например, 'title')
   * @param string $paramName Имя плейсхолдера
   * @param int    $weight    Вес (результат)
   */
  public function whenJsonLike(
    string $column,
    string $locale,
    string $field,
    string $paramName,
    int $weight
  ) : self
  {
    $condition = $this->dialect->jsonLike($column, $locale, $field, $paramName);
    return $this->when($condition, $weight);
  }
  
  /**
   * Добавить условие WHEN с EXISTS для JSON-массива
   *
   * @param string $column    Имя JSON-колонки
   * @param string $locale    Локаль
   * @param string $field     Поле-массив (например, 'keywords')
   * @param string $paramName Имя плейсхолдера
   * @param int    $weight    Вес
   */
  public function whenJsonArrayContains(
    string $column,
    string $locale,
    string $field,
    string $paramName,
    int $weight
  ) : self
  {
    $condition = $this->dialect->jsonArrayContainsLike($column, $locale, $field, $paramName);
    return $this->when($condition, $weight);
  }
  
  /**
   * Установить значение ELSE
   *
   * @param mixed $result
   * @return self
   */
  public function else(mixed $result) : self
  {
    $this->elseResult = $result;
    return $this;
  }
  
  /**
   * Установить алиас для выражения
   *
   * @param string $alias
   * @return self
   */
  public function as(string $alias) : self
  {
    $this->alias = $alias;
    return $this;
  }
  
  /**
   * Построить SQL-выражение
   *
   * @return string
   */
  public function build() : string
  {
    if (empty($this->whenConditions)) {
      return '';
    }
    
    $sql = 'CASE';
    
    foreach ($this->whenConditions as $index => $condition) {
      $result = $this->whenResults[$index];
      $resultStr = is_string($result) ? "'" . addslashes($result) . "'" : $result;
      $sql .= sprintf(' WHEN %s THEN %s', $condition, $resultStr);
    }
    
    if ($this->elseResult !== null) {
      $elseStr = is_string($this->elseResult) ? 
        "'" . addslashes($this->elseResult) . "'" : 
        $this->elseResult;
      $sql .= sprintf(' ELSE %s', $elseStr);
    }
    
    $sql .= ' END';
    
    if ($this->alias !== null) {
      $sql .= sprintf(' AS %s', $this->alias);
    }
    
    return $sql;
  }
  
  /**
   * Суммировать несколько CASE-выражений
   *
   * @param array $expressions Массив выражений
   * @param string|null $alias Алиас для суммы
   * @return string
   */
  public static function sum(array $expressions, ?string $alias = null) : string
  {
    $parts = array_map(fn($expr) => 
      $expr instanceof self ? $expr->buildWithoutAlias() : (string)$expr, 
      $expressions
    );
    
    $sql = '(' . implode(' + ', $parts) . ')';
    
    if ($alias !== null) {
      $sql .= ' AS ' . $alias;
    }
    
    return $sql;
  }
  
  /**
   * Построить выражение без алиаса (для вложенных конструкций)
   *
   * @return string
   */
  private function buildWithoutAlias() : string
  {
    if (empty($this->whenConditions)) {
      return '0';
    }
    
    $sql = 'CASE';
    
    foreach ($this->whenConditions as $index => $condition) {
      $result = $this->whenResults[$index];
      $resultStr = is_string($result) ? "'" . addslashes($result) . "'" : $result;
      $sql .= sprintf(' WHEN %s THEN %s', $condition, $resultStr);
    }
    
    if ($this->elseResult !== null) {
      $elseStr = is_string($this->elseResult) ? 
        "'" . addslashes($this->elseResult) . "'" : 
        $this->elseResult;
      $sql .= sprintf(' ELSE %s', $elseStr);
    }
    
    $sql .= ' END';
    
    return $sql;
  }
  
  public function __toString() : string
  {
    return $this->build();
  }
}
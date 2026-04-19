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

use \core\PHPLibrary\Database\DatabaseManagementSystem as CMSDMS;

final class CaseExpression
{
  private array $whenConditions = [];
  private array $whenResults = [];
  private mixed $elseResult = null;
  private ?string $alias = null;
  private CMSDMS $DMS;
  
  /**
   * __construct
   *
   * @param CMSDMS $DMS Тип СУБД
   */
  public function __construct(CMSDMS $DMS)
  {
    $this->DMS = $DMS;
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
   * @param string $jsonPath Путь в JSON (например: texts->'ru_RU'->>'title')
   * @param string $paramName Имя параметра
   * @param int $weight Вес (результат)
   * @param bool $caseInsensitive Регистронезависимый поиск
   * @return self
   */
  public function whenJsonLike(
    string $jsonPath, 
    string $paramName, 
    int $weight, 
    bool $caseInsensitive = true
  ) : self
  {
    $condition = match ($this->DMS) {
      CMSDMS::PostgreSQL => sprintf(
        "%s %s '%%' || :%s || '%%'",
        $jsonPath,
        $caseInsensitive ? 'ILIKE' : 'LIKE',
        $paramName
      ),
      CMSDMS::MySQL => sprintf(
        "JSON_UNQUOTE(JSON_EXTRACT(%s)) LIKE CONCAT('%%', :%s, '%%')",
        $this->convertJsonPathToMySQL($jsonPath),
        $paramName
      )
    };
    
    return $this->when($condition, $weight);
  }
  
  /**
   * Добавить условие WHEN с EXISTS для JSON-массива
   *
   * @param string $jsonArrayPath Путь к JSON-массиву
   * @param string $paramName Имя параметра
   * @param int $weight Вес
   * @return self
   */
  public function whenJsonArrayContains(
    string $jsonArrayPath,
    string $paramName,
    int $weight
  ) : self
  {
    $condition = match ($this->DMS) {
      CMSDMS::PostgreSQL => sprintf(
        "EXISTS (SELECT 1 FROM jsonb_array_elements_text(%s) AS elem WHERE elem ILIKE '%%' || :%s || '%%')",
        $jsonArrayPath,
        $paramName
      ),
      CMSDMS::MySQL => sprintf(
        "JSON_SEARCH(%s, 'one', :%s, NULL) IS NOT NULL",
        $this->convertJsonPathToMySQL($jsonArrayPath),
        $paramName
      )
    };
    
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
  
  /**
   * Конвертировать PostgreSQL JSON-путь в MySQL формат
   *
   * @param string $pgPath
   * @return string
   */
  private function convertJsonPathToMySQL(string $pgPath) : string
  {
    if (preg_match("/^(\w+)->'(\w+)'->>'(\w+)'$/", $pgPath, $matches)) {
      return sprintf("%s, '$.%s.%s'", $matches[1], $matches[2], $matches[3]);
    }
    
    if (preg_match("/^(\w+)->'(\w+)'->'(\w+)'$/", $pgPath, $matches)) {
      return sprintf("%s, '$.%s.%s[*]'", $matches[1], $matches[2], $matches[3]);
    }
    
    return $pgPath;
  }
  
  public function __toString() : string
  {
    return $this->build();
  }
}
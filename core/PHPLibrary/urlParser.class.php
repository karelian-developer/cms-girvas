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

namespace core\PHPLibrary;

/**
 * URLParser
 * 
 * Класс для работы URL
 * 
 * @author Andrey Shestakov <drelagas.new@yandex.ru>
 * @version 0.0.1-1
 */
final class URLParser
{
  private array $path = [];
  private array $params = [];
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->path = $this->getParsedPath();
    $this->params = $this->getParsedParams();
  }
      
  /**
   * Получить элемент пути URL
   *
   * @param  int $index Индекс элемента массива пути
   * 
   * @return string|null
   */
  public function getPath(int $index) : string|null
  {
    return $this->path[$index] ?? null;
  }
      
  /**
   * Получить массив элементов пути URL
   *
   * @return array
   */
  public function getPathes() : array
  {
    return $this->path ?? [];
  }
      
  /**
   * Получить путь URL в виде строки
   *
   * @return string
   */
  public function getPathString() : string
  {
    return !empty($this->path) ? implode('/', $this->path) : '';
  }
  
  /**
   * Получить массив параметров URL
   *
   * @return array
   */
  public function getParams() : array
  {
    return $this->params;
  }
  
  /**
   * Получить параметр URL
   *
   * @return string|null
   */
  public function getParam(string $name) : string|null
  {
    return $this->params[$name] ?? null;
  }
  
  /**
   * Получить массив элементов пути URL (парсинг)
   *
   * @return array
   */
  private function getParsedPath() : array
  {
    $result = [];

    $parsedURL = parse_url($_SERVER['REQUEST_URI']);
    if (array_key_exists('path', $parsedURL)) {
      error_log(print_r($parsedURL, true));
      $pathArray = explode('/', $parsedURL['path']);

      foreach ($pathArray as $pathElement) {
        if (!empty($pathElement)) {
          $pathElement = is_numeric($pathElement) ? (int) $pathElement : $pathElement;
          $result[] = $pathElement;
        }
      }
    }
    
    return $result;
  }
  
  /**
   * Получить массив параметров URL (парсинг)
   *
   * @return array
   */
  private function getParsedParams() : array
  {
    $result = [];

    $parsedURL = parse_url($_SERVER['REQUEST_URI']);
    
    if (array_key_exists('query', $parsedURL)) {
      $paramsArray = explode('&', $parsedURL['query']);

      foreach ($paramsArray as $param) {
        preg_match('/([a-z0-9\-\_\.]*)\=([a-z0-9\-\_\\.\,]*)/i', $param, $regexMatches);

        if (array_key_exists(1, $regexMatches) && array_key_exists(2, $regexMatches)) {
          $value = is_numeric($regexMatches[2]) ? (int) $regexMatches[2] : $regexMatches[2];
          $result[$regexMatches[1]] = $value;
        }
      }
    }

    return $result;
  }

  /**
   * Получить предыдущий URL (при наличии)
   * 
   * @param string $defaultURL
   * 
   * @return string
   */
  public function getPreviousURL(string $defaultURL = '/') : string {
    return $_SERVER['HTTP_REFERER'] ?? $defaultURL;
  }
}
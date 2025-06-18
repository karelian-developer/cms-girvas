<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  /**
   * URLParser
   * 
   * Класс для работы URL
   * 
   * @author Andrey Shestakov <drelagas.new@yandex.ru>
   * @version 0.0.1-1
   */
  final class URLParser {
    private array $path = [];
    private array $params = [];
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
      $this->path = $this->get_parsed_path();
      $this->params = $this->get_parsed_params();
    }
        
    /**
     * Получить элемент пути URL
     *
     * @param  int $index Индекс элемента массива пути
     * 
     * @return string|null
     */
    public function get_path(int $index) : string|null {
      return (isset($this->path[$index])) ? $this->path[$index] : null;
    }
        
    /**
     * Получить массив элементов пути URL
     *
     * @return array
     */
    public function get_pathes() : array {
      return (isset($this->path)) ? $this->path : [];
    }
        
    /**
     * Получить путь URL в виде строки
     *
     * @return string
     */
    public function get_path_string() : string {
      return (!empty($this->path)) ? implode('/', $this->path) : '';
    }
    
    /**
     * Получить массив параметров URL
     *
     * @return array
     */
    public function get_params() : array {
      return $this->params;
    }
    
    /**
     * Получить параметр URL
     *
     * @return mixed
     */
    public function get_param(string $name) : mixed {
      return (isset($this->params[$name])) ? $this->params[$name] : null;
    }
    
    /**
     * Получить массив элементов пути URL (парсинг)
     *
     * @return array
     */
    private function get_parsed_path() : array {
      $result = [];

      $parsedURL = parse_url($_SERVER['REQUEST_URI']);
      if (array_key_exists('path', $parsedURL)) {
        $pathArray = explode('/', $parsedURL['path']);

        foreach ($pathArray as $pathElement) {
          if (!empty($pathElement)) {
            $pathElement = (is_numeric($pathElement)) ? (int)$pathElement : $pathElement;
            array_push($result, $pathElement);
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
    private function get_parsed_params() : array {
      $result = [];

      $parsedURL = parse_url($_SERVER['REQUEST_URI']);
      if (array_key_exists('query', $parsedURL)) {
        $paramsArray = explode('&', $parsedURL['query']);

        foreach ($paramsArray as $param) {
          preg_match('/([a-z0-9\-\_\.]*)\=([a-z0-9\-\_\\.\,]*)/i', $param, $regexMatches);
          if (array_key_exists(1, $regexMatches) && array_key_exists(2, $regexMatches)) {
            $value = (is_numeric($regexMatches[2])) ? (int)$regexMatches[2] : $regexMatches[2];
            $result[$regexMatches[1]] = $value;
          }
        }
      }

      return $result;
    }

  }

}

?>
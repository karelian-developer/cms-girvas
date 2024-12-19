<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {

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
     * @param  int $path_index Индекс элемента массива пути
     * @return string|null
     */
    public function get_path(int $path_index) : string|null {
      return (isset($this->path[$path_index])) ? $this->path[$path_index] : null;
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
    public function get_param(string $param_name) : mixed {
      return (isset($this->params[$param_name])) ? $this->params[$param_name] : null;
    }
    
    /**
     * Получить массив элементов пути URL (парсинг)
     *
     * @return array
     */
    private function get_parsed_path() : array {
      $result = [];

      $url_parsed = parse_url($_SERVER['REQUEST_URI']);
      if (array_key_exists('path', $url_parsed)) {
        $url_path_array =  explode('/', $url_parsed['path']);

        foreach ($url_path_array as $url_path_element) {
          if (!empty($url_path_element)) {
            $url_path_element = (is_numeric($url_path_element)) ? (int)$url_path_element : $url_path_element;
            array_push($result, $url_path_element);
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

      $url_parsed = parse_url($_SERVER['REQUEST_URI']);
      if (array_key_exists('query', $url_parsed)) {
        $url_params_array =  explode('&', $url_parsed['query']);

        foreach ($url_params_array as $url_param) {
          preg_match('/([a-z0-9\-\_\.]*)\=([a-z0-9\-\_\\.\,]*)/i', $url_param, $regex_matches);
          if (array_key_exists(1, $regex_matches) && array_key_exists(2, $regex_matches)) {
            $param_value = (is_numeric($regex_matches[2])) ? (int)$regex_matches[2] : $regex_matches[2];
            $result[$regex_matches[1]] = $param_value;
          }
        }
      }

      return $result;
    }

  }

}

?>
<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {

  final class FileConnector implements InterfaceFileConnector {
    private mixed $system_core = null;
    private string $current_directory = '';
    private string $start_directory = '';
        
    /**
     * __construct
     *
     * @param  mixed $system_core Объект SystemCore
     * @return void
     */
    public function __construct(\core\PHPLibrary\SystemCore $system_core) {
      $this->system_core = $system_core;
    }
    
    /**
     * Сбросить текущую директорию.
     *
     * @return void
     */
    public function reset_current_directory() : void {
      $this->set_current_directory($this->get_start_directory());
    }
    
    /**
     * Назначить начальную директиву
     *
     * @param  mixed $directory Директория
     * @return void
     */
    public function set_start_directory(string $directory) : void {
      $this->start_directory = $directory;
    }
    
    /**
     * Получить начальную директиву
     *
     * @return string
     */
    public function get_start_directory() : string {
      return $this->start_directory;
    }
    
    /**
     * Назначить текущую директиву
     *
     * @param  mixed $directory Директория
     * @return void
     */
    public function set_current_directory(string $directory) : void {
      $this->current_directory = $directory;
    }
    
    /**
     * Получить текущую директиву
     *
     * @return string
     */
    public function get_current_directory() : string {
      return $this->current_directory;
    }

    
    /**
     * Подключение файла
     *
     * @param  mixed $file_path
     * @return bool
     */
    public function connect_file(string $file_path) : bool {
      /** @var string $file_path Полный путь до подключаемого файла */
      if (file_exists($file_path)) {
        require_once($file_path);
        return true;
      }

      return false;
    }

        
    /**
     * Рекурсивное подключение файлов
     *
     * @param  mixed $file_name_pattern Шаблон (regex) наименования шаблона
     * @param  int $level Уровень вложенности
     * @return bool
     */
    public function connect_files_recursive(string $file_name_pattern, int $level = 0) : void {
      /** @var string $files_path Полный путь до файлов */
      $files_path = $this->get_current_directory();
      /** @var array $files_list Массив файлов */
      $files_list = array_diff(scandir(sprintf($files_path)), ['..', '.']);
      foreach ($files_list as $file_name) {
        if ($level == 0) {
          $this->reset_current_directory();
        }
        
        /** @var string $file_path Полный путь до файла */
        $file_path = sprintf('%s/%s', $files_path, $file_name);
        
        if (preg_match($file_name_pattern, $file_name)) {
          // Подключаем файл
          $this->connect_file($file_path);
        } else {
          if (is_dir($file_path)) {
            $this->set_current_directory($file_path);
            // Погружаемся во вложенную папку для последующих подключений
            $this->connect_files_recursive($file_name_pattern, $level + 1);
          }
        }
      }
    }

  }

}

?>
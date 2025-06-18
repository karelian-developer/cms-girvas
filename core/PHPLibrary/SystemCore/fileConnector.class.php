<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {

  final class FileConnector implements InterfaceFileConnector {
    private mixed $CMSCore = null;
    private string $currentDirectory = '';
    private string $startDirectory = '';
        
    /**
     * __construct
     *
     * @param  mixed $CMSCore Объект SystemCore
     * @return void
     */
    public function __construct(\core\PHPLibrary\SystemCore $CMSCore) {
      $this->CMSCore = $CMSCore;
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
     * @param  mixed $path
     * @return bool
     */
    public function connect_file(string $path) : bool {
      if (file_exists($path)) {
        require_once $path;
        return true;
      }

      return false;
    }

        
    /**
     * Рекурсивное подключение файлов
     *
     * @param  mixed $fileNamePattern Шаблон (regex) наименования шаблона
     * @param  int $level Уровень вложенности
     * @return bool
     */
    public function connect_files_recursive(string $fileNamePattern, int $level = 0) : void {
      /** @var string $filesPath Полный путь до файлов */
      $filesPath = $this->get_current_directory();
      /** @var array $filesList Массив файлов */
      $filesList = array_diff(scandir(sprintf($filesPath)), ['..', '.']);
      foreach ($filesList as $fileName) {
        if ($level == 0) {
          $this->reset_current_directory();
        }
        
        /** @var string $filePath Полный путь до файла */
        $filePath = sprintf('%s/%s', $filesPath, $fileName);
        
        if (preg_match($fileNamePattern, $fileName)) {
          // Подключаем файл
          $this->connect_file($filePath);
        } else {
          if (is_dir($filePath)) {
            $this->set_current_directory($filePath);
            // Погружаемся во вложенную папку для последующих подключений
            $this->connect_files_recursive($fileNamePattern, $level + 1);
          }
        }
      }
    }

  }

}

?>
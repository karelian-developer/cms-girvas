<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\SystemCore as CMSCore;

final class FileConnector implements InterfaceFileConnector
{
  private CMSCore $CMSCore;
  private string $currentDirectory = '';
  private string $startDirectory = '';
      
  /**
   * __construct
   *
   * @param  mixed $CMSCore Объект SystemCore
   * 
   * @return void
   */
  public function __construct(CMSCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;
  }
  
  /**
   * Сбросить текущую директорию.
   *
   * @return void
   */
  public function resetCurrentDirectory() : void
  {
    $this->setCurrentDirectory($this->getStartDirectory());
  }
  
  /**
   * Назначить начальную директиву
   *
   * @param  mixed $directory Директория
   * 
   * @return void
   */
  public function setStartDirectory(string $directory) : void
  {
    $this->startDirectory = $directory;
  }
  
  /**
   * Получить начальную директиву
   *
   * @return string
   */
  public function getStartDirectory() : string
  {
    return $this->startDirectory;
  }
  
  /**
   * Назначить текущую директиву
   *
   * @param  mixed $directory Директория
   * @return void
   */
  public function setCurrentDirectory(string $directory) : void
  {
    $this->currentDirectory = $directory;
  }
  
  /**
   * Получить текущую директиву
   *
   * @return string
   */
  public function getCurrentDirectory() : string
  {
    return $this->currentDirectory;
  }

  
  /**
   * Подключение файла
   *
   * @param  mixed $path
   * @return bool
   */
  public function connectFile(string $path) : bool
  {
    if (file_exists($path)) {
      require_once $path;
      return true;
    }

    return false;
  }

      
  /**
   * Рекурсивное подключение файлов
   *
   * @param string $fileNamePattern Шаблон (regex) наименования шаблона
   * @param int $level Уровень вложенности
   * 
   * @return void
   */
  public function connectFilesRecursive(string $fileNamePattern, int $level = 0) : void
  {
    /** @var string $filesPath Полный путь до файлов */
    $filesPath = $this->getCurrentDirectory();
    /** @var array $filesList Массив файлов */
    $filesList = array_diff(scandir(sprintf($filesPath)), ['..', '.']);
    foreach ($filesList as $fileName) {
      if ($level === 0) {
        $this->resetCurrentDirectory();
      }
      
      $filePath = $filesPath . '/' . $fileName;
      
      if (preg_match($fileNamePattern, $fileName)) {
        // Подключаем файл
        $this->connectFile($filePath);
      } else {
        if (is_dir($filePath)) {
          $this->setCurrentDirectory($filePath);
          // Погружаемся во вложенную папку для последующих подключений
          $this->connectFilesRecursive($fileNamePattern, $level + 1);
        }
      }
    }
  }
}
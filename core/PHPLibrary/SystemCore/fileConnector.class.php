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
  private CMSCore|null $CMSCore = null;
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
   * @param string $path
   * 
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
    
    if ($level === 0) {
      $cacheKey = md5($filesPath . $fileNamePattern);
      $cacheFile = CMS_ROOT_DIRECTORY . '/cache/' . $cacheKey . '.json';
      $cacheIsValid = false;
      $cachedData = [];

      if (file_exists($cacheFile)) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        $cacheIsValid = $cachedData['dirMtime'] ?? 0 === filemtime($filesPath);
      }

      if ($cacheIsValid && time() < $cachedData['expires']) {
        foreach ($cachedData['files'] as $file) {
          error_log($file);
          $this->connectFile(strtr($file, ['\\' => '']));
        }

        return;
      }
    }

    $filesList = array_diff(scandir($filesPath), ['..', '.']);
    $foundFiles = [];
    
    foreach ($filesList as $fileName) {
      $filePath = $filesPath . '/' . $fileName;
      
      if (preg_match($fileNamePattern, $fileName)) {
        $foundFiles[] = $filePath;
        $this->connectFile($filePath);
      } elseif (is_dir($filePath)) {
        if (is_dir($filePath)) {
          $this->setCurrentDirectory($filePath);
          // Погружаемся во вложенную папку для последующих подключений
          $this->connectFilesRecursive($fileNamePattern, $level + 1);
          $this->resetCurrentDirectory();
        }
      }
    }

    if ($level === 0) {
      $cacheData = [
        'dirMtime' => filemtime($filesPath),
        'expires' => time() + 300, // 5 минут
        'files' => $foundFiles
      ];
        
      if (!is_dir(CMS_ROOT_DIRECTORY . '/cache')) {
        mkdir(CMS_ROOT_DIRECTORY . '/cache', 0777, true);
      }

      file_put_contents($cacheFile, json_encode($cacheData));
    }
  }
}
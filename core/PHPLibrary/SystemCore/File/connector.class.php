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

namespace core\PHPLibrary\SystemCore\File;

use \core\PHPLibrary\SystemCore as CMSCore;

final class Connector implements InterfaceConnector
{
  private ?CMSCore $CMSCore = null;
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
  public function generateCachePathesFiles(string $fileNamePattern, string $salt = '', int $level = 0) : void
  {
    /** @var string $filesPath Полный путь до файлов */
    $filesPath = $this->getCurrentDirectory();

    $cacheKey = md5($fileNamePattern . $salt);
    $cacheFile = CMS_ROOT_DIRECTORY . '/cache/' . $cacheKey . '.cache';

    error_log($filesPath);
    /** @var array $filesList Массив файлов */
    $filesList = array_diff(scandir($filesPath), ['..', '.']);
    $foundFiles = [];

    foreach ($filesList as $fileName) {
      if ($level === 0) {
        $this->resetCurrentDirectory();
      }
      
      $filePath = $filesPath . '/' . $fileName;
      
      if (preg_match($fileNamePattern, $fileName)) {
        $foundFiles[] = $filePath;

        $cacheData = [
          'expires' => time() + 300, // 5 минут
          'files' => $foundFiles
        ];

        if (file_exists($cacheFile)) {
          $cachedData = json_decode(file_get_contents($cacheFile), true);
          $cacheData['expires'] = time() + 300;
          $cachedData['files'][] = $filePath;
          
          file_put_contents($cacheFile, json_encode($cachedData), LOCK_EX);
        } else {
          file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
        }
        // Подключаем файл
        //$this->connectFile($filePath);
      } else {
        if (is_dir($filePath)) {
          $this->setCurrentDirectory($filePath);
          // Погружаемся во вложенную папку для последующих подключений
          $this->generateCachePathesFiles($fileNamePattern, $salt, $level + 1);
        }
      }
    }
  }
}
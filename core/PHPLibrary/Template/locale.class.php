<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\LocaleInterface as LocaleInterface;

final class Locale implements LocaleInterface
{
  public SystemCore $CMSCore;
  public Theme $theme;
  private string $name;
  private string $corePath;
  private string $dataPath;

  /**
   * __construct
   * 
   * @param Theme $theme
   * @param string $name
   */
  public function __construct(Theme $theme, string $name)
  {
    $this->CMSCore = $theme->CMSCore;
    $this->theme = $theme;
    $this->setName($name);

    $corePath = $this->CMSCore->getCMSPath() . '/templates/' . $theme->getName() . '/locales/' . $name;
    $dataPath = $this->CMSCore->getCMSPath() . '/templates/' . $theme->getName() . '/locales/' . $name;

    $this->setCorePath($corePath);
    $this->setDataPath($dataPath);
  }

  /**
   * Получить URL до иконки локализации
   * 
   * @return string
   */
  public function getIconURL() : string
  {
    return '/templates/' . $this->theme->getName() . '/locales/' . $this->getName() . '/icons/16.png';
  }

  /**
   * Установить наименование локализации
   * 
   * @param string $value
   * 
   * @return void
   */
  private function setName(string $value) : void
  {
    $this->name = $value;
  }

  /**
   * Получить наименование локализации
   * 
   * @return string
   */
  public function getName() : string
  {
    return $this->name;
  }
  
  /**
   * Назначить путь до локализации
   *
   * @param  string $path Путь до локализации
   * @return void
   */
  public function setCorePath(string $path) : void
  {
    $this->corePath = $path;
  }
  
  /**
   * Получить путь до локализации
   *
   * @return string
   */
  public function getCorePath() : string
  {
    return $this->corePath;
  }
  
  /**
   * Назначить путь до данных локализации
   *
   * @param  string $path Путь до локализации
   * @return void
   */
  public function setDataPath(string $path) : void
  {
    $this->dataPath = $path;
  }
  
  /**
   * Получить путь до данных локализации
   *
   * @return string
   */
  public function getDataPath() : string
  {
    return $this->dataPath;
  }

  /**
   * Получить заголовок
   * 
   * @return string
   */
  public function getTitle() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['title'] ?? '';
  }

  /**
   * Получить имя автора
   */
  public function getAuthorName() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['authorName'] ?? '';
  }
  
  /**
   * Получить код локализации одного из стандартов ISO-639
   * 
   * @param int $index Индекс стандарта
   * 
   * @return string
   */
  public function getISO639(int $index) : string
  {
    $metadata = $this->getMetadata();
    return $metadata['iso639_' . $index] ?? '';
  }

  /**
   * Получить код локализации стандарта ISO-639-1 (устаревшее)
   * 
   * @return string
   */
  public function getISO639_1() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['iso639_1'] ?? '';
  }

  /**
   * Получить код локализации стандарта ISO-639-2 (устаревшее)
   * 
   * @return string
   */
  public function getISO639_2() : string
  {
    $metadata = $this->getMetadata();
    return $metadata['iso639_2'] ?? '';
  }

  /**
   * Проверить наличие файла с данными локализации в формате JSON
   * 
   * @return bool
   */
  public function existsFileDataJSON() : bool
  {
    return file_exists($this->getFileDataJSONPath());
  }

  /**
   * Получить абсолютный путь до файла с данными локализации в формате JSON
   * 
   * @return string
   */
  public function getFileDataJSONPath() : string
  {
    return $this->getDataPath() . '/data.json';
  }

  /**
   * Проверить наличие файла с реестром локализации в формате JSON
   * 
   * @return bool
   */
  public function existsFileRegistryJSON() : bool
  {
    return file_exists($this->getFileDataJSONPath());
  }

  /**
   * Получить абсолютный путь до файла с реестром локализации в формате JSON
   * 
   * @return string
   */
  public function getFileRegistryJSONPath() : string
  {
    return $this->getDataPath() . '/registry.json';
  }

  /**
   * Получить данные локализации
   * 
   * @return array
   */
  public function getData() : array|bool|null
  {
    $filePath = $this->getFileDataJSONPath();
    $fileContent = file_exists($filePath) ? file_get_contents($filePath) : '{}';

    return json_decode($fileContent, true);
  }

  /**
   * Получить значение элемента локализации
   * 
   * @param array $data
   * @param string $name
   * 
   * @return array
   */
  public static function getDataValue(array $data, string $name) : string
  {
    if (array_key_exists($name, $data)) {
      return $data[$name];
    }

    $document = new DOMDocument();

    $spanElement = $document->createElement('span', '[' . $name . ']');
    $spanElement->setAttribute('style', 'background-color: red;color: white;');

    $document->appendChild($spanElement);

    return $document->saveHTML();
  }

  /**
   * Получить данные реестра локализации
   * 
   * @return array
   */
  public function getRegistryArray() : array
  {
    $filePath = $this->getFileRegistryJSONPath();
    $fileContent = (file_exists($filePath)) ? file_get_contents($filePath) : '{}';

    return json_decode($fileContent, true);
  }

  /**
   * Получить одиночное значение из данных локализации
   * 
   * @param string $key
   * 
   * @return string
   */
  public function getSingleValueByKey(string $key) : string
  {
    $data = $this->getData();
    return $data[$key] ?? '[ ??? ]';
  }

  /**
   * Проверить наличие файла с метаданными локализации в формате JSON
   * 
   * @return bool
   */
  public function existsFileMetadataJSON() : bool
  {
    return file_exists($this->getFileMetadataJSONPath());
  }

  /**
   * Получить абсолютный путь до файла с метаданными локализации в формате JSON
   * 
   * @return string
   */
  public function getFileMetadataJSONPath() : string
  {
    return $this->getCorePath() . '/metadata.json';
  }

  /**
   * Получить метаданные локализации
   * 
   * @return array
   */
  public function getMetadata() : array|null
  {
    $filePath = $this->getFileMetadataJSONPath();
    $fileContent = file_get_contents($filePath);

    return json_decode($fileContent, true);
  }
}
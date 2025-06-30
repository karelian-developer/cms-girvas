<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use SystemCore as CMSCore;

interface LocaleInterface
{
  public function __construct(mixed $object, string $name);

  public function getIconURL() : string;
  public function getName() : string;
  public function getCorePath() : string;
  public function getDataPath() : string;
  public function getTitle() : string;
  public function getAuthorName() : string;
  public function getISO639(int $index) : string;
  public function getFileDataJSONPath() : string;
  public function getFileRegistryJSONPath() : string;
  public function getFileMetadataJSONPath() : string;
  public function getMetadata() : array|null;
  public function getData() : array|bool|null;
  public function getRegistryArray() : array;
  public function getSingleValueByKey(string $key) : string;

  public function setCorePath(string $path) : void;
  public function setDataPath(string $path) : void;
  public function setName(string $value) : void;

  public function existsFileDataJSON() : bool;
  public function existsFileMetadataJSON() : bool;
  
  public static function getDataValue(array $data, string $name) : string;
}
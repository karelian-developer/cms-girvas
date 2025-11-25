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

  public function existsFileDataJSON() : bool;
  public function existsFileMetadataJSON() : bool;

  public static function getDataValue(array $data, string $name) : string;
}
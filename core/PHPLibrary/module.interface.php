<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Module\EnumMetadata as ModuleEnumMetadata;
use \core\PHPLibrary\Module\EnumWeight as ModuleEnumWeight;

interface InterfaceModule
{
  public function __construct(CoreInterface $CMSCore, string $name);
  public function setURL(string $url) : void;
  public function getPreviewURL() : string;
  public function getScreenshotsPath() : string;
  public function getScreenshotsURL() : string;
  public function getScreenshotsArray() : array;
  public function getPath() : string;
  public function getURL() : string;
  public function getName() : string;
  public function getDescription() : string;
  public function getAuthorName() : string;
  public static function getWeight(Module $module, ModuleEnumWeight $enumWeight) : float;
  public static function getMetadataName(ModuleEnumMetadata $metadata) : string;
  public static function connectCore(CoreInterface $CMSCore, string $name) : bool;
  public function isEnabled() : bool;
  public function isInstalled() : bool;
  public function install() : bool;
  public function delete() : bool;
  public function enable() : bool;
  public function disable() : bool;
  public function existsCoreFile() : bool;
  public function getCorePath() : string;
  public function getCoreCreatedUnixTimestamp() : int;
  public function existsFileMetadataJSON() : bool;
  public function getFileMetadataJSONPath() : string;
  public function getMetadata() : ?array;
  public function getFileReadmeMDPath() : string;
  public function getContentFileReadmeMD() : string;
  public function existsFileReadmeMD() : bool;
}
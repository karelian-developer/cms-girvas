<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Feed\Builder as FeedBuilder;

interface InterfaceSpecification
{
  public function __construct(SystemCore $CMSCore, FeedBuilder $feedBuilder);
  public function setTitle(string $value) : void;
  public function setDescription(string $value) : void;
  public function setLanguage(string $value) : void;
  public function setLink(string $value) : void;
  public function addItem(array $item) : void;
  public function getTitle() : string;
  public function getDescription() : string;
  public function getLanguage() : string;
  public function getLink() : string;
  public function getItems() : array;
}
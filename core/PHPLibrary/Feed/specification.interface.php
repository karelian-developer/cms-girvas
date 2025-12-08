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
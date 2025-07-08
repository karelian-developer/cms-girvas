<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed;

use \DOMDocument as DOMDocument;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Feed\EnumSpecification as EnumSpecification;
use \core\PHPLibrary\Feed\InterfaceSpecification as InterfaceSpecification;
use \core\PHPLibrary\Feed\Specification\RSS1_0 as SpecificationRSS1_0;
use \core\PHPLibrary\Feed\Specification\RSS2_0 as SpecificationRSS2_0;
use \core\PHPLibrary\Feed\Specification\Atom as SpecificationAtom;

final class Builder
{
  public DOMDocument $document;
  private SystemCore $CMSCore;
  public $feed;
  private array $items = [];
  private string $language = 'en-us';
  public string $assembled = '';

  /**
   * __construct
   *
   * @param SystemCore $CMSCore
   * @param EnumSpecification $feedType
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore, EnumSpecification $feedType)
  {
    $this->CMSCore = $CMSCore;
    $this->document = new DOMDocument('1.0');

    $this->feed = match ($feedType) {
      EnumSpecification::RSS1_0 => new SpecificationRSS1_0($CMSCore, $this),
      EnumSpecification::RSS2_0 => new SpecificationRSS2_0($CMSCore, $this),
      EnumSpecification::Atom => new SpecificationAtom($CMSCore, $this),
    };
  }
  
  /**
   * Назначит язык RSS-ленты
   *
   * @param  string $localeName
   * @return void
   */
  public function setLanguage(string $localeName) : void
  {
    $this->feed->setLanguage($localeName);
  }
  
  /**
   * Получить язык RSS-ленты
   *
   * @return string
   */
  public function getLanguage() : string
  {
    return $this->feed->language;
  }

  public static function getTypeEnum(int $typeID) : ?EnumSpecification
  {
    return match ($typeID) {
      1 => EnumSpecification::RSS1_0,
      2 => EnumSpecification::RSS2_0,
      3 => EnumSpecification::Atom,
    };

    return null;
  }

  public static function getTypeTitle(int $typeID) : string
  {
    return match ($typeID) {
      1 => SpecificationRSS1_0::TYPE_TITLE,
      2 => SpecificationRSS2_0::TYPE_TITLE,
      3 => SpecificationAtom::TYPE_TITLE,
    };

    return '';
  }

  public static function getTypeName(int $typeID) : string
  {
    return match ($typeID) {
      1 => SpecificationRSS1_0::TYPE_NAME,
      2 => SpecificationRSS2_0::TYPE_NAME,
      3 => SpecificationAtom::TYPE_NAME,
    };

    return '';
  }

  public function assembly() : void
  {
    $this->feed->assembly();
  }
}
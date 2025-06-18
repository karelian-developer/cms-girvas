<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Feed {
  use \DOMDocument as DOMDocument;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Feed\EnumSpecification as EnumSpecification;
  use \core\PHPLibrary\Feed\InterfaceSpecification as InterfaceSpecification;
  use \core\PHPLibrary\Feed\Specification\RSS1_0 as SpecificationRSS1_0;
  use \core\PHPLibrary\Feed\Specification\RSS2_0 as SpecificationRSS2_0;
  use \core\PHPLibrary\Feed\Specification\Atom as SpecificationAtom;

  final class Builder {
    public DOMDocument $document;
    private SystemCore $CMSCore;
    public $feed;
    private array $items = [];
    private string $language = 'en-us';
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore, EnumSpecification $feedType) {
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
     * @param  mixed $localeName
     * @return void
     */
    public function set_language(string $localeName) : void {
      $this->feed->set_language($localeName);
    }
    
    /**
     * Получить язык RSS-ленты
     *
     * @return string
     */
    public function get_language() : string {
      return $this->feed->language;
    }

    public static function get_type_enum(int $typeID) : EnumSpecification|null {
      switch ($typeID) {
        case 1: return EnumSpecification::RSS1_0;
        case 2: return EnumSpecification::RSS2_0;
        case 3: return EnumSpecification::Atom;
        case 4: return EnumSpecification::YandexTurbo;
      }

      return null;
    }

    public static function get_type_title(int $typeID) : string {
      switch ($typeID) {
        case 1: return SpecificationRSS1_0::TYPE_TITLE;
        case 2: return SpecificationRSS2_0::TYPE_TITLE;
        case 3: return SpecificationAtom::TYPE_TITLE;
        case 4: return SpecificationYandexTurbo::TYPE_TITLE;
      }

      return '';
    }

    public static function get_type_name(int $typeID) : string {
      switch ($typeID) {
        case 1: return SpecificationRSS1_0::TYPE_NAME;
        case 2: return SpecificationRSS2_0::TYPE_NAME;
        case 3: return SpecificationAtom::TYPE_NAME;
        case 4: return SpecificationYandexTurbo::TYPE_NAME;
      }

      return '';
    }

    public function assembly() : void {
      $this->feed->assembly();
    }
  }
}

?>
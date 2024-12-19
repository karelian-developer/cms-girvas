<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\WebChannel {
  use \DOMDocument as DOMDocument;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\WebChannel\EnumSpecification as EnumSpecification;
  use \core\PHPLibrary\WebChannel\InterfaceSpecification as InterfaceSpecification;
  use \core\PHPLibrary\WebChannel\Specification\RSS1_0 as SpecificationRSS1_0;
  use \core\PHPLibrary\WebChannel\Specification\RSS2_0 as SpecificationRSS2_0;
  use \core\PHPLibrary\WebChannel\Specification\Atom as SpecificationAtom;
  use \core\PHPLibrary\WebChannel\Specification\YandexTurbo as SpecificationYandexTurbo;

  final class Builder {
    public DOMDocument $document;
    private SystemCore $system_core;
    public $web_channel;
    private array $items = [];
    private string $language = 'en-us';
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @return void
     */
    public function __construct(SystemCore $system_core, EnumSpecification $web_channel_type) {
      $this->system_core = $system_core;
      $this->document = new DOMDocument('1.0');

      $this->web_channel = match ($web_channel_type) {
        EnumSpecification::RSS1_0 => new SpecificationRSS1_0($system_core, $this),
        EnumSpecification::RSS2_0 => new SpecificationRSS2_0($system_core, $this),
        EnumSpecification::Atom => new SpecificationAtom($system_core, $this),
        EnumSpecification::YandexTurbo => new SpecificationYandexTurbo($system_core, $this),
      };
    }
    
    /**
     * Назначит язык RSS-ленты
     *
     * @param  mixed $locale_name
     * @return void
     */
    public function set_language(string $locale_name) : void {
      $this->language = str_replace('_', '-', strtolower($locale_name));
    }
    
    /**
     * Получить язык RSS-ленты
     *
     * @return string
     */
    public function get_language() : string {
      return $this->language;
    }

    public static function get_type_enum(int $type_id) : EnumSpecification|null {
      switch ($type_id) {
        case 1: return EnumSpecification::RSS1_0;
        case 2: return EnumSpecification::RSS2_0;
        case 3: return EnumSpecification::Atom;
        case 4: return EnumSpecification::YandexTurbo;
      }

      return null;
    }

    public static function get_type_title(int $type_id) : string {
      switch ($type_id) {
        case 1: return SpecificationRSS1_0::TYPE_TITLE;
        case 2: return SpecificationRSS2_0::TYPE_TITLE;
        case 3: return SpecificationAtom::TYPE_TITLE;
        case 4: return SpecificationYandexTurbo::TYPE_TITLE;
      }

      return '';
    }

    public static function get_type_name(int $type_id) : string {
      switch ($type_id) {
        case 1: return SpecificationRSS1_0::TYPE_NAME;
        case 2: return SpecificationRSS2_0::TYPE_NAME;
        case 3: return SpecificationAtom::TYPE_NAME;
        case 4: return SpecificationYandexTurbo::TYPE_NAME;
      }

      return '';
    }

    public function assembly() : void {
      $this->web_channel->assembly();
    }
  }
}

?>
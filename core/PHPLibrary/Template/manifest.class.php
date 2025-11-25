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

namespace core\PHPLibrary\Template;

use \core\PHPLibrary\SystemCore\ConfiguratorInterface as CMSConfiguratorInterface;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \InvalidArgumentException as InvalidArgumentException;

class Manifest
{
  private array $data = [];

  public const DISPLAY_STANDALONE = 'standalone';
  public const DISPLAY_FULLSCREEN = 'fullscreen';
  public const DISPLAY_MINIMAL_UI = 'minimal-ui';
  public const DISPLAY_BROWSER = 'browser';
  private const COLOR_REGEX = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';

  /**
   * __construct
   * 
   * @param CMSConfiguratorInterface $CMSConfigurator;
   * @param ThemeInterfaceCore $theme;
   * 
   * @return void
   */
  public function __construct (
    private CMSConfiguratorInterface $CMSConfigurator,
    private ThemeInterfaceCore $themeCore
  ) {
    $this->setName($CMSConfigurator->getSiteTitle());
    $this->setShortName($CMSConfigurator->getSiteTitle());
    $this->setDescription($CMSConfigurator->getSiteDescription());
    $this->setThemeColor($themeCore->getPrimaryColor());
    $this->setDisplay(self::DISPLAY_STANDALONE);
    $this->setStartURL('/');

    $this->data['icons'] = [];
  }

  /**
   * Получить JSON-манифест
   * 
   * @return string
   */
  public function getJSON() : string
  {
    return json_encode($this->data, JSON_UNESCAPED_SLASHES);
  }

  /**
   * Установить наименование
   * 
   * @param string $name
   * 
   * @return void
   */
  public function setName(string $name) : void
  {
    $this->data['name'] = $name;
  }

  /**
   * Установить короткое наименование
   * 
   * @param string $shortName
   * 
   * @return void
   */
  public function setShortName(string $shortName) : void
  {
    $this->data['short_name'] = $shortName;
  }

  /**
   * Установить описание
   * 
   * @param string $description
   * 
   * @return void
   */
  public function setDescription(string $description) : void
  {
    $this->data['description'] = $description;
  }

  /**
   * Установить корневой URL
   * 
   * @param string $URL
   * 
   * @return void
   */
  public function setStartURL(string $URL = '/') : void
  {
    $this->data['start_url'] = $URL;
  }

  /**
   * Установить тип дисплея
   * 
   * @param string $display
   * 
   * @return void
   */
  public function setDisplay(string $display) : void
  {
    $allowedDisplays = [
      self::DISPLAY_STANDALONE,
      self::DISPLAY_FULLSCREEN,
      self::DISPLAY_MINIMAL_UI,
      self::DISPLAY_BROWSER
    ];

    if (!in_array($display, $allowedDisplays, true)) {
      throw new InvalidArgumentException('Неверный режим дисплея: ' . $display);
    }

    $this->data['display'] = $display;
  }

  /**
   * Установить цвет темы
   * 
   * @param string $color
   * 
   * @return void
   */
  public function setThemeColor(string $color) : void
  {
    if (!preg_match(self::COLOR_REGEX, $color)) {
      throw new InvalidArgumentException('Неверный формат цвета: ' . $color);
    }

    $this->data['theme_color'] = $color;
  }

  /**
   * Установить цвет фона
   * 
   * @param string $color
   * 
   * @return void
   */
  public function setBackgroundColor(string $color) : void
  {
    if (!preg_match(self::COLOR_REGEX, $color)) {
      throw new InvalidArgumentException('Неверный формат цвета: ' . $color);
    }

    $this->data['background_color'] = $color;
  }

  /**
   * Добавить иконку
   * 
   * @param string $URL
   * @param array $sizes
   * @param string $type
   * 
   * @return void
   */
  public function addIcon(string $URL, array $sizes, string $type) : void
  {
    if (count($sizes) == 2) {
      if (!is_numeric($sizes[0]) || !is_numeric($sizes[1])) {
        throw new InvalidArgumentException('Размеры переданы в неверном формате - они должны быть представлены в виде целых чисел.');
      }

      [$width, $height] = $sizes;
      $width = (int)$width;
      $height = (int)$height;

      $sizesLabel = $width . 'x' . $height;
      $themeFrame = $this->themeCore->getThemeFrame();
      $themeURL = $themeFrame->getURL();

      $this->data['icons'][] = [
        'src' => $themeURL . $URL,
        'sizes' => $sizesLabel,
        'type' => $type
      ];
    } else {
      throw new InvalidArgumentException('Передано неверное количество значений для размера иконки - их должно быть 2.');
    }
  }
}
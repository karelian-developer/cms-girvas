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

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageError implements InterfacePage
{
  public CMSCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public int $errorCode;
  private string $errorTitle;
  private string $errorDescription;
  
  /**
   * Карта поддерживаемых кодов ошибок.
   * Ключ — HTTP-код, значение — суффикс ключей локализации.
   *
   * @var array<int, string>
   */
  private const ERROR_LOCALE_MAP = [
    400 => '400',
    401 => '401',
    403 => '403',
    404 => '404',
    405 => '405',
    408 => '408',
    410 => '410',
    413 => '413',
    414 => '414',
    429 => '429',
    451 => '451',
    500 => '500',
    501 => '501',
    502 => '502',
    503 => '503',
    504 => '504',
  ];

  /**
   * Коды, для которых в описание подставляется URI запроса.
   *
   * @var array<int, bool>
   */
  private const ERROR_CODES_WITH_URI = [
    400 => true,
    404 => true,
    410 => true,
    414 => true,
  ];

  /**
   * __construct
   *
   * @param  CMSCore $CMSCore
   * @param  Page $page
   * @param  int $errorCode
   * @return void
   */
  public function __construct(CMSCore $CMSCore, Page $page, int $errorCode)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
    $this->errorCode = $errorCode;

    $localeData = $this->CMSCore->locale->getData();

    $suffix = self::ERROR_LOCALE_MAP[$errorCode] ?? null;

    if ($suffix !== null && isset($localeData["PAGE_ERROR_{$suffix}_TITLE"])) {
      $this->errorTitle = $localeData["PAGE_ERROR_{$suffix}_TITLE"];
      $description = $localeData["PAGE_ERROR_{$suffix}_DESCRIPTION"] ?? '';

      if (isset(self::ERROR_CODES_WITH_URI[$errorCode])) {
        $description = sprintf(
          $description,
          strip_tags(urldecode($_SERVER['REQUEST_URI'] ?? ''))
        );
      }

      $this->errorDescription = $description;
    } else {
      $this->errorTitle = $localeData['PAGE_ERROR_UNKNOWN_TITLE'];
      $this->errorDescription = $localeData['PAGE_ERROR_UNKNOWN_DESCRIPTION'];
    }
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    http_response_code($this->errorCode);

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/error.css', 'rel' => 'stylesheet']);

    $this->CMSCore->configurator->setMetaTitle($this->errorTitle);

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/error.tpl',
      [
        'ERROR_TITLE' => $this->errorTitle,
        'ERROR_DESCRIPTION' => $this->errorDescription
      ]
    );
  }
}
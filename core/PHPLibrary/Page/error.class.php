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
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageError implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public int $errorCode;
  private string $errorTitle;
  private string $errorDescription;
  
  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * @param  int $errorCode
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page, int $errorCode)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
    $this->errorCode = $errorCode;

    $localeData = $this->CMSCore->locale->getData();

    switch ($errorCode) {
      case 404:
        $this->errorTitle = $localeData['PAGE_ERROR_404_TITLE'];
        $this->errorDescription = sprintf($localeData['PAGE_ERROR_404_DESCRIPTION'], strip_tags(urldecode($_SERVER['REQUEST_URI'])));
        break;
      case 500:
        $this->errorTitle = $localeData['PAGE_ERROR_500_TITLE'];
        $this->errorDescription = $localeData['PAGE_ERROR_500_DESCRIPTION'];
        break;
      case 503:
        $this->errorTitle = $localeData['PAGE_ERROR_503_TITLE'];
        $this->errorDescription = $localeData['PAGE_ERROR_503_DESCRIPTION'];
        break;
      default:
        $this->errorTitle = $localeData['PAGE_ERROR_UNKNOWN_TITLE'];
        $this->errorDescription = $localeData['PAGE_ERROR_UNKNOWN_DESCRIPTION'];
    }
  }
  
  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page/error.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
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

    $this->addRequiredStyles();

    $this->CMSCore->configurator->setMetaTitle($this->errorTitle);

    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page.tpl',
      [
        'PAGE_NAME' => 'error error_' . $this->errorCode,
        'PAGE_CONTENT' => ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme, 'templates/page/error.tpl',
          [
            'ERROR_CODE' => $this->errorCode,
            'ERROR_TITLE' => $this->errorTitle,
            'ERROR_DESCRIPTION' => sprintf(
              '<div class="page__simple-note">%s</div>',
              $this->errorDescription
            )
          ]
        )
      ]
    );
  }
}
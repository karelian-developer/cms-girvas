<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\default;

use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \DOMDocument as DOMDocument;

#[\AllowDynamicProperties]
final class Core implements ThemeInterfaceCore
{
  private Theme $theme;
  private SystemCoreLocale $locale;
  public string $assembled = '';
  public DOMDocument|null $source = null;
  
  /**
   * __construct
   *
   * @param  mixed $theme
   * 
   * @return void
   */
  public function __construct(Theme $theme)
  {
    $this->theme = $theme;
  }

  /**
   * Сборка стартовой страницы
   * 
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyPageIndex(array $themeVars = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/page/index.tpl', [
      'PAGE_NAME' => 'index',
      'ENTRIES_LIST' => ''
    ]);
  }
  
  /**
   * Сборка заглушки сайта
   *
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyPlug(array $themeVars = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/plug.tpl', $themeVars);
  }
  
  /**
   * Сборка шапки сайта
   *
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyHeader(array $themeVars = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/header.tpl', $themeVars);
  }
  
  /**
   * Сборка главной секции сайта
   *
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyMain(array $themeVars = []) : string
  {
    $this->theme->CMSCore->initPage($this->theme->CMSCore->urlp->getPathString());
    $sitePage = $this->theme->CMSCore->getInitedPage();
    $sitePage->assembly();
    
    $themeVars['SITE_PAGE'] = ThemeCollector::assembly($sitePage->assembled, []);

    return ThemeCollector::assemblyFileContent($this->theme, 'templates/main.tpl', $themeVars);
  }
  
  /**
   * Сборка подвала сайта
   *
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyFooter(array $themeVars = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/footer.tpl', $themeVars);
  }
  
  /**
   * Сборка основной части документа
   *
   * @param  mixed $themeVars Массив с переменами темы и их значениями
   * 
   * @return string
   */
  public function assemblyDocument(array $themeVars = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/html.tpl', $themeVars);
  }
  
  /**
   * Итоговая сборка шаблона
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->theme->addStyle(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/common.css', 'rel' => 'stylesheet']);

    $localeData = $this->theme->locale->getData();

    $clientIsLogged = $this->theme->CMSCore->client->isLogged(1);
    $user = $clientIsLogged ? $this->theme->CMSCore->client->getUser(1) : null;
    
    if ($user !== null) {
      $user->initData(['metadata']);
    }

    $userGroupID = $user !== null ? $user->getGroupID() : 0;
    $CMSConfigEngineeringWorksStatus = $this->theme->CMSCore->configurator->getDatabaseEntryValue('base_engineering_works_status');

    if ($CMSConfigEngineeringWorksStatus === 'off' || $userGroupID === 1) {
      $this->theme->addStyle(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      
      $this->theme->addScript(['src' => 'common.js'], true);
      $this->theme->addScript(['src' => 'core.class.js', 'type' => 'module'], true);
      $this->theme->addScript(['src' => 'core.class.js', 'type' => 'module']);

      $profileLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="/profile"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_PROFILE']) : sprintf('<a id="SYSTEM_GE_IMC_00000001" class="header__nav-link display-block" href="#"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_LOGIN']);
      $registrationLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="/registration"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_REGISTRATION']) : '';
      $exitLink = $clientIsLogged ? sprintf('<a class="header__nav-link display-block" href="#" role="profileNavigationExit"><span class="header__nav-span">%s</span></a>', $localeData['DEFAULT_TEXT_EXIT']) : '';

      /** @var string $this->assembled Итоговый шаблон в виде строки */
      $this->assembled = ThemeCollector::assembly($this->assemblyDocument(), [
        'SITE_HEADER' => $this->assemblyHeader([
          'NAVIGATION_PROFILE_LINK' => $profileLink,
          'NAVIGATION_REGISTRATION_LINK' => $registrationLink,
          'NAVIGATION_EXIT_LINK' => $exitLink
        ]),
        'SITE_MAIN' => $this->assemblyMain(),
        'SITE_FOOTER' => $this->assemblyFooter()
      ]);
    } else {
      $this->assembled = ThemeCollector::assembly($this->assemblyDocument(), [
        'SITE_HEADER' => '',
        'SITE_MAIN' => ThemeCollector::assembly($this->assemblyPlug(), [
          'SITE_CLOSED_REASON' => $this->theme->CMSCore->configurator->getDatabaseEntryValue('base_engineering_works_text')
        ]),
        'SITE_FOOTER' => ''
      ]);
    }
  }
}
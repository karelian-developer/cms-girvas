<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\default;

use \core\PHPLibrary\SystemCore\Locale as CMSLocale;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \DOMDocument as DOMDocument;

#[\AllowDynamicProperties]
final class Core implements ThemeInterfaceCore
{
  private Theme $theme;
  private CMSLocale $locale;
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
    $CMSTheme = $this->theme;
    $CMSConfigurator = $CMSTheme->CMSCore->configurator;
    $CMSThemeProperties = $CMSTheme->getFilePropertiesData();
    $CMSThemeColorScheme = isset($CMSThemeProperties['COLOR_SCHEME'])
      ? $CMSThemeProperties['COLOR_SCHEME']['value']
      : 'default';

    if (!file_exists($CMSTheme->getPath() . '/styles/colors/' . $CMSThemeColorScheme . '.css')) {
      $CMSThemeColorScheme = 'default';
    }

    $CMSTheme->addStyle(['href' => 'styles/colors/' . $CMSThemeColorScheme . '.css', 'rel' => 'stylesheet']);
    $CMSTheme->addStyle(['href' => 'styles/common.css', 'rel' => 'stylesheet']);

    $localeData = $CMSTheme->locale->getData();

    $clientIsLogged = $CMSTheme->CMSCore->client->isLogged(1);
    $user = $clientIsLogged ? $CMSTheme->CMSCore->client->getUser(1) : null;
    
    if ($user !== null) {
      $user->initData(['metadata']);
    }

    $userGroupID = $user !== null ? $user->getGroupID() : 0;
    $CMSConfigEngineeringWorksStatus = $CMSConfigurator->getDatabaseEntryValue('base_engineering_works_status');

    if ($CMSConfigEngineeringWorksStatus === 'off' || $userGroupID === 1) {
      $CMSTheme->addStyle(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
      $CMSTheme->addStyle(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
      $CMSTheme->addStyle(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
      $CMSTheme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      
      $CMSTheme->addScript(['src' => 'common.js'], true);
      $CMSTheme->addScript(['src' => 'core.class.js', 'type' => 'module'], true);
      $CMSTheme->addScript(['src' => 'core.class.js', 'type' => 'module']);

      $profileLink = $clientIsLogged
        ? $this->assemblyProfileLink($localeData)
        : $this->assemblyLoginLink($localeData);
      $registrationLink = !$clientIsLogged
        ? $this->assemblyRegistrationLink($localeData)
        : '';
      $exitLink = $clientIsLogged
        ? $this->assemblyExitLink($localeData)
        : '';

      /** @var string $this->assembled Итоговый шаблон в виде строки */
      $this->assembled = ThemeCollector::assembly(
        $this->assemblyDocument(),
        [
          'SITE_HEADER' => $this->assemblyHeader(
            [
              'NAVIGATION_PROFILE_LINK' => $profileLink,
              'NAVIGATION_REGISTRATION_LINK' => $registrationLink,
              'NAVIGATION_EXIT_LINK' => $exitLink
            ]
          ),
          'SITE_MAIN' => $this->assemblyMain(),
          'SITE_FOOTER' => $this->assemblyFooter()
        ]
      );
    } else {
      $CMSConfigEngineeringWorksText = $CMSConfigurator->getDatabaseEntryValue('base_engineering_works_text');

      $this->assembled = ThemeCollector::assembly(
        $this->assemblyDocument(),
        [
          'SITE_HEADER' => '',
          'SITE_MAIN' => ThemeCollector::assembly(
            $this->assemblyPlug(),
            [
              'SITE_CLOSED_REASON' => $CMSConfigEngineeringWorksText
            ]
          ),
          'SITE_FOOTER' => ''
        ]
      );
    }
  }

  /**
   * Сборка ссылки "Профиль"
   * 
   * @param array $localeData
   * 
   * @return string
   */
  private function assemblyProfileLink(array $localeData = []) : string
  {
    $document = new DOMDocument('1.0');

    $documentFragment = $document->createDocumentFragment();

    $linkElement = $document->createElement('a');
    $linkElement->setAttribute('class', 'header__nav-link nav-link display-block');
    $linkElement->setAttribute('href', '/profile');

    $linkLabelElement = $document->createElement('span', $localeData['DEFAULT_TEXT_PROFILE']);
    $linkLabelElement->setAttribute('class', 'header__nav-span nav-span');

    $linkElement->appendChild($linkLabelElement);
    $documentFragment->appendChild($linkElement);
    $document->appendChild($documentFragment);

    return $document->saveHTML();
  }

  /**
   * Сборка ссылки "Войти"
   * 
   * @param array $localeData
   * 
   * @return string
   */
  private function assemblyLoginLink(array $localeData = []) : string
  {
    $document = new DOMDocument('1.0');

    $documentFragment = $document->createDocumentFragment();

    $linkElement = $document->createElement('a');
    $linkElement->setAttribute('id', 'SYSTEM_GE_IMC_00000001');
    $linkElement->setAttribute('class', 'header__nav-link nav-link display-block');
    $linkElement->setAttribute('href', '#');

    $linkLabelElement = $document->createElement('span', $localeData['DEFAULT_TEXT_LOGIN']);
    $linkLabelElement->setAttribute('class', 'header__nav-span nav-span');

    $linkElement->appendChild($linkLabelElement);
    $documentFragment->appendChild($linkElement);
    $document->appendChild($documentFragment);

    return $document->saveHTML();
  }

  /**
   * Сборка ссылки "Регистрация"
   * 
   * @param array $localeData
   * 
   * @return string
   */
  private function assemblyRegistrationLink(array $localeData = []) : string
  {
    $document = new DOMDocument('1.0');

    $documentFragment = $document->createDocumentFragment();

    $linkElement = $document->createElement('a');
    $linkElement->setAttribute('class', 'header__nav-link nav-link display-block');
    $linkElement->setAttribute('href', '/registration');

    $linkLabelElement = $document->createElement('span', $localeData['DEFAULT_TEXT_REGISTRATION']);
    $linkLabelElement->setAttribute('class', 'header__nav-span nav-span');

    $linkElement->appendChild($linkLabelElement);
    $documentFragment->appendChild($linkElement);
    $document->appendChild($documentFragment);

    return $document->saveHTML();
  }

  /**
   * Сборка ссылки "Выход"
   * 
   * @param array $localeData
   * 
   * @return string
   */
  private function assemblyExitLink(array $localeData = []) : string
  {
    $document = new DOMDocument('1.0');

    $documentFragment = $document->createDocumentFragment();

    $linkElement = $document->createElement('a');
    $linkElement->setAttribute('role', 'profileNavigationExit');
    $linkElement->setAttribute('class', 'header__nav-link nav-link display-block');
    $linkElement->setAttribute('href', '#');

    $linkLabelElement = $document->createElement('span', $localeData['DEFAULT_TEXT_EXIT']);
    $linkLabelElement->setAttribute('class', 'header__nav-span nav-span');

    $linkElement->appendChild($linkLabelElement);
    $documentFragment->appendChild($linkElement);
    $document->appendChild($documentFragment);

    return $document->saveHTML();
  }
}
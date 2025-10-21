<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\admin\default;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\ThemeInterface as ThemeInterface;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Template\InterfaceCore as ThemeInterfaceCore;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entries\Database as EntriesDatabase;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Users as Users;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Client\Session as ClientSession;
use \DOMDocument as DOMDocument;
use \DOMImplementation as DOMImplementation;

#[\AllowDynamicProperties]
final class Core implements ThemeInterfaceCore
{
  private string $primaryColor = '#EAEAEA';
  public string $assembled = '';
  public DOMDocument|null $source = null;
  public array $navigationSections = [
    'index' => [
      'name' => 'index',
      'iconName' => 'index',
      'link' => '/',
      'permanent' => true,
      'role' => ''
    ],
    'entries' => [
      'name' => 'entries',
      'iconName' => 'entries',
      'link' => '/entries',
      'permanent' => false,
      'role' => ''
    ],
    'static_pages' => [
      'name' => 'pages',
      'iconName' => 'pages',
      'link' => '/pages',
      'permanent' => false,
      'role' => ''
    ],
    'media' => [
      'name' => 'media',
      'iconName' => 'media',
      'link' => '/media',
      'permanent' => false,
      'role' => ''
    ],
    'users' => [
      'name' => 'users',
      'iconName' => 'users',
      'link' => '/users',
      'permanent' => false,
      'role' => ''
    ],
    'feeds' => [
      'name' => 'feeds',
      'iconName' => 'feeds',
      'link' => '/feeds',
      'permanent' => false,
      'role' => ''
    ],
    'modules' => [
      'name' => 'modules',
      'iconName' => 'modules',
      'link' => '/modules',
      'permanent' => false,
      'role' => ''
    ],
    'templates' => [
      'name' => 'templates',
      'iconName' => 'templates',
      'link' => '/templates',
      'permanent' => false,
      'role' => ''
    ],
    'analytics' => [
      'name' => 'analytics',
      'iconName' => 'analytics',
      'link' => '/analytics',
      'permanent' => false,
      'role' => ''
    ],
    'settings' => [
      'name' => 'settings_cms',
      'iconName' => 'settings',
      'link' => '/settings',
      'permanent' => true,
      'role' => ''
    ],
    'about' => [
      'name' => 'about_cms',
      'iconName' => 'about',
      'link' => '/about',
      'permanent' => true,
      'role' => ''
    ],
    'exit' => [
      'name' => 'exit_cms',
      'iconName' => 'exit',
      'link' => '/',
      'permanent' => true,
      'role' => 'mainNavigationExit'
    ]
  ];
  
  /**
   * __construct
   *
   * @param ThemeInterface $theme
   * 
   * @return void
   */
  public function __construct(
    private ThemeInterface $theme
  ) {}

  /**
   * Получить основной объект темы
   * 
   * @return ThemeInterface
   */
  public function getThemeFrame() : ThemeInterface
  {
    return $this->theme;
  }

  /**
   * Получение начального цвета темы
   * 
   * @return string
   */
  public function getPrimaryColor() : string
  {
    return $this->primaryColor;
  }

  /**
   * Получить абсолютный путь SVG-файла иконки раздела
   * 
   * @param string $navigationItemName
   * 
   * @return string
   */
  private function getMainNavigationIconPath(string $navigationItemName) : string
  {
    $themePath = $this->theme->getPath();
    return $themePath . '/images/icons/mainNavigation/' . $navigationItemName . '.svg';
  }

  /**
   * Инициализация главной навигации
   * 
   * @return void
   */
  public function initMainNavigation() : void
  {
    if ($this->source !== null) {
      $elementCMSAPMainNavigation = $this->source->getElementById('SYSTEM_AP_MAIN_NAVIGATION');
      if ($elementCMSAPMainNavigation !== null) {
        $listElement = $elementCMSAPMainNavigation->ownerDocument->createElement('ul');
        $listElement->setAttribute('class', 'navigation__list list list-reset');

        if (count($this->navigationSections) > 0) {
          foreach ($this->navigationSections as $navigationSectionIndex => $navigationSectionData) {
            $navigationSectionName = $navigationSectionData['name'];
            $navigationSectionLink = $navigationSectionData['link'];
            $navigationSectionIconName = $navigationSectionData['iconName'];
            $navigationSectionPermanentStatus = $navigationSectionData['permanent'];
            $navigationSectionRole = $navigationSectionData['role'];
            
            $sectionAllowed = false;

            if (!$navigationSectionPermanentStatus) {
              $methodSectionCheckerName = 'getSection' . ucfirst((string) $navigationSectionIndex) . 'Status';
              
              if (method_exists($this->theme->CMSCore->configurator, $methodSectionCheckerName)) {
                if ($this->theme->CMSCore->configurator->{$methodSectionCheckerName}(true)) {
                  $sectionAllowed = true;
                }
              } else {
                $sectionAllowed = true;
              }
            } else {
              $sectionAllowed = true;
            }

            if ($sectionAllowed) {
              $itemTitle = sprintf('{LANG:MAIN_NAVIGATION_%s_LABEL}', strtoupper($navigationSectionName));
              $itemTitle = ThemeCollector::assemblyLocale($itemTitle, $this->theme->CMSCore->locale);
              
              $itemElement = $elementCMSAPMainNavigation->ownerDocument->createElement('li');
              $linkElement = $elementCMSAPMainNavigation->ownerDocument->createElement('a');
              $labelElement = $elementCMSAPMainNavigation->ownerDocument->createElement('div', $itemTitle);
              
              $itemElement->setAttribute('class', 'list__item item item_' . $navigationSectionName);

              if ($navigationSectionRole !== '') {
                $itemElement->setAttribute('role', $navigationSectionRole); 
              }

              $linkElement->setAttribute('class', 'item__link link');
              $linkElement->setAttribute('href', '/admin' . $navigationSectionLink);
              $linkElement->setAttribute('title', $itemTitle);
              $labelElement->setAttribute('class', 'item__label label');
              
              $SVGElement = new DOMDocument();
              $SVGElement->load($this->getMainNavigationIconPath($navigationSectionIconName));
              $SVGImportedElement = $this->source->importNode($SVGElement->documentElement, true);
              $SVGImportedElement->setAttribute('class', 'item__icon icon');

              $linkElement->appendChild($SVGImportedElement);
              $linkElement->appendChild($labelElement);
              $itemElement->appendChild($linkElement);
              $listElement->appendChild($itemElement);
            }
          }

          $elementCMSAPMainNavigation->appendChild($listElement);
        }
      }
    }
  }
  
  /**
   * Сборка шапки сайта
   *
   * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
   * 
   * @return string
   */
  public function assemblyHeader(array $themeReplaces = []) : string
  {
    /** @var User Объект авторизованного пользователя */
    $user = $this->theme->CMSCore->client->getUser(2);
    $user->initData(['login', 'metadata']);

    /** @var UserGroup Объект группы пользователя */
    $userGroup = $user->getGroup();
    $userGroup->initData(['texts']);

    /** @var string Техническое имя локализации шаблона */
    $themeLocaleName = $this->theme->locale->getName();

    /** @var string Логин пользователя */
    $themeReplaces['CLIENT_USER_LOGIN'] = $user->getLogin();
    /** @var string Логин пользователя */
    $themeReplaces['CLIENT_USER_GROUP_TITLE'] = $userGroup->getTitle($themeLocaleName);

    return ThemeCollector::assemblyFileContent($this->theme, 'templates/header.tpl', $themeReplaces);
  }
  
  /**
   * Сборка главной секции сайта
   *
   * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
   * 
   * @return string
   */
  public function assemblyMain(array $themeReplaces = []) : string
  {
    $this->theme->CMSCore->initPage(ltrim($_SERVER['REQUEST_URI'], '/'));
    $sitePage = $this->theme->CMSCore->getInitedPage();
    $sitePage->assembly();

    $themeReplaces['ADMIN_PANEL_PAGE_WRAPPER'] = ThemeCollector::assemblyFileContent($this->theme, 'templates/page.tpl', [
      'ADMIN_PANEL_PAGE' => $sitePage->assembled,
    ]);

    return ThemeCollector::assemblyFileContent($this->theme, 'templates/main.tpl', $themeReplaces);
  }
  
  /**
   * Сборка подвала сайта
   *
   * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
   * 
   * @return string
   */
  public function assemblyFooter(array $themeReplaces = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/footer.tpl', $themeReplaces);
  }

  public function assemblyAuthAdminPage(array $themeReplaces = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/page/auth.tpl', $themeReplaces);
  }

  public function assemblyAdminPanelNavigation(array $themeReplaces = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/navigation.tpl', $themeReplaces);
  }
  
  /**
   * Итоговая сборка шаблона
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->theme->addStyle(['href' => 'styles/normalize.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/fonts.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/common.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/table.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/form.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/modal.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/interactive.css', 'rel' => 'stylesheet']);
    $this->theme->addStyle(['href' => 'styles/notification.css', 'rel' => 'stylesheet']);
    
    $this->theme->addScript(['src' => 'interactive.class.js', 'type' => 'module'], true);
    $this->theme->addScript(['src' => 'common.js'], true);
    $this->theme->addScript(['src' => 'core.class.js', 'type' => 'module'], true);
    $this->theme->addScript(['src' => 'core.class.js', 'type' => 'module']);


    /** @var string $userIP IP-адрес пользователя */
    $userIP = $_SERVER['REMOTE_ADDR'];

    if ($this->theme->CMSCore->client->isLogged(2)) {
      $this->theme->addStyle(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
      $this->theme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);

      /** @var string $this->assembled Итоговый шаблон в виде строки */
      $this->assembled = ThemeCollector::assembly($this->assemblyDocument(), [
        'ADMIN_PANEL_HEADER' => $this->assemblyHeader(),
        'ADMIN_PANEL_MAIN' => $this->assemblyMain(),
        'ADMIN_PANEL_FOOTER' => $this->assemblyFooter(),
        'ADMIN_PANEL_NAVIGATION' => $this->assemblyAdminPanelNavigation()
      ]);
    } else {
      $this->theme->addStyle(['href' => 'styles/page/auth.css', 'rel' => 'stylesheet']);

      $this->assembled = ThemeCollector::assembly($this->assemblyDocument(), [
        'ADMIN_PANEL_HEADER' => '',
        'ADMIN_PANEL_MAIN' => $this->assemblyAuthAdminPage(),
        'ADMIN_PANEL_FOOTER' => ''
      ]);
    }
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
    $themeURL = $this->theme->getURL();
    $themeLocale = $this->theme->locale;
    $themeLocaleName = $themeLocale->getName();

    $documentLang = mb_substr($themeLocaleName, 0, 2);
    $documentLang = strtolower($documentLang);

    $document = new DOMDocument('1.0', 'UTF-8');
    $implementation = new DOMImplementation();
    $documentType = $implementation->createDocumentType('html');
    $document->appendChild($documentType);

    $HTMLElement = $document->createElement('html');
    $HTMLElement->setAttribute('lang', $documentLang);

    $headElement = $document->createElement('head');

    $titleElement = $document->createElement('title', '{SITE_TITLE} | Admin');
    $headElement->appendChild($titleElement);

    $metaCharsetElement = $document->createElement('meta');
    $metaCharsetElement->setAttribute('charset', '{SITE_CHARSET}');
    $headElement->appendChild($metaCharsetElement);

    $metaHTTPEquivElement = $document->createElement('meta');
    $metaHTTPEquivElement->setAttribute('http-equiv', 'X-UA-Compatible');
    $metaHTTPEquivElement->setAttribute('content', 'IE=edge');
    $headElement->appendChild($metaHTTPEquivElement);

    $metaViewportElement = $document->createElement('meta');
    $metaViewportElement->setAttribute('name', 'viewport');
    $metaViewportElement->setAttribute('content', 'width=device-width, initial-scale=1.0');
    $headElement->appendChild($metaViewportElement);

    $metaDescriptionElement = $document->createElement('meta');
    $metaDescriptionElement->setAttribute('name', 'description');
    $metaDescriptionElement->setAttribute('content', '{SITE_DESCRIPTION}');
    $headElement->appendChild($metaDescriptionElement);

    $metaKeywordsElement = $document->createElement('meta');
    $metaKeywordsElement->setAttribute('name', 'keywords');
    $metaKeywordsElement->setAttribute('content', '{SITE_KEYWORDS}');
    $headElement->appendChild($metaKeywordsElement);

    foreach ([256, 192, 180, 167, 152, 128, 120, 96, 64, 48, 32, 16] as $faviconWidth) {
      $linkFaviconElement = $document->createElement('link');
      $faviconSizesLabel = $faviconWidth . 'x' . $faviconWidth;

      if (in_array($faviconWidth, [192, 180, 167, 152, 120])) {
        $linkFaviconElement->setAttribute('rel', 'apple-touch-icon');
        $linkFaviconElement->setAttribute('href', '/' . $themeURL . '/favicons/apple-touch-icon-' . $faviconSizesLabel . '.png');
      }

      if (in_array($faviconWidth, [512, 256, 128, 96, 64, 48, 32, 16])) {
        $linkFaviconElement->setAttribute('rel', 'icon');
        $linkFaviconElement->setAttribute('type', 'image/png');
        $linkFaviconElement->setAttribute('href', '/' . $themeURL . '/favicons/favicon-' . $faviconSizesLabel . '.png');
      }

      $linkFaviconElement->setAttribute('sizes', $faviconSizesLabel);
      $headElement->appendChild($linkFaviconElement);
    }

    $linkManifestElement = $document->createElement('link');
    $linkManifestElement->setAttribute('rel', 'manifest');
    $linkManifestElement->setAttribute('href', '/manifest');
    $headElement->appendChild($linkManifestElement);

    $bodyElement = $document->createElement('body');

    if (!$this->theme->CMSCore->client->isLogged(2)) {
      $bodyElement->setAttribute('class', 'body body_auth');
      $bodyContentElement = $document->createTextNode('{ADMIN_PANEL_HEADER}{ADMIN_PANEL_MAIN}{ADMIN_PANEL_FOOTER}');
      
      $bodyElement->appendChild($bodyContentElement);
    } else {
      $bodyElement->setAttribute('class', 'body body_base admin-panel');

      $adminPanelWrapperElement = $document->createElement('div');
      $adminPanelWrapperElement->setAttribute('class', 'admin-panel__wrapper wrapper');

      $adminPanelNavigationElement = $document->createTextNode('{ADMIN_PANEL_NAVIGATION}');

      $adminPanelBasisElement = $document->createElement('div');
      $adminPanelBasisElement->setAttribute('class', 'admin-panel__basis basis');

      $adminPanelBasisContentElement = $document->createTextNode('{ADMIN_PANEL_HEADER}{ADMIN_PANEL_MAIN}{ADMIN_PANEL_FOOTER}');

      $adminPanelBasisElement->appendChild($adminPanelBasisContentElement);
      $adminPanelWrapperElement->appendChild($adminPanelNavigationElement);
      $adminPanelWrapperElement->appendChild($adminPanelBasisElement);
      $bodyElement->appendChild($adminPanelWrapperElement);
    }

    $HTMLElement->appendChild($headElement);
    $HTMLElement->appendChild($bodyElement);
    $document->appendChild($HTMLElement);

    return $document->saveHTML();
  }
}
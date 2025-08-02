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
use \core\PHPLibrary\InterfaceTemplate as InterfaceTheme;
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
   * @param  Theme $theme
   * 
   * @return void
   */
  public function __construct(
    private Theme $theme
  ) {}

  /**
   * Получить основной объект темы
   * 
   * @return InterfaceTheme
   */
  public function getThemeFrame() : InterfaceTheme
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
              $methodSectionCheckerName = 'get_section_' . (string) $navigationSectionIndex . '_status';
              
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
  
  /**
   * Сборка основной части документа
   *
   * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
   * @return string
   */
  public function assemblyDocument(array $themeReplaces = []) : string
  {
    /** @var string $assembled Содержимое шаблона */
    $assembled;

    if ($this->theme->CMSCore->client->isLogged(2)) {
      $themeContent = ThemeCollector::assemblyFileContent($this->theme, 'templates/documentBase.tpl', $themeReplaces);
    } else {
      $themeContent = ThemeCollector::assemblyFileContent($this->theme, 'templates/documentAuth.tpl', $themeReplaces);
    }

    return $themeContent;
  }

  public function assemblyAuthAdminPage(array $themeReplaces = []) : string
  {
    return ThemeCollector::assemblyFileContent($this->theme, 'templates/page/auth.tpl', $themeReplaces);
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
        'ADMIN_PANEL_FOOTER' => $this->assemblyFooter()
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
}
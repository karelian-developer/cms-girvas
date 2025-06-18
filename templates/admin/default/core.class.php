<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\admin\default {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Entries\Database as EntriesDatabase;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Users as Users;
  use \core\PHPLibrary\UserGroup as UserGroup;
  use \core\PHPLibrary\Client\Session as ClientSession;
  use \DOMDocument as DOMDocument;

  #[\AllowDynamicProperties]
  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $theme;
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
     * @param  mixed $theme
     * @return void
     */
    public function __construct(\core\PHPLibrary\Template $theme) {
      $this->theme = $theme;
    }

    /**
     * Получить абсолютный путь SVG-файла иконки раздела
     * 
     * @param string $navigationItemName
     * @return string
     */
    private function get_main_navigation_icon_path(string $navigationItemName) : string {
      $themePath = $this->theme->get_path();
      return $themePath . '/images/icons/mainNavigation/' . $navigationItemName . '.svg';
    }

    /**
     * Инициализация главной навигации
     * 
     * @return void
     */
    public function init_main_navigation() : void {
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
                $itemTitle = TemplateCollector::assembly_locale($itemTitle, $this->theme->CMSCore->locale);
                
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
                $SVGElement->load($this->get_main_navigation_icon_path($navigationSectionIconName));
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
     * @return string
     */
    public function assembly_header(array $themeReplaces = []) : string {
      /** @var User Объект авторизованного пользователя */
      $user = $this->theme->CMSCore->client->get_user(2);
      $user->init_data(['login', 'metadata']);

      /** @var UserGroup Объект группы пользователя */
      $userGroup = $user->get_group();
      $userGroup->init_data(['texts']);

      /** @var string Техническое имя локализации шаблона */
      $themeLocaleName = $this->theme->locale->get_name();

      /** @var string Логин пользователя */
      $themeReplaces['CLIENT_USER_LOGIN'] = $user->get_login();
      /** @var string Логин пользователя */
      $themeReplaces['CLIENT_USER_GROUP_TITLE'] = $userGroup->get_title($themeLocaleName);

      return TemplateCollector::assembly_file_content($this->theme, 'templates/header.tpl', $themeReplaces);
    }
    
    /**
     * Сборка главной секции сайта
     *
     * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_main(array $themeReplaces = []) : string {
      $this->theme->CMSCore->init_page(ltrim($_SERVER['REQUEST_URI'], '/'));
      $sitePage = $this->theme->CMSCore->get_inited_page();
      $sitePage->assembly();
      
      $themeReplaces['ADMIN_PANEL_PAGE_WRAPPER'] = TemplateCollector::assembly_file_content($this->theme, 'templates/page.tpl', [
        'ADMIN_PANEL_PAGE' => $sitePage->assembled,
      ]);

      return TemplateCollector::assembly_file_content($this->theme, 'templates/main.tpl', $themeReplaces);
    }
    
    /**
     * Сборка подвала сайта
     *
     * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_footer(array $themeReplaces = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/footer.tpl', $themeReplaces);
    }
    
    /**
     * Сборка основной части документа
     *
     * @param  mixed $themeReplaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_document(array $themeReplaces = []) : string {
      /** @var string $assembled Содержимое шаблона */
      $assembled;

      if ($this->theme->CMSCore->client->is_logged(2)) {
        $themeContent = TemplateCollector::assembly_file_content($this->theme, 'templates/documentBase.tpl', $themeReplaces);
      } else {
        $themeContent = TemplateCollector::assembly_file_content($this->theme, 'templates/documentAuth.tpl', $themeReplaces);
      }

      return $themeContent;
    }

    public function assembly_auth_admin_page(array $themeReplaces = []) : string {
      return TemplateCollector::assembly_file_content($this->theme, 'templates/page/auth.tpl', $themeReplaces);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->theme->add_style(['href' => 'styles/normalize.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/fonts.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/table.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/form.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/modal.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/interactive.css', 'rel' => 'stylesheet']);
      $this->theme->add_style(['href' => 'styles/notification.css', 'rel' => 'stylesheet']);
      
      $this->theme->add_script(['src' => 'interactive.class.js', 'type' => 'module'], true);
      $this->theme->add_script(['src' => 'common.js'], true);
      $this->theme->add_script(['src' => 'core.class.js', 'type' => 'module'], true);
      $this->theme->add_script(['src' => 'core.class.js', 'type' => 'module']);


      /** @var string $userIP IP-адрес пользователя */
      $userIP = $_SERVER['REMOTE_ADDR'];

      if ($this->theme->CMSCore->client->is_logged(2)) {
        $this->theme->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
        $this->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);

        /** @var string $this->assembled Итоговый шаблон в виде строки */
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => $this->assembly_header(),
          'ADMIN_PANEL_MAIN' => $this->assembly_main(),
          'ADMIN_PANEL_FOOTER' => $this->assembly_footer()
        ]);
      } else {
        $this->theme->add_style(['href' => 'styles/page/auth.css', 'rel' => 'stylesheet']);

        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => '',
          'ADMIN_PANEL_MAIN' => $this->assembly_auth_admin_page(),
          'ADMIN_PANEL_FOOTER' => ''
        ]);
      }
    }

  }

}

?>